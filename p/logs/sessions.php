<?php
/**
 * Prolyn Admin Panel - Active Sessions Management
 * Prolyn/p/logs/sessions.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Oturum Sonlandırma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'kill_session') {
    // CSRF: sonlandırma işlemi yalnızca bu sayfadaki formdan gelen geçerli token ile yapılır
    if (!validateCSRFToken((string)($_POST['csrf_token'] ?? ''))) {
        echo "<script>window.location.replace(" . json_encode($__panelBase . 'logs/sessions.php?error=csrf') . ");</script>";
        exit;
    }
    $session_id = trim((string)($_POST['session_id'] ?? ''));
    // PHP oturum kimliği biçimi dışındaki değerler reddedilir (sunucuya güvenme)
    if (!preg_match('/^[A-Za-z0-9,-]{16,128}$/', $session_id)) {
        echo "<script>window.location.replace(" . json_encode($__panelBase . 'logs/sessions.php?error=invalid') . ");</script>";
        exit;
    }

    $kendi = hash_equals(session_id(), $session_id); // yönetici kendi oturumunu mu sonlandırıyor?
    $hedef = $db->getRow("SELECT user_email, user_type FROM oturumlar WHERE session_id = ?", [$session_id]);
    $db->Delete("DELETE FROM oturumlar WHERE session_id = ?", [$session_id]);

    // Denetim kaydı (kim, hangi oturumu sonlandırdı)
    try {
        xlogSimple('OTURUM_SONLANDIRILDI', 'Sonlandıran: ' . ($_SESSION['email'] ?? '-') . ' | Hedef: ' . ($hedef->user_email ?? ($hedef['user_email'] ?? '-')) . ($kendi ? ' (kendi oturumu)' : ''));
    } catch (Throwable $e) { /* log yazılamazsa işlem yine de tamamlanır */ }

    // Kendi oturumunu sonlandıran yönetici hemen çıkış sayfasına gider (oturum çerezi ve session sunucuda temizlenir).
    // Başka bir oturum ise satırı silinmiştir; o tarayıcı bir sonraki sayfa isteğinde header.php kontrolüyle çıkarılır.
    $hedefUrl = $kendi ? $__panelBase . 'logout.php' : $__panelBase . 'logs/sessions.php?success=killed';
    echo "<script>window.location.replace(" . json_encode($hedefUrl) . ");</script>";
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($hedefUrl, ENT_QUOTES, 'UTF-8') . '"></noscript>';
    exit;
}

// Oturumları Çek (Admins ve Customers Join)
$query = "
    SELECT * 
    FROM oturumlar 
    WHERE expires_at > NOW()
      AND last_activity > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ORDER BY last_activity DESC
