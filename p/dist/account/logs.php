<?php
// CSV export must run before any HTML output
if (isset($_GET['export']) && (int)$_GET['export'] === 1) {
    require_once __DIR__ . '/../../classes/allClass.php';
    require_once __DIR__ . '/../../functions/combine.php';

    $db = new Database();
    start_secure_session();
    oturumkontrol();

    $logs = $db->getRows(
        "SELECT LogID, LogType, LogDesc, LogStatus, UserID, IpAddress, UserAgent, RequestMethod, RequestPath, LogDate 
         FROM admin_logsnew 
         WHERE UserID = ? 
         ORDER BY LogDate DESC",
        [$_SESSION['admin_id']]
    );

    $filename = 'logs-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Excel uyumu için BOM ekle
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Tip', 'Açıklama', 'İstek Yöntemi', 'İstek Yolu', 'Durum', 'Tarih', 'IP', 'User Agent', 'LogID']);

    foreach ($logs as $log) {
        $typeLabel = match($log->LogType) {
            1 => 'Hata',
            2 => 'Uyarı',
            3 => 'Başarılı',
            4 => 'Bilgi',
            5 => 'Hata Ayıklama',
            default => 'Bilinmiyor',
        };

        $statusLabel = ucfirst($log->LogStatus ?? 'info');

        fputcsv($out, [
            $typeLabel,
            $log->LogDesc ?? '-',
            $log->RequestMethod ?? 'GET',
            $log->RequestPath ?? '/',
            $statusLabel,
            $log->LogDate,
            $log->IpAddress ?? '-',
            $log->UserAgent ?? '-',
            $log->LogID,
        ]);
    }

    fclose($out);
    exit;
}

require_once '../../config/header.php'; 
require_once '../../config/sidebar.php'; 
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <?php require_once 'account_header.php'; ?>

                <!-- <div class="card mb-5 mb-lg-10">
                    <div class="card-header">
                        <div class="card-title">
                            <h3>Login Sessions</h3>
                        </div>
                        <div class="card-toolbar">
                            <div class="my-1 me-4">

                                <select class="form-select form-select-sm form-select-solid w-125px"
                                    data-control="select2" data-placeholder="Select Hours" data-hide-search="true">
                                    <option value="1" selected="selected">1 Saat</option>
                                    <option value="2">6 Saat</option>
                                    <option value="3">12 Saat</option>
                                    <option value="4">24 Saat</option>
                                </select>
                            </div>
                            <a href="#" class="btn btn-sm btn-primary my-1">Tümünü Görüntüle</a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-bordered table-row-solid gy-4 gs-9">
                                <thead class="border-gray-200 fs-5 fw-semibold bg-lighten">
                                    <tr>
                                        <th class="min-w-250px">Konum</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="min-w-150px">Cihaz</th>
                                        <th class="min-w-150px">IP Adresi</th>
                                        <th class="min-w-150px">Zaman</th>
                                    </tr>
                                </thead>

                                <tbody class="fw-6 fw-semibold text-gray-600">
                                    <?php ?>
                                    <tr>
                                        <td>
                                            <a href="#" class="text-hover-primary text-gray-600">USA(5)</a>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-success fs-7 fw-bold">OK</span>
                                        </td>
                                        <td>Chrome - Windows</td>
                                        <td>236.125.56.78</td>
                                        <td>2 mins ago</td>
                                    </tr>
                                    <?php ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->

                <div class="card pt-4">
                    <div class="card-header border-0">
                        <div class="card-title">
                            <h2>Logs</h2>
                        </div>

                        <div class="card-toolbar">
                            <a href="dist/account/logs.php?export=1" class="btn btn-sm btn-light-primary">
                                <i class="ki-duotone ki-cloud-download fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>Kayıtları İndir</a>
                        </div>
                    </div>

                    <div class="card-body py-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fw-semibold text-gray-600 fs-6 gy-5"
                                id="kt_table_customers_logs">
                                <thead class="border-gray-200 fs-6 fw-semibold bg-lighten">
                                    <tr>
                                        <th class="min-w-100px">Tip</th>
                                        <th class="min-w-200px">Açıklama</th>
                                        <th class="min-w-150px">İstek</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="text-end min-w-150px">Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
									<?php
									// Giriş denemesi için admin bilgilerini al
									$logcek = $db->getRows("SELECT
                                                                LogID,
                                                                LogType,
                                                                LogDesc,
                                                                LogStatus,
                                                                UserID,
                                                                IpAddress,
                                                                UserAgent,
                                                                RequestMethod,
                                                                RequestPath,
                                                                OldValue,
                                                                NewValue,
                                                                EntityType,
                                                                EntityID,
                                                                AdditionalData,
                                                                LogDate 
                                                            FROM
                                                                admin_logsnew 
                                                            WHERE
                                                                UserID = ? 
                                                            ORDER BY LogDate DESC", [$_SESSION['admin_id']]);

									foreach ($logcek as $log) {
                                        // Badge rengini LogType'a göre belirle
                                        $badgeClass = match($log->LogType) {
                                            1 => 'badge-light-danger',
                                            2 => 'badge-light-warning',
                                            3 => 'badge-light-success',
                                            4 => 'badge-light-info',
                                            5 => 'badge-light-primary',
                                            default => 'badge-light-secondary',
                                        };
                                        
                                        // Status badge rengi
                                        $statusBadge = match(strtolower($log->LogStatus ?? 'info')) {
                                            'success' => 'badge-light-success',
                                            'error', 'failed' => 'badge-light-danger',
                                            'warning' => 'badge-light-warning',
                                            'pending' => 'badge-light-primary',
                                            default => 'badge-light-info',
                                        };
									?>
                                    <tr>
                                        <td class="min-w-100px">
                                            <div class="badge <?php echo $badgeClass; ?>">
                                            <?php 
                                            echo match($log->LogType) {
                                                1 => 'Hata',
                                                2 => 'Uyarı',
                                                3 => 'Başarılı',
                                                4 => 'Bilgi',
                                                5 => 'Hata Ayıklama',
                                                default => 'Bilinmiyor',
                                            };
                                            ?></div>
                                        </td>

                                        <td class="min-w-200px">
                                            <span class="text-gray-800"><?php echo htmlspecialchars($log->LogDesc ?? '-'); ?></span>
                                        </td>

                                        <td class="min-w-150px">
                                            <span class="badge badge-light"><?php echo htmlspecialchars($log->RequestMethod ?? 'GET'); ?></span>
                                            <span class="text-gray-600"><?php echo htmlspecialchars($log->RequestPath ?? '/'); ?></span>
                                        </td>

                                        <td class="min-w-100px">
                                            <div class="badge <?php echo $statusBadge; ?>">
                                                <?php echo ucfirst($log->LogStatus ?? 'info'); ?>
                                            </div>
                                        </td>

                                        <td class="pe-0 text-end min-w-150px"><?php echo tr_datetime($log->LogDate); ?></td>
                                    </tr>
									<?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.initDataTable === 'function') {
            window.initDataTable('#kt_table_customers_logs', {
                order: [[4, 'desc']],
                stateSave: true
            });
        }
    });
    </script>
    <?php 
require_once '../../config/footer.php'; 
?>