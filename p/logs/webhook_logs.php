<?php
/**
 * Prolyn Admin Panel - Webhook Logs Management
 * prolynweb/p/logs/webhook_logs.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Form Submit (Kayıt Silme - opsiyonel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_log') {
        $log_id = (int)$_POST['log_id'];
        $db->Delete("DELETE FROM webhook_loglari WHERE log_id=?", [$log_id]);
        echo "<script>window.location.href='logs/webhook_logs.php?success=deleted';</script>";
        exit;
    }
}

// Logları çek
$logs = $db->getRowsAssoc("SELECT wl.*, w.url as webhook_url, w.customer_id, k.company_name 
                           FROM webhook_loglari wl 
                           LEFT JOIN webhooklar w ON wl.webhook_id = w.webhook_id 
                           LEFT JOIN kurumlar k ON w.customer_id = k.customer_id 
                           ORDER BY wl.created_at DESC LIMIT 200");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Webhook Geçmişi / Logları</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log ve Sistem Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-gray-900">Webhook İstek Geçmişi</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>İşlem başarıyla gerçekleştirildi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" id="log_search" class="form-control form-control-solid w-250px ps-12" placeholder="Olay/Kurum Ara" />
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-10px pe-2">#</th>
                                        <th>Webhook / Kurum</th>
                                        <th>Olay (Event)</th>
                                        <th>Durum</th>
                                        <th>HTTP Kodu</th>
                                        <th>Süre (ms)</th>
                                        <th>Tarih</th>
                                        <th class="text-end">Ayrıntılar</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $item): ?>
                                        <tr>
                                            <td><?php echo $item['log_id']; ?></td>
                                            <td>
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($item['company_name'] ?? 'Genel / Silinmiş'); ?></div>
                                                <div class="text-muted fs-8 text-truncate" style="max-width:200px;" title="<?php echo htmlspecialchars($item['webhook_url']); ?>"><?php echo htmlspecialchars($item['webhook_url'] ?? '-'); ?></div>
                                            </td>
                                            <td><span class="badge badge-light-primary fw-bold"><?php echo htmlspecialchars($item['event_type']); ?></span></td>
                                            <td>
                                                <?php 
                                                $s = $item['status'];
                                                $c = $s == 'success' ? 'success' : ($s == 'pending' ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge badge-light-<?php echo $c; ?> text-uppercase"><?php echo $s; ?></span>
                                            </td>
                                            <td>
                                                <?php if($item['response_code']): ?>
                                                    <span class="badge badge-light-<?php echo ($item['response_code'] >= 200 && $item['response_code'] < 300) ? 'success' : 'danger'; ?>"><?php echo $item['response_code']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $item['duration_ms'] ? $item['duration_ms'] . ' ms' : '-'; ?></td>
                                            <td><?php echo date('d.m.Y H:i:s', strtotime($item['created_at'])); ?></td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-icon btn-light-info btn-sm btn-view-log" 
                                                    data-id="<?php echo $item['log_id']; ?>"
                                                    data-payload="<?php echo htmlspecialchars($item['payload']); ?>"
                                                    data-response="<?php echo htmlspecialchars($item['response_body']); ?>"
                                                    data-error="<?php echo htmlspecialchars($item['error_message']); ?>"
                                                    title="Ayrıntıları Görüntüle">
                                                    <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-10">Kayıt bulunamadı.</td>
                                        </tr>
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

<!-- Modal: Log Ayrıntıları -->
<div class="modal fade" id="kt_modal_view_log" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Webhook İstek Ayrıntıları</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="mb-5">
                    <label class="fw-bold fs-6 mb-2">Payload (Gönderilen JSON)</label>
                    <div class="bg-light p-4 rounded text-dark fs-7" style="font-family: monospace; white-space: pre-wrap; word-break: break-all; max-height: 250px; overflow-y: auto;" id="view_payload"></div>
                </div>
                <div class="mb-5">
                    <label class="fw-bold fs-6 mb-2">Hedef Sunucu Yanıtı (Response Body)</label>
                    <div class="bg-light p-4 rounded text-dark fs-7" style="font-family: monospace; white-space: pre-wrap; word-break: break-all; max-height: 250px; overflow-y: auto;" id="view_response"></div>
                </div>
                <div class="mb-5" id="view_error_container">
                    <label class="fw-bold fs-6 mb-2 text-danger">Bağlantı Hatası / Sistem Hatası</label>
                    <div class="bg-light-danger p-4 rounded text-danger fs-7" style="font-family: monospace; white-space: pre-wrap; word-break: break-all;" id="view_error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Arama
    const searchInput = document.getElementById('log_search');
    const tableRows = document.querySelectorAll('#kt_logs_table tbody tr');

    searchInput.addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // View Modal
    const viewButtons = document.querySelectorAll('.btn-view-log');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            let payload = this.getAttribute('data-payload');
            let response = this.getAttribute('data-response');
            let error = this.getAttribute('data-error');

            try { payload = JSON.stringify(JSON.parse(payload), null, 4); } catch(e) {}
            try { response = JSON.stringify(JSON.parse(response), null, 4); } catch(e) {}

            document.getElementById('view_payload').textContent = payload || 'Boş / Yok';
            document.getElementById('view_response').textContent = response || 'Boş / Yok';
            
            if (error && error !== 'null' && error !== '') {
                document.getElementById('view_error_container').classList.remove('d-none');
                document.getElementById('view_error').textContent = error;
            } else {
                document.getElementById('view_error_container').classList.add('d-none');
            }

            const modal = new bootstrap.Modal(document.getElementById('kt_modal_view_log'));
            modal.show();
        });
    });
});
</script>

<?php require_once '../config/footer.php'; ?>
