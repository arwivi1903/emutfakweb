<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$announcements = $db->getRows("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>Duyurular</h3></div>
                         <div class="card-toolbar">
                             <button type="button" class="btn btn-primary">Yeni Duyuru</button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Başlık</th>
                                    <th>Hedef Kitle</th>
                                    <th>Tür</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($announcements as $ann): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ann->title) ?></td>
                                    <td><?= ucfirst($ann->target_audience) ?></td>
                                    <td><span class="badge badge-light-<?= $ann->type ?>"><?= $ann->type ?></span></td>
                                    <td><?= date('d.m.Y', strtotime($ann->created_at)) ?></td>
                                    <td>
                                        <span class="badge badge-light-<?= $ann->is_active ? 'success' : 'secondary' ?>">
                                            <?= $ann->is_active ? 'Yayında' : 'Taslak' ?>
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
