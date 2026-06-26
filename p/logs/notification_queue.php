<?php
/**
 * Prolyn Admin Panel - Notification Queue (Bildirim Kuyruğu)
 * prolynweb/p/logs/notification_queue.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Bildirim kuyruğunu çek
$query = "
    SELECT * 
    FROM bildirim_kuyrugu 
    ORDER BY id DESC 
    LIMIT 1000
";
$queue = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Bildirim Kuyruğu (SMS/Email)
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Bildirim Kuyruğu</li>
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
                                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Kuyrukta Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Oluşturulma / Gönderim</th>
                                        <th>Kanal</th>
                                        <th>Alıcı</th>
                                        <th>Konu</th>
                                        <th>Durum (Deneme)</th>
                                        <th class="text-end">Mesaj & Hata</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($queue)): ?>
                                        <?php foreach ($queue as $q): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fs-7">Oluş: <?php echo date('d.m.Y H:i', strtotime($q['created_at'])); ?></div>
                                                <div class="text-muted fs-8">Gön: <?php echo $q['sent_at'] ? date('d.m.Y H:i', strtotime($q['sent_at'])) : 'Bekliyor'; ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $channel_class = 'badge-light-primary';
                                                    if ($q['channel'] == 'email') $channel_class = 'badge-light-info';
                                                    elseif ($q['channel'] == 'sms') $channel_class = 'badge-light-success';
                                                ?>
                                                <span class="badge <?php echo $channel_class; ?> text-uppercase"><?php echo htmlspecialchars($q['channel']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($q['recipient']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?php echo htmlspecialchars($q['subject'] ?? '-'); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-warning';
                                                    if ($q['status'] == 'sent') $status_class = 'badge-light-success';
                                                    elseif ($q['status'] == 'failed') $status_class = 'badge-light-danger';
                                                ?>
                                                <div class="badge <?php echo $status_class; ?> text-uppercase mb-1"><?php echo htmlspecialchars($q['status']); ?></div>
                                                <div class="text-muted fs-8">Deneme: <?php echo $q['attempts']; ?></div>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-light-info view-queue-details"
                                                    data-message="<?php echo htmlspecialchars($q['message']); ?>"
                                                    data-error="<?php echo htmlspecialchars($q['last_error'] ?? ''); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#kt_modal_queue_details">
                                                    <i class="ki-duotone ki-eye fs-3"></i> Detay
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

<!-- Modal: Kuyruk Detayları -->
<div class="modal fade" id="kt_modal_queue_details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Kuyruk Gönderim Detayı</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body py-10 px-lg-17">
                <h5 class="mb-3 text-primary">Gönderilen Mesaj</h5>
                <div class="bg-light p-4 rounded mb-6">
                    <div id="modal_queue_message" class="fs-6 text-gray-800" style="white-space: pre-wrap;"></div>
                </div>
                
                <div id="modal_queue_error_container" style="display:none;">
                    <h5 class="mb-3 text-danger">Son Alınan Hata (Last Error)</h5>
                    <div class="bg-light-danger p-4 rounded">
                        <pre id="modal_queue_error" class="mb-0 text-danger" style="white-space: pre-wrap;"></pre>
                    </div>
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
document.querySelectorAll('.view-queue-details').forEach(btn => {
    btn.addEventListener('click', function() {
        const message = this.getAttribute('data-message');
        const error = this.getAttribute('data-error');
        
        document.getElementById('modal_queue_message').innerHTML = message; // innerHTML to allow HTML formatted emails to render or simply be read
        
        const errorContainer = document.getElementById('modal_queue_error_container');
        const errorEl = document.getElementById('modal_queue_error');
        
        if (error && error.trim() !== '') {
            errorEl.innerText = error;
            errorContainer.style.display = 'block';
        } else {
            errorContainer.style.display = 'none';
        }
    });
});
</script>
