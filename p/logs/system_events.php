<?php
/**
 * Prolyn Admin Panel - System Events Log
 * Prolyn/p/logs/system_events.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

$query = "SELECT * FROM sistem_olaylari ORDER BY created_at DESC";
$events = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Sistem Olayları (Event Queue)
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log ve Sistem Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Sistem Olayları</li>
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
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Olay Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Olay Adı (Event)</th>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                        <th>İşlenme Tarihi</th>
                                        <th class="text-end">Payload (Veri)</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($events)): ?>
                                        <?php foreach ($events as $ev): ?>
                                        <tr>
                                            <td><span class="badge badge-light-primary fw-bold text-uppercase"><?php echo htmlspecialchars($ev['event_name']); ?></span></td>
                                            <td><?php echo date('d.m.Y H:i:s', strtotime($ev['created_at'])); ?></td>
                                            <td>
                                                <?php 
                                                $s = $ev['status']; 
                                                $class = $s == 'pending' ? 'warning' : ($s == 'processing' ? 'primary' : ($s == 'failed' ? 'danger' : 'success'));
                                                ?>
                                                <span class="badge badge-light-<?php echo $class; ?> text-uppercase"><?php echo $s; ?></span>
                                            </td>
                                            <td>
                                                <?php echo $ev['processed_at'] ? date('d.m.Y H:i:s', strtotime($ev['processed_at'])) : '-'; ?>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light-info" onclick='viewPayload(<?php echo json_encode($ev['payload'], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Göster</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-5">Kayıtlı sistem olayı bulunamadı.</td></tr>
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

<!-- Modal: Payload Detayı -->
<div class="modal fade" id="kt_modal_payload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Olay Verisi (Payload)</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; font-size: 12px;" id="view_payload_content"></pre>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewPayload(payload) {
    try {
        var obj = JSON.parse(payload);
        document.getElementById('view_payload_content').innerText = JSON.stringify(obj, null, 2);
    } catch(e) {
        document.getElementById('view_payload_content').innerText = payload;
    }
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_payload'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
