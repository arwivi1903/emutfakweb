<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$webhooks = $db->getRows("SELECT * FROM webhooks ORDER BY created_at DESC");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>Webhook Yönetimi</h3></div>
                        <div class="card-toolbar">
                             <button type="button" class="btn btn-primary">Yeni Webhook</button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>URL</th>
                                    <th>Event</th>
                                    <th>Son Başarı</th>
                                    <th>Son Hata</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($webhooks)): ?>
                                    <?php foreach ($webhooks as $hook): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($hook->url) ?></td>
                                        <td><?= htmlspecialchars($hook->event) ?></td>
                                        <td><?= $hook->last_success_at ?? '-' ?></td>
                                        <td><?= $hook->last_failure_at ?? '-' ?></td>
                                        <td>
                                            <span class="badge badge-light-<?= $hook->status == 'active' ? 'success' : 'danger' ?>">
                                                <?= $hook->status ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">Kayıtlı webhook bulunamadı.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
