/**
 * Dashboard Charts - Chart.js Integration
 * Handles all chart rendering and updates for the dashboard
 */

"use strict";

var DashboardCharts = (function() {
    // Chart instances
    let revenueChart = null;
    let customerGrowthChart = null;
    let paymentMethodChart = null;
    let ticketPriorityChart = null;

    // Chart colors
    const colors = {
        primary: '#3E97FF',
        success: '#50CD89',
        warning: '#FFC700',
        danger: '#F1416C',
        info: '#7239EA',
        dark: '#181C32',
        gray: '#A1A5B7'
    };

    /**
     * Initialize revenue chart
     */
    function initRevenueChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // Destroy existing chart
        if (revenueChart) {
            revenueChart.destroy();
        }

        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Gelir (₺)',
                    data: data.revenue,
                    borderColor: colors.primary,
                    backgroundColor: hexToRGBA(colors.primary, 0.1),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: colors.primary
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY'
                                }).format(context.parsed.y);
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY',
                                    minimumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });

        return revenueChart;
    }

    /**
     * Initialize customer growth chart
     */
    function initCustomerGrowthChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (customerGrowthChart) {
            customerGrowthChart.destroy();
        }

        customerGrowthChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Yeni Müşteriler',
                    data: data.new_customers,
                    backgroundColor: hexToRGBA(colors.success, 0.8),
                    borderColor: colors.success,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        return customerGrowthChart;
    }

    /**
     * Initialize payment method pie chart
     */
    function initPaymentMethodChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (paymentMethodChart) {
            paymentMethodChart.destroy();
        }

        paymentMethodChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels.map(label => {
                    return label === 'credit_card' ? 'Kredi Kartı' : 'Banka Transferi';
                }),
                datasets: [{
                    data: data.amounts,
                    backgroundColor: [
                        hexToRGBA(colors.primary, 0.8),
                        hexToRGBA(colors.success, 0.8),
                        hexToRGBA(colors.warning, 0.8),
                        hexToRGBA(colors.danger, 0.8)
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY'
                                }).format(context.parsed);
                                
                                // Add percentage
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                label += ` (${percentage}%)`;
                                
                                return label;
                            }
                        }
                    }
                }
            }
        });

        return paymentMethodChart;
    }

    /**
     * Initialize ticket priority chart
     */
    function initTicketPriorityChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (ticketPriorityChart) {
            ticketPriorityChart.destroy();
        }

        const labels = [];
        const counts = [];
        const backgroundColors = [];

        data.forEach(item => {
            labels.push(item.priority.charAt(0).toUpperCase() + item.priority.slice(1));
            counts.push(item.count);
            
            // Color based on priority
            if (item.priority === 'high') {
                backgroundColors.push(hexToRGBA(colors.danger, 0.8));
            } else if (item.priority === 'medium') {
                backgroundColors.push(hexToRGBA(colors.warning, 0.8));
            } else {
                backgroundColors.push(hexToRGBA(colors.info, 0.8));
            }
        });

        ticketPriorityChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: backgroundColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        return ticketPriorityChart;
    }

    /**
     * Update chart data
     */
    function updateChartData(chart, newData) {
        if (!chart) return;

        chart.data.labels = newData.labels;
        chart.data.datasets[0].data = newData.data;
        chart.update();
    }

    /**
     * Convert hex color to RGBA
     */
    function hexToRGBA(hex, alpha = 1) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    /**
     * Destroy all charts
     */
    function destroyAllCharts() {
        if (revenueChart) revenueChart.destroy();
        if (customerGrowthChart) customerGrowthChart.destroy();
        if (paymentMethodChart) paymentMethodChart.destroy();
        if (ticketPriorityChart) ticketPriorityChart.destroy();
    }

    // Public API
    return {
        initRevenueChart: initRevenueChart,
        initCustomerGrowthChart: initCustomerGrowthChart,
        initPaymentMethodChart: initPaymentMethodChart,
        initTicketPriorityChart: initTicketPriorityChart,
        updateChartData: updateChartData,
        destroyAllCharts: destroyAllCharts,
        colors: colors
    };
})();

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Charts module loaded');
});
