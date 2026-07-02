<?php
/**
 * Prolyn Admin Panel - User Activity Logs
 * Prolyn/p/logs/user_activity.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Kullanıcı aktivite loglarını çek
$query = "
    SELECT * 
    FROM kullanici_aktivite_loglari 
    ORDER BY id DESC 
    LIMIT 1000
";
$logs = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Kullanıcı Aktivite Logları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Kullanıcı Aktiviteleri</li>
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
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Aktivite Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Tarih</th>
                                        <th>Kullanıcı (Tip/ID)</th>
                                        <th>Aktivite</th>
                                        <th>IP Adresi</th>
                                        <th class="text-end">Metadata Detayı</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800"><?php echo date('d.m.Y', strtotime($log['created_at'])); ?></div>
                                                <div class="text-muted fs-7"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light"><?php echo htmlspecialchars($log['user_type']); ?></span>
                                                <span class="fw-bold">#<?php echo $log['user_id']; ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800"><?php echo htmlspecialchars($log['activity']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></td>
                                            <td class="text-end">
                                                <?php if(!empty($log['metadata'])): ?>
                                                    <button type="button" class="btn btn-sm btn-light-info view-metadata"
                                                        data-meta="<?php echo htmlspecialchars($log['metadata'] ?? ''); ?>"
                                                        data-bs-toggle="modal" data-bs-target="#kt_modal_metadata">
                                                        <i class="ki-duotone ki-eye fs-3"></i> Metadata
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted py-5">Kayıt bulunamadı.</td></tr>
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

<!-- Modal: Metadata Detayları -->
<div class="modal fade" id="kt_modal_metadata" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Aktivite Metadata Detayları</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body py-10 px-lg-17">
                <div id="metadata_body" class="fs-6 text-gray-700 bg-light p-4 rounded">
                    <!-- JS ile doldurulacak -->
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
document.querySelectorAll('.view-metadata').forEach(btn => {
    btn.addEventListener('click', function() {
        const metaStr = this.getAttribute('data-meta');
        let html = '';
        
        try {
            if (metaStr && metaStr.trim() !== '') {
                const metaObj = JSON.parse(metaStr);
                html = '<pre class="mb-0" style="white-space: pre-wrap;">' + JSON.stringify(metaObj, null, 2) + '</pre>';
            }
        } catch(e) {
            html = '<pre class="mb-0" style="white-space: pre-wrap;">' + metaStr + '</pre>';
        }

        document.getElementById('metadata_body').innerHTML = html;
    });
});
</script>
