<?php
/**
 * Prolyn Admin Panel - Customer Unified Logs
 * Tenant DB + Master DB loglarini birlikte gosterir.
 */

if (isset($_GET['export']) && (int)$_GET['export'] === 1) {
    require_once __DIR__ . '/../classes/allClass.php';
    require_once __DIR__ . '/../functions/combine.php';

    start_secure_session();
    oturumkontrol();

    $adminId = (int)($_SESSION['admin_id'] ?? 0);
    $rows = unified_logs_fetch($adminId, 2000, true);

    $filename = 'customer-unified-logs-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Kaynak DB', 'Kaynak Tablo', 'Tip', 'Aciklama', 'Istek Yontemi', 'Istek Yolu', 'Durum', 'Tarih', 'IP', 'LogID']);

    foreach ($rows as $log) {
        $typeLabel = match ((int)$log->log_type) {
            1 => 'Hata',
            2 => 'Uyari',
            3 => 'Islem',
            4 => 'Bilgi',
            5 => 'Debug',
            default => 'Bilinmiyor',
        };

        fputcsv($out, [
            $log->source_db ?? '-',
            $log->source_table ?? '-',
            $typeLabel,
            $log->log_description ?? '-',
            $log->request_method ?? 'GET',
            $log->request_path ?? '/',
            $log->log_status ?? 'info',
            $log->created_at ?? '-',
            $log->ip_address ?? '-',
            $log->log_id ?? '-',
        ]);
    }

    fclose($out);
    exit;
}

require_once __DIR__ . '/../config/header.php';
Auth::requireModule('view_logs');
require_once __DIR__ . '/../config/sidebar.php';

$adminId = (int)($_SESSION['admin_id'] ?? 0);
$logs = unified_logs_fetch($adminId, 1000, true);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Musteri Birlesik Loglari
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log Yonetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Musteri Birlesik Loglari</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="logs/customer_logs.php?export=1" class="btn btn-sm btn-light-primary">
                        <i class="ki-duotone ki-cloud-download fs-3"><span class="path1"></span><span class="path2"></span></i>
                        CSV Indir
                    </a>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Loglarda Ara..." />
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customer_unified_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Tarih</th>
                                        <th>Kaynak DB</th>
                                        <th>Tablo</th>
                                        <th>Tip</th>
                                        <th>Aciklama</th>
                                        <th>Istek</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                <?php if (!empty($logs)): ?>
                                    <?php foreach ($logs as $log): ?>
                                        <?php
                                            $typeBadge = match ((int)$log->log_type) {
                                                1 => 'badge-light-danger',
                                                2 => 'badge-light-warning',
                                                3 => 'badge-light-success',
                                                4 => 'badge-light-info',
                                                5 => 'badge-light-primary',
                                                default => 'badge-light-secondary',
                                            };

                                            $statusBadge = match (strtolower((string)($log->log_status ?? 'info'))) {
                                                'success' => 'badge-light-success',
                                                'error', 'failed' => 'badge-light-danger',
                                                'warning' => 'badge-light-warning',
                                                'pending' => 'badge-light-primary',
                                                default => 'badge-light-info',
                                            };
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800"><?php echo htmlspecialchars((string)($log->created_at ?? '-')); ?></div>
                                            </td>
                                            <td><span class="badge badge-light-dark"><?php echo htmlspecialchars((string)($log->source_db ?? '-')); ?></span></td>
                                            <td><span class="text-gray-700"><?php echo htmlspecialchars((string)($log->source_table ?? '-')); ?></span></td>
                                            <td>
                                                <span class="badge <?php echo $typeBadge; ?>">
                                                    <?php
                                                        echo match ((int)$log->log_type) {
                                                            1 => 'Hata',
                                                            2 => 'Uyari',
                                                            3 => 'Islem',
                                                            4 => 'Bilgi',
                                                            5 => 'Debug',
                                                            default => 'Bilinmiyor',
                                                        };
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800"><?php echo htmlspecialchars((string)($log->log_description ?? '-')); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light"><?php echo htmlspecialchars((string)($log->request_method ?? 'GET')); ?></span>
                                                <span class="text-gray-600"><?php echo htmlspecialchars((string)($log->request_path ?? '/')); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars(ucfirst((string)($log->log_status ?? 'info'))); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-8">Log kaydi bulunamadi.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../config/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.initDataTable === 'function') {
        window.initDataTable('#kt_customer_unified_logs_table', {
            order: [[0, 'desc']],
            stateSave: true
        });
    }
});
</script>
