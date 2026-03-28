<?php
/**
 * Prolyn Admin Panel - Modern Dashboard
 * prolynweb/p/index.php
 */

// Header ve sidebar dahil et
require_once 'config/header.php';
require_once 'config/sidebar.php';

// Dashboard istatistikleri için örnek veriler
$stats = [
    'total_projects' => 156,
    'active_users' => 2847,
    'revenue' => 125840,
    'growth' => 23.5
];
?>

<style>
/* Modern Dashboard Custom Styles */
:root {
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-success: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --gradient-danger: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.08);
    --shadow-hover: 0 15px 50px rgba(0, 0, 0, 0.15);
}

/* Animated Gradient Background */
.dashboard-header {
    background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    border-radius: 20px;
    padding: 3rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.dashboard-header-content {
    position: relative;
    z-index: 1;
}

/* Glassmorphism Stats Cards */
.stat-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-soft);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(255, 255, 255, 0.3);
    position: relative;
    overflow: hidden;
}

[data-bs-theme="dark"] .stat-card {
    background: rgba(30, 30, 40, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.6s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    position: relative;
    animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.stat-icon.primary {
    background: var(--gradient-primary);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.stat-icon.success {
    background: var(--gradient-success);
    box-shadow: 0 10px 30px rgba(240, 147, 251, 0.4);
}

.stat-icon.info {
    background: var(--gradient-info);
    box-shadow: 0 10px 30px rgba(79, 172, 254, 0.4);
}

.stat-icon.warning {
    background: var(--gradient-warning);
    box-shadow: 0 10px 30px rgba(250, 112, 154, 0.4);
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    animation: countUp 2s ease-out;
}

@keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stat-change {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 0.8rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 0.8rem;
}

.stat-change.positive {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.stat-change.negative {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Chart Cards */
.chart-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-soft);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

[data-bs-theme="dark"] .chart-card {
    background: rgba(30, 30, 40, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.chart-card:hover {
    box-shadow: var(--shadow-hover);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.chart-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
}

[data-bs-theme="dark"] .chart-title {
    color: #f1f5f9;
}

/* Activity Feed */
.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1.2rem;
    border-radius: 12px;
    margin-bottom: 0.8rem;
    background: rgba(102, 126, 234, 0.05);
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.activity-item:hover {
    background: rgba(102, 126, 234, 0.1);
    border-left-color: #667eea;
    transform: translateX(5px);
}

.activity-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.3rem;
}

[data-bs-theme="dark"] .activity-title {
    color: #f1f5f9;
}

.activity-time {
    font-size: 0.85rem;
    color: #64748b;
}

/* Progress Bars */
.modern-progress {
    height: 10px;
    border-radius: 50px;
    background: rgba(102, 126, 234, 0.1);
    overflow: hidden;
    margin-top: 0.8rem;
}

.modern-progress-bar {
    height: 100%;
    border-radius: 50px;
    background: var(--gradient-primary);
    transition: width 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    animation: progressSlide 2s ease-out;
}

@keyframes progressSlide {
    from { width: 0; }
}

/* Quick Actions */
.quick-action {
    background: #ffffff;
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

[data-bs-theme="dark"] .quick-action {
    background: rgba(30, 30, 40, 0.95);
}

.quick-action:hover {
    border-color: #667eea;
    transform: translateY(-5px);
    box-shadow: var(--shadow-soft);
}

.quick-action-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.8rem;
}

.quick-action-label {
    font-weight: 600;
    color: #475569;
}

[data-bs-theme="dark"] .quick-action-label {
    color: #cbd5e1;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-header {
        padding: 2rem;
    }
    
    .stat-value {
        font-size: 2rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
.animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
.animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
.animate-delay-4 { animation-delay: 0.4s; opacity: 0; }
</style>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Dashboard
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Dashboard</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <!-- Animated Header -->
                <div class="dashboard-header animate-in">
                    <div class="dashboard-header-content">
                        <h1 class="text-white fw-bold mb-3" style="font-size: 2.5rem;">
                            Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>! 👋
                        </h1>
                        <p class="text-white fs-5 mb-0 opacity-75">
                            İşte bugünkü performans özetiniz
                        </p>
                    </div>
                </div>

                <!-- Stats Cards Row -->
                <div class="row g-4 mb-5">
                    <!-- Total Projects -->
                    <div class="col-xl-3 col-md-6 animate-in animate-delay-1">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="ki-duotone ki-abstract-26 text-white">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['total_projects']); ?></div>
                            <div class="stat-label">Toplam Proje</div>
                            <div class="stat-change positive">
                                <i class="ki-duotone ki-arrow-up fs-6"></i>
                                <span>12% artış</span>
                            </div>
                            <div class="modern-progress">
                                <div class="modern-progress-bar" style="width: 75%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Users -->
                    <div class="col-xl-3 col-md-6 animate-in animate-delay-2">
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="ki-duotone ki-profile-user text-white">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['active_users']); ?></div>
                            <div class="stat-label">Aktif Kullanıcı</div>
                            <div class="stat-change positive">
                                <i class="ki-duotone ki-arrow-up fs-6"></i>
                                <span>8% artış</span>
                            </div>
                            <div class="modern-progress">
                                <div class="modern-progress-bar" style="width: 85%; background: var(--gradient-success);"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <div class="col-xl-3 col-md-6 animate-in animate-delay-3">
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="ki-duotone ki-dollar text-white">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                            <div class="stat-value">₺<?php echo number_format($stats['revenue']); ?></div>
                            <div class="stat-label">Toplam Gelir</div>
                            <div class="stat-change positive">
                                <i class="ki-duotone ki-arrow-up fs-6"></i>
                                <span>23% artış</span>
                            </div>
                            <div class="modern-progress">
                                <div class="modern-progress-bar" style="width: 92%; background: var(--gradient-info);"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Growth Rate -->
                    <div class="col-xl-3 col-md-6 animate-in animate-delay-4">
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="ki-duotone ki-chart-simple text-white">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </div>
                            <div class="stat-value"><?php echo $stats['growth']; ?>%</div>
                            <div class="stat-label">Büyüme Oranı</div>
                            <div class="stat-change positive">
                                <i class="ki-duotone ki-arrow-up fs-6"></i>
                                <span>5% artış</span>
                            </div>
                            <div class="modern-progress">
                                <div class="modern-progress-bar" style="width: 68%; background: var(--gradient-warning);"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-5">
                    <!-- Revenue Chart -->
                    <div class="col-xl-8">
                        <div class="chart-card animate-in">
                            <div class="chart-header">
                                <h3 class="chart-title">Gelir Analizi</h3>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-light-primary active">Haftalık</button>
                                    <button class="btn btn-light">Aylık</button>
                                    <button class="btn btn-light">Yıllık</button>
                                </div>
                            </div>
                            <div id="revenue_chart" style="height: 350px;">
                                <canvas id="revenueCanvas"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Feed -->
                    <div class="col-xl-4">
                        <div class="chart-card animate-in">
                            <div class="chart-header">
                                <h3 class="chart-title">Son Aktiviteler</h3>
                                <a href="#" class="text-primary fw-semibold">Tümünü Gör</a>
                            </div>
                            <div class="activity-feed">
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: var(--gradient-primary);">
                                        <i class="ki-duotone ki-document text-white fs-3"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Yeni proje oluşturuldu</div>
                                        <div class="activity-time">2 saat önce</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: var(--gradient-success);">
                                        <i class="ki-duotone ki-user-tick text-white fs-3"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">5 yeni kullanıcı kaydı</div>
                                        <div class="activity-time">4 saat önce</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: var(--gradient-info);">
                                        <i class="ki-duotone ki-wallet text-white fs-3"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Ödeme alındı: ₺15,000</div>
                                        <div class="activity-time">6 saat önce</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: var(--gradient-warning);">
                                        <i class="ki-duotone ki-notification text-white fs-3"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Sistem güncellemesi</div>
                                        <div class="activity-time">1 gün önce</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: var(--gradient-danger);">
                                        <i class="ki-duotone ki-shield-tick text-white fs-3"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Güvenlik taraması tamamlandı</div>
                                        <div class="activity-time">2 gün önce</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <div class="chart-card animate-in">
                            <div class="chart-header mb-4">
                                <h3 class="chart-title">Hızlı İşlemler</h3>
                            </div>
                            <div class="row g-4">
                                <div class="col-xl-2 col-md-4 col-6">
                                    <div class="quick-action">
                                        <div class="quick-action-icon" style="background: var(--gradient-primary);">
                                            <i class="ki-duotone ki-add-files text-white">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </div>
                                        <div class="quick-action-label">Yeni Proje</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4 col-6">
                                    <div class="quick-action">
                                        <div class="quick-action-icon" style="background: var(--gradient-success);">
                                            <i class="ki-duotone ki-user-plus text-white">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </div>
                                        <div class="quick-action-label">Kullanıcı Ekle</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4 col-6">
                                    <div class="quick-action">
                                        <div class="quick-action-icon" style="background: var(--gradient-info);">
                                            <i class="ki-duotone ki-chart-line text-white">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                        <div class="quick-action-label">Raporlar</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4 col-6">
                                    <div class="quick-action">
                                        <div class="quick-action-icon" style="background: var(--gradient-warning);">
                                            <i class="ki-duotone ki-setting-2 text-white">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                        <div class="quick-action-label">Ayarlar</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4 col-6">
                                    <div class="quick-action">
                                        <div class="quick-action-icon" style="background: var(--gradient-danger);">
                                            <i class="ki-duotone ki-message-text text-white">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </div>
                                        <div class="quick-action-label">Mesajlar</div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4 col-6">
                                    <div class="quick-action">
                                        <div class="quick-action-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                                            <i class="ki-duotone ki-calendar text-white">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                        <div class="quick-action-label">Takvim</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueCanvas');
    if (ctx) {
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.8)');
        gradient.addColorStop(1, 'rgba(118, 75, 162, 0.2)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'],
                datasets: [{
                    label: 'Gelir (₺)',
                    data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                    borderColor: '#667eea',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#667eea',
                    pointBorderWidth: 3,
                    pointHoverBackgroundColor: '#667eea',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return '₺' + context.parsed.y.toLocaleString('tr-TR');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '₺' + (value / 1000) + 'k';
                            },
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
});
</script>

<?php
// Footer dahil et
require_once 'config/footer.php';
?>
