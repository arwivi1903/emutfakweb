<?php
require_once __DIR__ . '/../../classes/database.class.php';
require_once __DIR__ . '/../../classes/dashboard_cache.class.php';
require_once __DIR__ . '/../../config/header.php';
require_once __DIR__ . '/../../config/sidebar.php';

// Handle cache actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'flush_all':
                DashboardCache::flush();
                $message = 'Tüm cache temizlendi!';
                $messageType = 'success';
                break;
            
            case 'clear_specific':
                if (isset($_POST['cache_key'])) {
                    DashboardCache::forget($_POST['cache_key']);
                    $message = 'Cache anahtarı temizlendi: ' . htmlspecialchars($_POST['cache_key']);
                    $messageType = 'success';
                }
                break;
        }
    }
}

// Get cache statistics
$cacheStats = DashboardCache::stats();

// Common cache keys
$commonCacheKeys = [
    'active_customers' => 'Aktif Müşteriler',
    'active_subscriptions' => 'Aktif Abonelikler',
    'today_revenue_' . date('Y-m-d') => 'Bugünkü Gelir',
    'open_tickets' => 'Açık Talepler',
    'recent_errors' => 'Son Hatalar',
    'recent_logs' => 'Son Loglar',
    'revenue_chart_30d' => 'Gelir Grafiği (30 gün)',
    'customer_growth_12m' => 'Müşteri Büyümesi (12 ay)',
    'mrr_metrics' => 'MRR Metrikleri',
    'arr_metrics' => 'ARR Metrikleri',
    'comparative_metrics' => 'Karşılaştırmalı Metrikler',
    'ticket_analytics' => 'Ticket Analitiği',
    'email_queue_status' => 'Email Kuyruğu Durumu',
    'system_health' => 'Sistem Sağlığı',
    'payment_methods_30d' => 'Ödeme Yöntemleri (30 gün)',
    'customer_segmentation' => 'Müşteri Segmentasyonu'
];
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Cache Yönetimi</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="../../index.php" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Ayarlar</li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Cache Yönetimi</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> d-flex align-items-center mb-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-<?= $messageType ?> me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div class="d-flex flex-column">
                        <span><?= $message ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Cache Statistics -->
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <div class="col-xl-3">
                        <div class="card card-flush h-md-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex flex-column mb-7">
                                    <span class="text-gray-800 fs-2hx fw-bold"><?= $cacheStats['total_files'] ?></span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Toplam Cache Dosyası</span>
                                </div>
                                <i class="ki-duotone ki-files fs-3x text-primary opacity-50">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3">
                        <div class="card card-flush h-md-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex flex-column mb-7">
                                    <span class="text-gray-800 fs-2hx fw-bold"><?= $cacheStats['total_size_kb'] ?> KB</span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Toplam Boyut</span>
                                </div>
                                <i class="ki-duotone ki-cloud fs-3x text-success opacity-50">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3">
                        <div class="card card-flush h-md-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex flex-column mb-7">
                                    <span class="text-gray-800 fs-6 fw-bold"><?= $cacheStats['oldest_cache'] ?? 'N/A' ?></span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">En Eski Cache</span>
                                </div>
                                <i class="ki-duotone ki-time fs-3x text-warning opacity-50">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3">
                        <div class="card card-flush h-md-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex flex-column mb-7">
                                    <span class="text-gray-800 fs-6 fw-bold"><?= $cacheStats['newest_cache'] ?? 'N/A' ?></span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">En Yeni Cache</span>
                                </div>
                                <i class="ki-duotone ki-calendar fs-3x text-info opacity-50">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Management Actions -->
                <div class="row g-5 g-xl-10">
                    <div class="col-xl-6">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Tüm Cache'i Temizle</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Dashboard'daki tüm cache verilerini sil</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <p class="text-gray-600 fs-6 mb-5">
                                    Bu işlem tüm cache dosyalarını silecektir. Sonraki sayfa yüklemesi daha yavaş olabilir ancak veriler güncellenecektir.
                                </p>
                                <form method="POST" onsubmit="return confirm('Tüm cache temizlenecek. Emin misiniz?');">
                                    <input type="hidden" name="action" value="flush_all">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ki-duotone ki-trash fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                        Tüm Cache'i Temizle
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Belirli Cache Anahtarını Temizle</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Sadece seçili veriyi güncelle</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="clear_specific">
                                    <div class="mb-5">
                                        <label class="form-label">Cache Anahtarı Seç</label>
                                        <select name="cache_key" class="form-select" required>
                                            <option value="">Seçiniz...</option>
                                            <?php foreach ($commonCacheKeys as $key => $label): ?>
                                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ki-duotone ki-eraser fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                        </i>
                                        Seçili Cache'i Temizle
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Information -->
                <div class="row g-5 g-xl-10 mt-5">
                    <div class="col-xl-12">
                        <div class="card card-flush">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Cache Anahtarları ve TTL Süreleri</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Sistemdeki cache anahtarları ve yaşam süreleri</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th class="min-w-200px">Cache Anahtarı</th>
                                                <th class="min-w-150px">Açıklama</th>
                                                <th class="min-w-100px">TTL (Saniye)</th>
                                                <th class="min-w-100px">TTL (Dakika)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code>active_customers</code></td>
                                                <td>Aktif müşteri sayısı</td>
                                                <td>120</td>
                                                <td>2 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>active_subscriptions</code></td>
                                                <td>Aktif abonelik sayısı</td>
                                                <td>120</td>
                                                <td>2 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>today_revenue_<?= date('Y-m-d') ?></code></td>
                                                <td>Bugünkü gelir toplamı</td>
                                                <td>300</td>
                                                <td>5 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>open_tickets</code></td>
                                                <td>Açık destek talepleri</td>
                                                <td>120</td>
                                                <td>2 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>recent_errors</code></td>
                                                <td>Son sistem hataları</td>
                                                <td>60</td>
                                                <td>1 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>recent_logs</code></td>
                                                <td>Son admin logları</td>
                                                <td>60</td>
                                                <td>1 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>revenue_chart_30d</code></td>
                                                <td>30 günlük gelir grafiği</td>
                                                <td>300</td>
                                                <td>5 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>customer_growth_12m</code></td>
                                                <td>12 aylık müşteri büyümesi</td>
                                                <td>300</td>
                                                <td>5 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>mrr_metrics</code></td>
                                                <td>MRR metrikleri</td>
                                                <td>300</td>
                                                <td>5 dakika</td>
                                            </tr>
                                            <tr>
                                                <td><code>arr_metrics</code></td>
                                                <td>ARR metrikleri</td>
                                                <td>300</td>
                                                <td>5 dakika</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once '../../config/footer.php'; ?>

<script>
// Auto-refresh stats every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
</script>
