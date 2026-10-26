<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$logs = $db->getRows("
    SELECT l.*, a.username 
    FROM admin_logs l 
    LEFT JOIN admins a ON l.admin_id = a.admin_id 
    ORDER BY l.created_at DESC 
    LIMIT 100
");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card pt-4">
                    <div class="card-header border-0">
                        <div class="card-title">
                            <h2>Sistem Logları</h2>
                        </div>
                    </div>
                    <div class="card-body py-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 fw-semibold gy-5" id="kt_table_users_logs">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-100px">Admin</th>
                                        <th class="min-w-100px">Tip</th>
                                        <th class="min-w-200px">İşlem</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="min-w-100px">IP</th>
                                        <th class="min-w-150px">Tarih</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600">
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log->username ?? 'Sistem') ?></td>
                                        <td>
                                            <?php 
                                            // 1=Error, 2=Warning, 3=Admin Action, 4=Info, 5=Debug
                                            if($log->log_type == 1) echo '<div class="badge badge-light-danger">Hata</div>';
                                            elseif($log->log_type == 2) echo '<div class="badge badge-light-warning">Uyarı</div>';
                                            else echo '<div class="badge badge-light-info">Bilgi</div>';
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($log->log_description) ?></td>
                                        <td>
                                            <?php 
                                            if($log->log_status == 'success') echo '<div class="badge badge-light-success">Başarılı</div>';
                                            elseif($log->log_status == 'error') echo '<div class="badge badge-light-danger">Hata</div>';
                                            else echo '<div class="badge badge-light-secondary">'.htmlspecialchars($log->log_status).'</div>';
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($log->ip_address) ?></td>
                                        <td><?= htmlspecialchars($log->created_at) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