";
$sessions = $db->getRowsAssoc($query);
$mevcutSessionId = session_id();
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Aktif Oturumlar
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log ve Sistem Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Aktif Oturumlar</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <?php if(isset($_GET['success']) && $_GET['success'] === 'killed'): ?>
                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        text: "Oturum sonlandırıldı. Kullanıcı, tarayıcıda bir sonraki sayfa isteğinde otomatik olarak çıkış yapar.",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Tamam",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                });
                </script>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger mb-5">Oturum sonlandırılamadı (<?= $_GET['error'] === 'csrf' ? 'güvenlik doğrulaması başarısız, sayfayı yenileyip tekrar deneyin' : 'geçersiz oturum kimliği' ?>).</div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-session-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Kullanıcı, e-posta veya IP ara..." <?= empty($sessions) ? 'disabled' : '' ?> />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <?php if (empty($sessions)): ?>
                            <div class="text-center text-muted py-10">Sistemde şu an aktif oturum bulunmuyor.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_sessions_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-250px">Kullanıcı (İsim / E-posta / Firma)</th>
                                        <th>Giriş Türü</th>
                                        <th>IP & Cihaz</th>
                                        <th>Durum & Son Hareket</th>
                                        <th>Bitiş (Expires)</th>
                                        <th class="text-end no-sort">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                        <?php foreach ($sessions as $sess): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold fs-6"><?php echo htmlspecialchars($sess['user_full_name'] ?? 'Bilinmiyor (ID: '.$sess['user_id'].')'); ?></span>
                                                    <div class="text-muted fs-8 mt-1">
                                                        <?php echo htmlspecialchars($sess['user_email'] ?? '-'); ?>
                                                        <?php if (!empty($sess['firma_adi'])): ?>
                                                            <span class="badge badge-light-info fw-bold ms-1 px-2 py-1"><?php echo htmlspecialchars($sess['firma_adi']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $type_class = 'badge-light-primary';
                                                    if ($sess['user_type'] == 'admin') $type_class = 'badge-light-danger';
                                                ?>
                                                <span class="badge <?php echo $type_class; ?> text-uppercase"><?php echo htmlspecialchars($sess['user_type']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800"><i class="ki-duotone ki-geolocation fs-6 text-primary me-1"><span class="path1"></span><span class="path2"></span></i> <?php echo htmlspecialchars($sess['ip_address']); ?></span>
                                                <div class="text-muted fs-8 mt-1" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($sess['user_agent']); ?>">
                                                    <i class="ki-duotone ki-screen fs-6 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> <?php echo htmlspecialchars($sess['user_agent']); ?>
                                                </div>
                                            </td>
                                            <?php
                                                $last_activity_time = strtotime($sess['last_activity']);
                                                $is_active = true; // liste zaten yalnızca son 15 dakikada hareketi olanları içerir
                                                $bu_oturum = hash_equals($mevcutSessionId, (string)$sess['session_id']);
                                                $expTs = $sess['expires_at'] != '0000-00-00 00:00:00' ? strtotime($sess['expires_at']) : 0;
                                            ?>
                                            <td data-order="<?= (int)$last_activity_time ?>">
                                                <div class="d-flex flex-column">
                                                    <span><?php echo date('d.m.Y H:i:s', $last_activity_time); ?></span>
                                                    <div class="mt-1">
                                                        <?php if ($is_active): ?>
                                                            <span class="badge badge-light-success">Çevrimiçi</span>
                                                            <?php if ($bu_oturum): ?><span class="badge badge-light-primary ms-1">Bu oturum</span><?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge badge-light-secondary">Çevrimdışı</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-order="<?= (int)$expTs ?>">
                                                <?php echo $sess['expires_at'] != '0000-00-00 00:00:00' ? date('d.m.Y H:i:s', strtotime($sess['expires_at'])) : 'Süresiz'; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($is_active): ?>
                                                    <form action="logs/sessions.php" method="POST" style="display:inline-block;">
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="action" value="kill_session">
                                                        <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($sess['session_id']); ?>">
                                                        <button type="button" class="btn btn-sm btn-light-danger" onclick="confirmKillSession(this, <?= $bu_oturum ? 'true' : 'false' ?>);">
                                                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> <?= $bu_oturum ? 'Çıkış yap' : 'Sonlandır' ?>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted fs-8 text-uppercase">İşlem Yok</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function confirmKillSession(btn, kendi) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: kendi ? 'Bu sizin şu an kullandığınız oturum. Sonlandırırsanız çıkış yapılır ve giriş sayfasına yönlendirilirsiniz.' : 'Bu oturumu sonlandırmak istediğinize emin misiniz? Kullanıcı bir sonraki sayfa isteğinde sistemden atılacaktır.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: kendi ? 'Evet, çıkış yap' : 'Evet, Sonlandır',
        cancelButtonText: 'İptal',
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-active-light"
        }
    }).then((result) => {
        if (result.isConfirmed) {
            btn.closest('form').submit();
        }
    });
}
</script>

<?php require_once '../config/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.initDataTable !== 'function' || !document.getElementById('kt_sessions_table')) { return; }
    // Sunucu zaten yalnızca aktif oturumları getirir; DataTable sıralama, sayfalama ve arama ekler.
    window.initDataTable('#kt_sessions_table', {
        dom: "rt<'d-flex flex-stack flex-wrap pt-4'<'text-muted'i>p>",
        order: [[3, 'desc']],
        columnDefs: [{ targets: 'no-sort', orderable: false }]
    });
    var tablo = window.jQuery('#kt_sessions_table').DataTable();
    var ara = document.querySelector('[data-kt-session-table-filter="search"]');
    if (ara) { ara.addEventListener('input', function () { tablo.search(this.value).draw(); }); }
});
</script>
