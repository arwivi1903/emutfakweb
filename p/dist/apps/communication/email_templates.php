<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$templates = $db->getRows("SELECT * FROM email_templates ORDER BY name ASC");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>E-posta Şablonları</h3></div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Şablon Adı</th>
                                    <th>Konu</th>
                                    <th>Son Güncelleme</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $tpl): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tpl->name) ?></td>
                                    <td><?= htmlspecialchars($tpl->subject) ?></td>
                                    <td><?= $tpl->updated_at ?></td>
                                    <td><a href="#" class="btn btn-sm btn-light btn-active-light-primary">Düzenle</a></td>
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
