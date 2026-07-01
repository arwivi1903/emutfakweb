<?php
/**
 * Prolyn Admin Panel - Maintenance Logs
 * prolynweb/p/logs/maintenance.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Durum Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['log_id'])) {
    $action = $_POST['action'];
    $log_id = (int)$_POST['log_id'];
    
    if ($action === 'complete') {
        $db->Update("UPDATE bakim_loglari SET status = 'completed', is_active = 0 WHERE id = ?", [$log_id]);
    } elseif ($action === 'cancel') {
        $db->Update("UPDATE bakim_loglari SET status = 'cancelled', is_active = 0 WHERE id = ?", [$log_id]);
    }
    
    echo "<script>window.location.href='logs/maintenance.php';</script>";
    exit;
}

// Bakım loglarını çek
$query = "
    SELECT * 
    FROM bakim_loglari 
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
                        Bakım Logları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Bakım Logları</li>
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
                                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Bakım Kaydı Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>ID</th>
                                        <th>Başlık / Açıklama</th>
                                        <th>Öncelik</th>
                                        <th>Başlangıç/Bitiş</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo $log['id']; ?></td>
                                            <td>
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($log['title']); ?></div>
                                                <div class="text-muted fs-7"><?php echo htmlspecialchars($log['description'] ?? '-'); ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $pri_class = 'badge-light';
                                                    if ($log['priority'] == 'critical') $pri_class = 'badge-light-danger';
                                                    elseif ($log['priority'] == 'high') $pri_class = 'badge-light-warning';
                                                    elseif ($log['priority'] == 'medium') $pri_class = 'badge-light-primary';
                                                ?>
                                                <span class="badge <?php echo $pri_class; ?> text-uppercase"><?php echo htmlspecialchars($log['priority']); ?></span>
                                            </td>
                                            <td>
                                                <div><i class="ki-duotone ki-calendar text-success fs-7"></i> <?php echo date('d.m.Y H:i', strtotime($log['start_time'])); ?></div>
                                                <div><i class="ki-duotone ki-calendar text-danger fs-7"></i> <?php echo $log['end_time'] != '0000-00-00 00:00:00' ? date('d.m.Y H:i', strtotime($log['end_time'])) : '-'; ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-info';
                                                    if ($log['status'] == 'completed') $status_class = 'badge-light-success';
                                                    elseif ($log['status'] == 'cancelled') $status_class = 'badge-light-danger';
                                                    elseif ($log['status'] == 'scheduled') $status_class = 'badge-light-warning';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($log['status']); ?></span>
                                                <?php if($log['is_active']): ?>
                                                    <span class="badge badge-success ms-1">Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <form action="logs/maintenance.php" method="POST" style="display:inline-block;">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="log_id" value="<?php echo $log['id']; ?>">
                                                    <?php if ($log['status'] === 'scheduled' || $log['status'] === 'in_progress'): ?>
                                                        <input type="hidden" name="action" value="complete">
                                                        <button type="button" class="btn btn-sm btn-light-success" onclick="Swal.fire({title: 'Emin misiniz?', text: 'Tamamlandı olarak işaretlemek istediğinize emin misiniz?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { let f = this.closest('form'); f.querySelector('input[name=action]').value = 'complete'; f.submit(); } });">Tamamla</button>
                                                        
                                                        <button type="button" class="btn btn-sm btn-light-danger" onclick="Swal.fire({title: 'Emin misiniz?', text: 'İptal etmek istediğinize emin misiniz?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { let f = this.closest('form'); f.querySelector('input[name=action]').value = 'cancel'; f.submit(); } });">İptal Et</button>
                                                    <?php else: ?>
                                                        <span class="text-muted">İşlem Yok</span>
                                                    <?php endif; ?>
                                                </form>
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

<?php require_once '../config/footer.php'; ?>
