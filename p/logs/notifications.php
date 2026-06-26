<?php
/**
 * Prolyn Admin Panel - In-App Notifications (Uygulama Bildirimleri)
 * prolynweb/p/logs/notifications.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Bildirimleri çek (yoneticiler tablosuyla JOIN yaparak kime gittiğini gösterelim)
$query = "
    SELECT b.*, y.full_name as admin_name 
    FROM bildirimler b
    LEFT JOIN yoneticiler y ON b.admin_id = y.admin_id
    ORDER BY b.notification_id DESC 
    LIMIT 1000
";
$notifications = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Uygulama İçi Bildirimler
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Uygulama Bildirimleri</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Bildirim Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Gönderim Tarihi</th>
                                        <th>Alıcı Yönetici</th>
                                        <th>Başlık / Tür</th>
                                        <th>İlişkili Kayıt</th>
                                        <th>Okunma Durumu</th>
                                        <th class="text-end">İçerik Detayı</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($notifications)): ?>
                                        <?php foreach ($notifications as $notif): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800"><?php echo date('d.m.Y', strtotime($notif['created_at'])); ?></div>
                                                <div class="text-muted fs-7"><?php echo date('H:i:s', strtotime($notif['created_at'])); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($notif['admin_name'] ?? 'Sistem Geneli'); ?></span>
                                                <?php if($notif['admin_id']): ?>
                                                    <div class="text-muted fs-8">ID: <?php echo $notif['admin_id']; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="text-gray-800 fw-bold mb-1"><?php echo htmlspecialchars($notif['title']); ?></div>
                                                <?php 
                                                    $type_class = 'badge-light-primary';
                                                    if ($notif['type'] == 'error') $type_class = 'badge-light-danger';
                                                    elseif ($notif['type'] == 'warning') $type_class = 'badge-light-warning';
                                                    elseif ($notif['type'] == 'success') $type_class = 'badge-light-success';
                                                ?>
                                                <span class="badge <?php echo $type_class; ?> text-uppercase"><?php echo htmlspecialchars($notif['type']); ?></span>
                                            </td>
                                            <td>
                                                <?php if($notif['related_entity_type']): ?>
                                                    <span class="badge badge-outline badge-primary"><?php echo htmlspecialchars($notif['related_entity_type']); ?> #<?php echo $notif['related_entity_id']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($notif['is_read']): ?>
                                                    <div class="badge badge-light-success mb-1">Okundu</div>
                                                    <div class="text-muted fs-8"><?php echo date('d.m.Y H:i', strtotime($notif['read_at'])); ?></div>
                                                <?php else: ?>
                                                    <div class="badge badge-light-warning">Bekliyor</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-light-info view-notif-details"
                                                    data-title="<?php echo htmlspecialchars($notif['title']); ?>"
                                                    data-message="<?php echo htmlspecialchars($notif['message']); ?>"
                                                    data-url="<?php echo htmlspecialchars($notif['action_url'] ?? ''); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#kt_modal_notif_details">
                                                    <i class="ki-duotone ki-eye fs-3"></i> Oku
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Kayıt bulunamadı.</td></tr>
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

<!-- Modal: Bildirim Detayları -->
<div class="modal fade" id="kt_modal_notif_details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_notif_title">Bildirim Detayı</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body py-10 px-lg-17">
                <div id="modal_notif_message" class="fs-5 text-gray-800 mb-6" style="white-space: pre-wrap;"></div>
                
                <div id="modal_notif_url_container" style="display:none;">
                    <hr>
                    <h5 class="text-muted">Aksiyon URL:</h5>
                    <a href="#" id="modal_notif_url" class="btn btn-sm btn-light-primary mt-2" target="_blank">Git</a>
                </div>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../config/footer.php'; ?>

<script>
document.querySelectorAll('.view-notif-details').forEach(btn => {
    btn.addEventListener('click', function() {
        const title = this.getAttribute('data-title');
        const message = this.getAttribute('data-message');
        const url = this.getAttribute('data-url');
        
        document.getElementById('modal_notif_title').innerText = title;
        document.getElementById('modal_notif_message').innerText = message;
        
        const urlContainer = document.getElementById('modal_notif_url_container');
        const urlBtn = document.getElementById('modal_notif_url');
        
        if (url && url.trim() !== '') {
            urlBtn.href = url;
            urlBtn.innerText = url;
            urlContainer.style.display = 'block';
        } else {
            urlContainer.style.display = 'none';
        }
    });
});
</script>
