/**
 * Dashboard Real-time Updates
 * Handles AJAX polling and automatic data refresh
 */

"use strict";

var DashboardRealtime = (function () {
    let updateInterval = null;
    let notificationInterval = null;

    const config = {
        metricsUpdateInterval: 30000,  // 30 seconds
        chartsUpdateInterval: 300000,  // 5 minutes
        notificationPollInterval: 10000 // 10 seconds
    };

    /**
     * Start real-time updates
     */
    function start() {
        console.log('Starting real-time dashboard updates...');

        // Initial load
        updateMetrics();
        updateNotifications();

        // Set intervals
        updateInterval = setInterval(updateMetrics, config.metricsUpdateInterval);
        notificationInterval = setInterval(updateNotifications, config.notificationPollInterval);
    }

    /**
     * Stop real-time updates
     */
    function stop() {
        console.log('Stopping real-time dashboard updates...');

        if (updateInterval) {
            clearInterval(updateInterval);
            updateInterval = null;
        }

        if (notificationInterval) {
            clearInterval(notificationInterval);
            notificationInterval = null;
        }
    }

    /**
     * Update dashboard metrics
     */
    function updateMetrics() {
        fetch('dist/api/dashboard_api.php?action=get_metrics')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateMetricCards(data.data);
                }
            })
            .catch(error => {
                console.error('Error updating metrics:', error);
            });
    }

    /**
     * Update metric cards in the UI
     */
    function updateMetricCards(data) {
        // Update MRR if element exists
        const mrrElement = document.getElementById('mrr-value');
        if (mrrElement && data.mrr) {
            mrrElement.textContent = formatCurrency(data.mrr.current_mrr);
        }

        // Update ARR if element exists
        const arrElement = document.getElementById('arr-value');
        if (arrElement && data.arr) {
            arrElement.textContent = formatCurrency(data.arr.current_arr);
        }

        // Update churn rate
        const churnElement = document.getElementById('churn-rate');
        if (churnElement && data.churn) {
            churnElement.textContent = data.churn.churn_rate + '%';
        }

        // Update comparative metrics
        if (data.comparative) {
            updateComparativeMetrics(data.comparative);
        }
    }

    /**
     * Update comparative metrics (trends)
     */
    function updateComparativeMetrics(comparative) {
        // Revenue trend
        const revenueTrendElement = document.getElementById('revenue-trend');
        if (revenueTrendElement && comparative.revenue) {
            const trend = comparative.revenue.trend;
            const percentage = Math.abs(comparative.revenue.change_percentage);

            revenueTrendElement.innerHTML = `
                <i class="ki-duotone ki-arrow-${trend === 'up' ? 'up' : 'down'} fs-5 text-${trend === 'up' ? 'success' : 'danger'} ms-n1">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                ${percentage.toFixed(1)}%
            `;
        }

        // Customer trend
        const customerTrendElement = document.getElementById('customer-trend');
        if (customerTrendElement && comparative.customers) {
            const trend = comparative.customers.trend;
            const percentage = Math.abs(comparative.customers.change_percentage);

            customerTrendElement.innerHTML = `
                <i class="ki-duotone ki-arrow-${trend === 'up' ? 'up' : 'down'} fs-5 text-${trend === 'up' ? 'success' : 'danger'} ms-n1">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                ${percentage.toFixed(1)}%
            `;
        }
    }

    /**
     * Update notifications
     */
    function updateNotifications() {
        fetch('dist/api/notifications_api.php?action=get_unread')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.unread_count);
                    updateNotificationDropdown(data.data);
                }
            })
            .catch(error => {
                console.error('Error updating notifications:', error);
            });
    }

    /**
     * Update notification badge
     */
    function updateNotificationBadge(count) {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Update notification dropdown
     */
    function updateNotificationDropdown(notifications) {
        const container = document.getElementById('notification-list');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10">
                    <div class="text-gray-400 fs-6">Bildirim yok</div>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const typeClass = getNotificationTypeClass(notification.type);
            const timeAgo = getTimeAgo(notification.created_at);

            html += `
                <div class="d-flex flex-stack py-4 notification-item" data-id="${notification.notification_id}">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-35px me-4">
                            <span class="symbol-label bg-light-${typeClass}">
                                <i class="ki-duotone ki-notification fs-2 text-${typeClass}">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                        </div>
                        <div class="mb-0 me-2">
                            <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bold">${escapeHtml(notification.title)}</a>
                            <div class="text-gray-400 fs-7">${escapeHtml(notification.message)}</div>
                            <div class="text-gray-400 fs-8 mt-1">${timeAgo}</div>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-icon btn-active-light-primary mark-read-btn" data-id="${notification.notification_id}">
                        <i class="ki-duotone ki-check fs-2"></i>
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;

        // Attach event listeners
        attachNotificationEventListeners();
    }

    /**
     * Attach event listeners to notification items
     */
    function attachNotificationEventListeners() {
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const notificationId = this.getAttribute('data-id');
                markNotificationAsRead(notificationId);
            });
        });
    }

    /**
     * Mark notification as read
     */
    function markNotificationAsRead(notificationId) {
        const formData = new FormData();
        formData.append('action', 'mark_read');
        formData.append('notification_id', notificationId);

        fetch('dist/api/notifications_api.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove notification from UI
                    const item = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                    if (item) {
                        item.style.opacity = '0';
                        setTimeout(() => item.remove(), 300);
                    }

                    // Update immediately
                    updateNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
    }

    /**
     * Get notification type CSS class
     */
    function getNotificationTypeClass(type) {
        const typeMap = {
            'success': 'success',
            'warning': 'warning',
            'error': 'danger',
            'info': 'primary'
        };
        return typeMap[type] || 'primary';
    }

    /**
     * Get time ago string
     */
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Az önce';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' dakika önce';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' saat önce';
        if (seconds < 604800) return Math.floor(seconds / 86400) + ' gün önce';

        return date.toLocaleDateString('tr-TR');
    }

    /**
     * Format currency
     */
    function formatCurrency(value) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY',
            minimumFractionDigits: 0
        }).format(value);
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Public API
    return {
        start: start,
        stop: stop,
        updateMetrics: updateMetrics,
        updateNotifications: updateNotifications,
        markNotificationAsRead: markNotificationAsRead
    };
})();

// Auto-start on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    // Start real-time updates if on dashboard page
    if (document.getElementById('kt_app_content')) {
        DashboardRealtime.start();
    }
});

// Stop updates when page is hidden
document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        DashboardRealtime.stop();
    } else {
        DashboardRealtime.start();
    }
});
