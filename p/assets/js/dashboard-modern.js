/**
 * Modern Dashboard Animations
 * Number counters, chart enhancements, and interactive effects
 */

(function () {
    'use strict';

    /**
     * Animated Number Counter
     * Animates numbers from 0 to target value
     */
    function animateCounter(element, target, duration = 2000, decimals = 0) {
        const start = 0;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (easeOutExpo)
            const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);

            const current = start + (target - start) * easeProgress;

            if (decimals > 0) {
                element.textContent = current.toFixed(decimals);
            } else {
                element.textContent = Math.floor(current).toLocaleString('tr-TR');
            }

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                // Ensure final value is exact
                if (decimals > 0) {
                    element.textContent = target.toFixed(decimals);
                } else {
                    element.textContent = Math.floor(target).toLocaleString('tr-TR');
                }
            }
        }

        requestAnimationFrame(update);
    }

    /**
     * Initialize all number counters on page
     */
    function initCounters() {
        // Stat card numbers
        document.querySelectorAll('.stat-card-gradient .fs-2hx').forEach(el => {
            const text = el.textContent.trim();
            const number = parseFloat(text.replace(/[^0-9.]/g, ''));

            if (!isNaN(number)) {
                const hasDecimal = text.includes('.') || text.includes(',');
                const decimals = hasDecimal ? 2 : 0;
                el.textContent = '0';

                // Start animation after a short delay
                setTimeout(() => {
                    animateCounter(el, number, 2000, decimals);
                }, 100);
            }
        });

        // Large metric numbers
        document.querySelectorAll('.metric-number-huge').forEach(el => {
            const text = el.textContent.trim();
            const number = parseFloat(text.replace(/[^0-9.]/g, ''));

            if (!isNaN(number)) {
                const hasDecimal = text.includes('.') || text.includes(',');
                const decimals = hasDecimal ? 2 : 0;
                el.textContent = '0';

                setTimeout(() => {
                    animateCounter(el, number, 2500, decimals);
                }, 200);
            }
        });
    }

    /**
     * Create SVG health circle with gradient
     */
    function createHealthCircle(percentage) {
        const container = document.querySelector('.health-circle');
        if (!container) return;

        const radius = 50;
        const circumference = 2 * Math.PI * radius;
        const offset = circumference - (percentage / 100) * circumference;

        let gradientId, statusClass;
        if (percentage >= 90) {
            gradientId = 'healthGradientGreen';
            statusClass = 'healthy';
        } else if (percentage >= 70) {
            gradientId = 'healthGradientYellow';
            statusClass = 'warning';
        } else {
            gradientId = 'healthGradientRed';
            statusClass = 'critical';
        }

        const svg = `
            <svg width="120" height="120" viewBox="0 0 120 120">
                <defs>
                    <linearGradient id="healthGradientGreen" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#56ab2f;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#a8e063;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="healthGradientYellow" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#f46b45;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#eea849;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="healthGradientRed" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#eb3349;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#f45c43;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <circle class="health-circle-bg" cx="60" cy="60" r="${radius}" />
                <circle class="health-circle-progress ${statusClass}" 
                        cx="60" cy="60" r="${radius}"
                        stroke-dasharray="${circumference}"
                        stroke-dashoffset="${circumference}" />
            </svg>
            <div class="health-percentage">${percentage}%</div>
        `;

        container.innerHTML = svg;
        container.classList.add(statusClass);

        // Animate the circle
        setTimeout(() => {
            const circle = container.querySelector('.health-circle-progress');
            circle.style.strokeDashoffset = offset;
        }, 100);
    }

    /**
     * Add ripple effect to buttons
     */
    function addRippleEffect() {
        document.querySelectorAll('.btn-ripple').forEach(button => {
            button.addEventListener('click', function (e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');

                this.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    /**
     * Intersection Observer for scroll animations
     */
    function initScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        // Observe all cards
        document.querySelectorAll('.card, .timeline-item-modern').forEach(el => {
            observer.observe(el);
        });
    }

    /**
     * Period selector functionality
     */
    function initPeriodSelectors() {
        document.querySelectorAll('.period-selector .btn').forEach(btn => {
            btn.addEventListener('click', function () {
                // Remove active class from siblings
                this.parentElement.querySelectorAll('.btn').forEach(b => {
                    b.classList.remove('active');
                });

                // Add active class to clicked button
                this.classList.add('active');

                // Get period value
                const period = this.dataset.period;

                // Trigger chart update (if chart exists)
                const chartId = this.closest('.card').querySelector('canvas')?.id;
                if (chartId && window.DashboardCharts) {
                    // Reload chart with new period
                    console.log('Updating chart:', chartId, 'with period:', period);
                }
            });
        });
    }

    /**
     * Enhanced chart tooltips
     */
    function enhanceChartTooltips() {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            Chart.defaults.plugins.tooltip.titleColor = '#fff';
            Chart.defaults.plugins.tooltip.bodyColor = '#fff';
            Chart.defaults.plugins.tooltip.borderColor = 'rgba(255, 255, 255, 0.1)';
            Chart.defaults.plugins.tooltip.borderWidth = 1;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;
            Chart.defaults.plugins.tooltip.padding = 12;
            Chart.defaults.plugins.tooltip.displayColors = true;
            Chart.defaults.plugins.tooltip.boxPadding = 6;
        }
    }

    /**
     * Smooth scroll to element
     */
    function smoothScrollTo(element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    /**
     * Initialize all dashboard enhancements
     */
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        console.log('Initializing modern dashboard...');

        // Initialize counters
        initCounters();

        // Create health circle if element exists
        const healthScore = document.querySelector('[data-health-score]');
        if (healthScore) {
            const score = parseInt(healthScore.dataset.healthScore);
            createHealthCircle(score);
        }

        // Add ripple effects
        addRippleEffect();

        // Initialize scroll animations
        initScrollAnimations();

        // Initialize period selectors
        initPeriodSelectors();

        // Enhance chart tooltips
        enhanceChartTooltips();

        console.log('Modern dashboard initialized successfully!');
    }

    // Auto-initialize
    init();

    // Expose public API
    window.DashboardModern = {
        animateCounter: animateCounter,
        createHealthCircle: createHealthCircle,
        smoothScrollTo: smoothScrollTo
    };

})();
