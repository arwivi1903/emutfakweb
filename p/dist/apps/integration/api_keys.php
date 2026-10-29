<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
// api_keys tablosu mevcut
$keys = $db->getRows("SELECT a.*, c.company_name FROM api_keys a LEFT JOIN customers c ON a.customer_id = c.customer_id ORDER BY a.created_at DESC");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>API Anahtarları</h3></div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Müşteri</th>
                                    <th>Anahtar (Maskeli)</th>
                                    <th>İzinler</th>
                                    <th>Son Erişim</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($keys as $key): ?>
                                <tr>
                                    <td><?= htmlspecialchars($key->company_name) ?></td>
                                    <td><?= substr($key->api_key, 0, 8) ?>...</td>
                                    <td><?= htmlspecialchars($key->permissions) ?></td>
                                    <td><?= $key->last_used_at ?></td>
                                    <td>
                                        <span class="badge badge-light-<?= $key->status == 'active' ? 'success' : 'danger' ?>">
                                            <?= $key->status ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
