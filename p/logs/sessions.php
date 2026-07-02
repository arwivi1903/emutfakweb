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
    $session_id = trim($_POST['session_id']);
    $db->Delete("DELETE FROM oturumlar WHERE session_id = ?", [$session_id]);
    
    echo "<script>window.location.href='logs/sessions.php?success=killed';</script>";
    exit;
}

// Oturumları Çek (Admins ve Customers Join)
$query = "
    SELECT * 
    FROM oturumlar 
    WHERE expires_at > NOW()
    ORDER BY last_activity DESC
";
$sessions = $db->getRowsAssoc($query);
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
                        text: "Oturum başarıyla sonlandırıldı. (İlgili kullanıcının çıkışı yapıldı)",
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

                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-session-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Oturum veya İsim Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_sessions_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-250px">Kullanıcı (İsim / E-posta / Firma)</th>
                                        <th>Giriş Türü</th>
                                        <th>IP & Cihaz</th>
                                        <th>Durum & Son Hareket</th>
                                        <th>Bitiş (Expires)</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($sessions)): ?>
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
                                            <td>
                                                <?php 
                                                    $last_activity_time = strtotime($sess['last_activity']);
                                                    $is_active = (time() - $last_activity_time) <= 900; // 15 dakika
                                                ?>
                                                <div class="d-flex flex-column">
                                                    <span><?php echo date('d.m.Y H:i:s', $last_activity_time); ?></span>
                                                    <div class="mt-1">
                                                        <?php if ($is_active): ?>
                                                            <span class="badge badge-light-success">Çevrimiçi</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-light-secondary">Çevrimdışı</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo $sess['expires_at'] != '0000-00-00 00:00:00' ? date('d.m.Y H:i:s', strtotime($sess['expires_at'])) : 'Süresiz'; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($is_active): ?>
                                                    <form action="logs/sessions.php" method="POST" style="display:inline-block;">
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="action" value="kill_session">
                                                        <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($sess['session_id']); ?>">
                                                        <button type="button" class="btn btn-sm btn-light-danger" onclick="confirmKillSession(this);">
                                                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Sonlandır
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted fs-8 text-uppercase">İşlem Yok</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Sistemde şu an aktif oturum bulunmuyor.</td></tr>
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
function confirmKillSession(btn) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: 'Bu oturumu zorla sonlandırmak istediğinize emin misiniz? Kullanıcı sistemden atılacaktır.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sonlandır',
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
