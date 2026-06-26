<?php
/**
 * Prolyn Admin Panel - Activity Logs
 * prolynweb/p/logs/activity.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Aktivite loglarını çek
$query = "
    SELECT a.*, k.company_name 
    FROM aktivite_loglari a 
    LEFT JOIN kurumlar k ON a.customer_id = k.customer_id 
    ORDER BY a.activity_id DESC 
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
                        Aktivite Logları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Aktivite Logları</li>
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
                                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Loglarda Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Tarih</th>
                                        <th>Firma Adı</th>
                                        <th>Kullanıcı ID</th>
                                        <th>İşlem (Action)</th>
                                        <th>Hedef Tip/ID</th>
                                        <th>Açıklama</th>
                                        <th>IP Adresi</th>
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
                                            <td><?php echo htmlspecialchars($log['company_name'] ?? 'Sistem'); ?></td>
                                            <td><?php echo $log['user_id'] ? '#' . $log['user_id'] : '-'; ?></td>
                                            <td><span class="badge badge-light-primary text-uppercase"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                            <td>
                                                <span class="text-muted"><?php echo htmlspecialchars($log['entity_type']); ?></span>
                                                <?php if($log['entity_id']) echo ' <span class="fw-bold">#' . $log['entity_id'] . '</span>'; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['description'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-5">Kayıt bulunamadı.</td></tr>
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
