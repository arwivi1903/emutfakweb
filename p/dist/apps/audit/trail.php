<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$audit_logs = [];
try {
    $audit_logs = $db->getRows("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 50");
} catch(Exception $e) {
    $error = "Tablo bulunamadı (audit_logs).";
}
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>Sistem Denetim Logları (Audit Trail)</h3></div>
                    </div>
                    <div class="card-body pt-0">
                         <?php if (isset($error)): ?>
                            <div class="alert alert-warning"><?= $error ?></div>
                        <?php else: ?>
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Kullanıcı</th>
                                    <th>Eylem</th>
                                    <th>Etkilenen Kayıt</th>
                                    <th>IP Adresi</th>
                                    <th>Zaman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($audit_logs)): ?>
                                    <tr><td colspan="5" class="text-center">Kayıt yok.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($audit_logs as $log): ?>
                                    <tr>
                                        <td><?= $log->user_id ?></td>
                                        <td><?= htmlspecialchars($log->action) ?></td>
                                        <td><?= htmlspecialchars($log->entity_type . ' #' . $log->entity_id) ?></td>
                                        <td><?= $log->ip_address ?></td>
                                        <td><?= $log->created_at ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
