<?php
require_once __DIR__ . '/../classes/database.class.php';
require_once __DIR__ . '/../config/header.php';
Auth::requireModule('manage_settings');
require_once __DIR__ . '/../config/sidebar.php';

$db = new Database();

// Yedek Logları Çek
$logs = $db->getRows("
    SELECT l.*, a.full_name, a.username 
    FROM yonetici_loglari_yedek l 
    LEFT JOIN yoneticiler a ON l.admin_id = a.admin_id 
    ORDER BY l.created_at DESC 
    LIMIT 1000
");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Log Arşivi</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-gray-900">Log Arşivi</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Arşivlenmiş Denetim Logları</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Sistemden otomatik veya manuel arşivlenmiş geçmiş kayıtlar. (Son 1000 kayıt)</span>
                            </h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-150px">Tarih</th>
                                        <th class="min-w-150px">Yönetici</th>
                                        <th class="min-w-100px">İşlem Tipi</th>
                                        <th class="min-w-250px">Açıklama</th>
                                        <th class="min-w-100px">IP Adresi</th>
                                        <th class="min-w-100px text-end">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i:s', strtotime($log->created_at)) ?></td>
                                        <td>
                                            <?php if($log->full_name): ?>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold"><?= htmlspecialchars($log->full_name) ?></span>
                                                    <span class="text-muted fs-8"><?= htmlspecialchars($log->username) ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Sistem / Silinmiş (ID: <?= $log->admin_id ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log->log_type == 1): ?>
                                                <span class="badge badge-light-primary">Create</span>
                                            <?php elseif ($log->log_type == 2): ?>
                                                <span class="badge badge-light-info">Update</span>
                                            <?php elseif ($log->log_type == 3): ?>
                                                <span class="badge badge-light-danger">Delete</span>
                                            <?php else: ?>
                                                <span class="badge badge-light">Giriş/Diğer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($log->log_description) ?></td>
                                        <td><?= htmlspecialchars($log->ip_address) ?></td>
                                        <td class="text-end">
                                            <?php if ($log->log_status == 'success'): ?>
                                                <div class="badge badge-success">Başarılı</div>
                                            <?php elseif ($log->log_status == 'error' || $log->log_status == 'failed'): ?>
                                                <div class="badge badge-danger">Hata</div>
                                            <?php elseif ($log->log_status == 'warning'): ?>
                                                <div class="badge badge-warning">Uyarı</div>
                                            <?php else: ?>
                                                <div class="badge badge-info">Bilgi</div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($logs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-10">Arşivde log kaydı bulunamadı.</td>
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

<?php require_once __DIR__ . '/../config/footer.php'; ?>
