<?php
/**
 * Prolyn Admin Panel - Login Attempts Management
 * prolynweb/p/logs/login_attempts.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Logları Çek
$query = "
    SELECT * 
    FROM giris_denemeleri
    ORDER BY created_at DESC
    LIMIT 1000
";
$attempts = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Giriş Denemeleri
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log ve Sistem Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Giriş Denemeleri</li>
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
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="E-posta veya IP Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Kullanıcı (E-posta)</th>
                                        <th>Tür</th>
                                        <th>IP Adresi</th>
                                        <th>Durum</th>
                                        <th>Açıklama / Hata</th>
                                        <th>Tarih</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($attempts)): ?>
                                        <?php foreach ($attempts as $log): ?>
                                        <tr>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($log['email'] ?? 'Bilinmiyor'); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $type_class = 'badge-light-primary';
                                                    if ($log['attempt_type'] == 'admin') $type_class = 'badge-light-danger';
                                                ?>
                                                <span class="badge <?php echo $type_class; ?> text-uppercase"><?php echo htmlspecialchars($log['attempt_type']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                                                <div class="text-muted fs-8" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                                    <?php echo htmlspecialchars($log['user_agent']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-primary';
                                                    if ($log['status'] == 'success') $status_class = 'badge-light-success';
                                                    elseif ($log['status'] == 'failed') $status_class = 'badge-light-warning';
                                                    elseif ($log['status'] == 'blocked') $status_class = 'badge-light-danger';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($log['status']); ?></span>
                                            </td>
                                            <td>
                                                <?php if(!empty($log['failure_reason'])): ?>
                                                    <span class="text-danger"><?php echo htmlspecialchars($log['failure_reason']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Giriş denemesi kaydı bulunamadı.</td></tr>
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
