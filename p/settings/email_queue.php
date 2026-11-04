<?php
/**
 * Prolyn Admin Panel - Email Queue Management
 * Prolyn/p/settings/email_queue.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Yeniden Kuyruğa Al veya İptal Et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'retry') {
        $id = (int)$_POST['queue_id'];
        $db->Update("UPDATE mail_kuyrugu SET status = 'pending', attempts = 0, last_error = NULL, processed_at = NULL WHERE queue_id = ?", [$id]);
        echo "<script>window.location.href='settings/email_queue.php?success=retry';</script>";
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $id = (int)$_POST['queue_id'];
        $db->Update("UPDATE mail_kuyrugu SET status = 'cancelled', processed_at = NOW() WHERE queue_id = ?", [$id]);
        echo "<script>window.location.href='settings/email_queue.php?success=cancel';</script>";
        exit;
    }
}

// Reset taleplerinde mail kuyrugu durumu tamamlanmis olarak tutulur.
$db->Update(
    "UPDATE mail_kuyrugu
     SET status = 'completed', processed_at = IFNULL(processed_at, NOW())
     WHERE status IN ('pending', 'queued') AND source = 'password_reset_request'"
);

// Kuyruğu Çek
$query = "SELECT * FROM mail_kuyrugu ORDER BY created_at DESC LIMIT 500";
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
                        <span><?= ($_GET['success'] ?? '') === 'resend' ? 'E-posta yeni alıcıya gönderilmek üzere kuyruğa eklendi; bir dakika içinde gönderilir.' : 'Kuyruk işlemi başarıyla uygulandı.' ?></span>
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
                                        <th>Alıcı</th>
                                        <th>Konu (Subject)</th>
                                        <th>Kaynak</th>
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
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($q['recipient'] ?? '-'); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold mb-1 d-block"><?php echo htmlspecialchars($q['subject'] ?? '-'); ?></span>
                                                <span class="badge badge-light-secondary fs-8">Kanal: <?php echo htmlspecialchars($q['channel'] ?? 'email'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-info text-uppercase"><?php echo htmlspecialchars($q['source'] ?? 'unknown'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light"><?php echo (int)($q['attempts'] ?? 0); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-primary';
                                                    if ($q['status'] == 'pending') $status_class = 'badge-light-warning';
                                                    elseif ($q['status'] == 'sent' || $q['status'] == 'completed') $status_class = 'badge-light-success';
                                                    elseif ($q['status'] == 'failed') $status_class = 'badge-light-danger';
                                                    elseif ($q['status'] == 'cancelled') $status_class = 'badge-light-dark';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($q['status']); ?></span>
                                                <?php if($q['status'] === 'sent' && !empty($q['last_error'])): ?>
                                                    <!-- Gönderildi ama Gönderilenler kopyası alınamadı -->
                                                    <i class="ki-duotone ki-information-5 text-warning ms-1" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($q['last_error']); ?>"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                <?php endif; ?>
                                                <?php if($q['status'] == 'failed' && !empty($q['last_error'])): ?>
                                                    <i class="ki-duotone ki-information-5 text-danger ms-1" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($q['last_error']); ?>"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fs-8 text-muted">Kayıt: <?php echo date('d.m.Y H:i', strtotime($q['created_at'])); ?></span>
                                                    <?php if(!empty($q['processed_at'])): ?>
                                                    <span class="fs-8 text-success">İşl.: <?php echo date('d.m.Y H:i', strtotime($q['processed_at'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <!-- Modal yerine ayrı sayfa: içerik + alıcıyı düzeltip tekrar gönder -->
                                                <a href="settings/email_resend.php?id=<?php echo (int)($q['queue_id'] ?? 0); ?>" class="btn btn-icon btn-light-info btn-sm me-1" title="İçeriği gör / düzeltip tekrar gönder" aria-label="İçeriği gör">
                                                    <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </a>
                                                <?php if (in_array($q['status'], ['sent', 'failed', 'cancelled'], true)): ?>
                                                <a href="settings/email_resend.php?id=<?php echo (int)($q['queue_id'] ?? 0); ?>" class="btn btn-icon btn-light-primary btn-sm me-1" title="Alıcıyı düzeltip tekrar gönder" aria-label="Alıcıyı düzeltip tekrar gönder" data-bs-toggle="tooltip">
                                                    <i class="ki-duotone ki-arrows-loop fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </a>
                                                <?php endif; ?>
                                                <?php if($q['status'] === 'failed' || $q['status'] === 'cancelled'): ?>
                                                <form action="settings/email_queue.php" method="POST" class="d-inline" onsubmit="event.preventDefault(); Swal.fire({title: 'Emin misiniz?', text: 'Bu e-postayı tekrar kuyruğa almak istediğinize emin misiniz?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { this.submit(); } });">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="retry">
                                                    <input type="hidden" name="queue_id" value="<?php echo (int)($q['queue_id'] ?? 0); ?>">
                                                    <button type="submit" class="btn btn-icon btn-light-warning btn-sm" title="Tekrar Dene">
                                                        <i class="ki-duotone ki-arrows-circle fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if($q['status'] === 'pending'): ?>
                                                <form action="settings/email_queue.php" method="POST" class="d-inline" onsubmit="event.preventDefault(); Swal.fire({title: 'Emin misiniz?', text: 'Bu e-postanın gönderimini iptal etmek istediğinize emin misiniz?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { this.submit(); } });">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="queue_id" value="<?php echo (int)($q['queue_id'] ?? 0); ?>">
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

<script>
// Tooltip Initialize
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
</script>

<?php require_once '../config/footer.php'; ?>
