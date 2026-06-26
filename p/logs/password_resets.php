<?php
/**
 * Prolyn Admin Panel - Password Resets Log
 * prolynweb/p/logs/password_resets.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

$query = "SELECT * FROM sifre_sifirlama ORDER BY created_at DESC";
$resets = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Şifre Sıfırlama İstekleri
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log ve Sistem Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Şifre Sıfırlama</li>
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
                                <input type="text" data-kt-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>E-posta</th>
                                        <th>Kullanıcı Türü</th>
                                        <th>İstek Tarihi</th>
                                        <th>Son Kullanım (Expires)</th>
                                        <th>Durum (Kullanıldı mı?)</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($resets)): ?>
                                        <?php foreach ($resets as $row): ?>
                                        <tr>
                                            <td class="text-gray-800 fw-bold"><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <span class="badge badge-light-<?php echo $row['user_type']=='admin' ? 'danger' : 'primary'; ?> text-uppercase">
                                                    <?php echo htmlspecialchars($row['user_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($row['expires_at'])); ?></td>
                                            <td>
                                                <?php if($row['used']): ?>
                                                    <span class="badge badge-light-success">Kullanıldı (<?php echo date('d.m.Y H:i', strtotime($row['used_at'])); ?>)</span>
                                                <?php else: ?>
                                                    <?php if(strtotime($row['expires_at']) < time()): ?>
                                                        <span class="badge badge-light-danger">Süresi Doldu</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light-warning">Bekliyor</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-5">Kayıt bulunamadı.</td></tr>
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
