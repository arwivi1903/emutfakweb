<?php
/**
 * Prolyn Admin Panel - System Errors Log
 * prolynweb/p/logs/system_errors.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_error') {
    $error_id = (int)$_POST['error_id'];
    $db->Update("UPDATE sistem_hatalari SET resolved_at = NOW() WHERE id = ?", [$error_id]);
    echo "<script>window.location.href='logs/system_errors.php?success=resolved';</script>";
    exit;
}

$query = "SELECT * FROM sistem_hatalari ORDER BY created_at DESC";
$errors = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Sistem Hataları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log ve Sistem Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Sistem Hataları</li>
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
                        <span>Hata durumu "Çözüldü" olarak işaretlendi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Hata Kaynağı</th>
                                        <th>Mesaj</th>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($errors)): ?>
                                        <?php foreach ($errors as $err): ?>
                                        <tr>
                                            <td><span class="badge badge-light-primary fw-bold"><?php echo htmlspecialchars($err['source']); ?></span></td>
                                            <td>
                                                <div class="text-gray-800 fw-bold text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($err['message']); ?>">
                                                    <?php echo htmlspecialchars($err['message']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i:s', strtotime($err['created_at'])); ?></td>
                                            <td>
                                                <?php if($err['resolved_at']): ?>
                                                    <span class="badge badge-light-success">Çözüldü (<?php echo date('d.m.Y H:i', strtotime($err['resolved_at'])); ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-danger">Aktif (Çözülmedi)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-icon btn-light-info me-1" title="Detay / Stack Trace" onclick='viewError(<?php echo json_encode($err, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </button>
                                                <?php if(!$err['resolved_at']): ?>
                                                <form action="logs/system_errors.php" method="POST" style="display:inline-block;">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="resolve_error">
                                                    <input type="hidden" name="error_id" value="<?php echo $err['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-icon btn-light-success" title="Çözüldü Olarak İşaretle">
                                                        <i class="ki-duotone ki-check fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-5">Kayıtlı sistem hatası bulunamadı.</td></tr>
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

<!-- Modal: Hata Detayı -->
<div class="modal fade" id="kt_modal_error_detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Hata Detayları</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="mb-5">
                    <span class="fw-bold text-gray-800 d-block mb-1">Kaynak:</span>
                    <span class="badge badge-light-primary fs-6" id="view_source"></span>
                </div>
                <div class="mb-5">
                    <span class="fw-bold text-gray-800 d-block mb-1">Hata Mesajı:</span>
                    <div class="bg-light-danger p-3 rounded text-danger" id="view_message"></div>
                </div>
                <div class="mb-5">
                    <span class="fw-bold text-gray-800 d-block mb-1">Stack Trace:</span>
                    <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; font-size: 11px;" id="view_stack_trace"></pre>
                </div>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewError(err) {
    document.getElementById('view_source').innerText = err.source;
    document.getElementById('view_message').innerText = err.message;
    document.getElementById('view_stack_trace').innerText = err.stack_trace || 'Stack trace bulunmuyor.';
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_error_detail'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
