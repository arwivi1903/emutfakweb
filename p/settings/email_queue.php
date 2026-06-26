<?php
/**
 * Prolyn Admin Panel - Email Queue Management
 * prolynweb/p/settings/email_queue.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Yeniden Kuyruğa Al veya İptal Et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'retry') {
        $id = (int)$_POST['queue_id'];
        // Status pending yap, attempts sıfırla veya arttır
        $db->Update("UPDATE eposta_kuyrugu SET status = 'pending', attempts = 0, last_error = NULL, updated_at = NOW() WHERE queue_id = ?", [$id]);
        echo "<script>window.location.href='settings/email_queue.php?success=retry';</script>";
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $id = (int)$_POST['queue_id'];
        $db->Update("UPDATE eposta_kuyrugu SET status = 'cancelled', updated_at = NOW() WHERE queue_id = ?", [$id]);
        echo "<script>window.location.href='settings/email_queue.php?success=cancel';</script>";
        exit;
    }
}

// Kuyruğu Çek
$query = "SELECT * FROM eposta_kuyrugu ORDER BY created_at DESC LIMIT 500";
$queue = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        E-posta Kuyruğu
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">E-posta Kuyruğu</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>Kuyruk işlemi başarıyla uygulandı.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-queue-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="E-posta Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_queue_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Alıcı Bilgileri</th>
                                        <th>Konu (Subject)</th>
                                        <th>Öncelik</th>
                                        <th>Deneme</th>
                                        <th>Durum</th>
                                        <th>Tarihler</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($queue)): ?>
                                        <?php foreach ($queue as $q): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($q['to_name'] ?? '-'); ?></div>
                                                <div class="text-muted fs-7"><?php echo htmlspecialchars($q['to_email']); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold mb-1 d-block"><?php echo htmlspecialchars($q['subject']); ?></span>
                                                <span class="badge badge-light-secondary fs-8">Şablon: <?php echo htmlspecialchars($q['template_key'] ?? 'Özel (Yok)'); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $pri_class = 'badge-light-info';
                                                    if ($q['priority'] == 'high') $pri_class = 'badge-light-warning';
                                                    elseif ($q['priority'] == 'urgent') $pri_class = 'badge-light-danger';
                                                ?>
                                                <span class="badge <?php echo $pri_class; ?> text-uppercase"><?php echo htmlspecialchars($q['priority']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light"><?php echo $q['attempts']; ?> / <?php echo $q['max_attempts']; ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-primary';
                                                    if ($q['status'] == 'pending') $status_class = 'badge-light-warning';
                                                    elseif ($q['status'] == 'sent') $status_class = 'badge-light-success';
                                                    elseif ($q['status'] == 'failed') $status_class = 'badge-light-danger';
                                                    elseif ($q['status'] == 'cancelled') $status_class = 'badge-light-dark';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($q['status']); ?></span>
                                                <?php if($q['status'] == 'failed' && !empty($q['last_error'])): ?>
                                                    <i class="ki-duotone ki-information-5 text-danger ms-1" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($q['last_error']); ?>"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fs-8 text-muted">Kayıt: <?php echo date('d.m.Y H:i', strtotime($q['created_at'])); ?></span>
                                                    <?php if($q['sent_at']): ?>
                                                    <span class="fs-8 text-success">Gön.: <?php echo date('d.m.Y H:i', strtotime($q['sent_at'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-icon btn-light-info btn-sm me-1" title="İçeriği Gör" 
                                                    onclick="viewContent(<?php echo htmlspecialchars(json_encode($q)); ?>)">
                                                    <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </button>
                                                <?php if($q['status'] === 'failed' || $q['status'] === 'cancelled'): ?>
                                                <form action="settings/email_queue.php" method="POST" class="d-inline" onsubmit="return confirm('Bu e-postayı tekrar kuyruğa almak istediğinize emin misiniz?');">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="retry">
                                                    <input type="hidden" name="queue_id" value="<?php echo $q['queue_id']; ?>">
                                                    <button type="submit" class="btn btn-icon btn-light-warning btn-sm" title="Tekrar Dene">
                                                        <i class="ki-duotone ki-arrows-circle fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if($q['status'] === 'pending'): ?>
                                                <form action="settings/email_queue.php" method="POST" class="d-inline" onsubmit="return confirm('Bu e-postanın gönderimini iptal etmek istediğinize emin misiniz?');">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="queue_id" value="<?php echo $q['queue_id']; ?>">
                                                    <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="İptal Et">
                                                        <i class="ki-duotone ki-cross-circle fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-5">Kuyrukta e-posta bulunamadı.</td></tr>
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

<!-- Modal: E-posta İçeriğini Göster -->
<div class="modal fade" id="kt_modal_view_content" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">E-posta İçerik Detayı</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                
                <div class="d-flex flex-column mb-5">
                    <span class="text-muted fs-7 fw-bold mb-1">Alıcı (To):</span>
                    <span class="text-gray-800 fs-6" id="view_to"></span>
                </div>
                
                <div class="d-flex flex-column mb-5">
                    <span class="text-muted fs-7 fw-bold mb-1">Konu (Subject):</span>
                    <span class="text-gray-800 fs-6" id="view_subject"></span>
                </div>

                <div class="d-flex flex-column mb-8">
                    <span class="text-muted fs-7 fw-bold mb-1">HTML İçerik (Raw):</span>
                    <div class="p-5 bg-light rounded border border-gray-300 font-monospace" style="max-height: 400px; overflow-y: auto;">
                        <pre id="view_body_html" class="mb-0 text-gray-800" style="white-space: pre-wrap; word-break: break-all;"></pre>
                    </div>
                </div>
                
                <div class="d-flex flex-column" id="view_error_container" style="display: none !important;">
                    <span class="text-danger fs-7 fw-bold mb-1">Son Hata (Last Error):</span>
                    <div class="p-4 bg-light-danger text-danger rounded border border-danger border-dashed">
                        <span id="view_last_error" style="white-space: pre-wrap;"></span>
                    </div>
                </div>

            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewContent(data) {
    document.getElementById('view_to').innerText = (data.to_name ? data.to_name + ' ' : '') + '<' + data.to_email + '>';
    document.getElementById('view_subject').innerText = data.subject;
    document.getElementById('view_body_html').textContent = data.body_html || data.body_text || 'İçerik Yok';
    
    var errorContainer = document.getElementById('view_error_container');
    if (data.last_error && data.status === 'failed') {
        errorContainer.style.setProperty('display', 'flex', 'important');
        document.getElementById('view_last_error').innerText = data.last_error;
    } else {
        errorContainer.style.setProperty('display', 'none', 'important');
    }
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_view_content'));
    modal.show();
}

// Tooltip Initialize
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
</script>

<?php require_once '../config/footer.php'; ?>
