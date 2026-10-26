<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

// Scaffold: Assuming feature_usage might not allow SELECT * immediately if table missing, 
// using try/catch or just showing mock if empty.
$db = new Database();
$usage_data = [];
try {
    $usage_data = $db->getRows("SELECT f.*, c.company_name FROM feature_usage f JOIN customers c ON f.customer_id = c.customer_id ORDER BY f.last_used_at DESC LIMIT 50");
} catch (Exception $e) {
    $error = "Tablo bulunamadı veya erişim hatası.";
}
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>Özellik Kullanım Takibi</h3></div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-warning"><?= $error ?> (feature_usage tablosu kontrol edilmeli)</div>
                        <?php else: ?>
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th>Müşteri</th>
                                        <th>Özellik</th>
                                        <th>Kullanım</th>
                                        <th>Limit</th>
                                        <th>Son Kullanım</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($usage_data)): ?>
                                        <?php foreach ($usage_data as $usage): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($usage->company_name) ?></td>
                                            <td><?= htmlspecialchars($usage->feature_key) ?></td>
                                            <td><?= $usage->used_amount ?></td>
                                            <td><?= $usage->limit_amount ?></td>
                                            <td><?= $usage->last_used_at ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">Veri bulunamadı.</td></tr>
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
