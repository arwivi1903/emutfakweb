<?php
/**
 * Prolyn Admin Panel - Dashboard
 * prolynweb/p/index.php
 */

require_once 'config/header.php';
require_once 'config/sidebar.php';

// Veritabanı ve istatistikleri çek
$admin_id = $_SESSION['admin_id'] ?? 0;
$adminRow = $db->getRow("SELECT username FROM yoneticiler WHERE admin_id = ?", [$admin_id]);
$username = $adminRow ? $adminRow->username : 'Yönetici';

// İstatistikleri doğru mantıkla (tenants.php ile aynı) hesapla
$allTenants = $db->getRows("SELECT status, trial_end_date, sozlesme_bitis_tarihi FROM kurumlar WHERE del_status = 1 AND deleted_at IS NULL");
$totalTenants = count($allTenants);
$activeTenants = 0;
$passiveTenants = 0;
$trialTenants = 0;

$currentTime = time();

foreach ($allTenants as $t) {
    $trialEndDateStr = $t->trial_end_date;
    $contractDateStr = $t->sozlesme_bitis_tarihi;
    
    $hasTrialDate = !empty($trialEndDateStr) && $trialEndDateStr !== '0000-00-00' && $trialEndDateStr !== '1970-01-01';
    $hasContract = !empty($contractDateStr) && $contractDateStr !== '0000-00-00' && $contractDateStr !== '1970-01-01';
    
    $isTrial = $hasTrialDate && !$hasContract;
    $isTrialExpired = false;
    
    if ($isTrial) {
        $trialEndTimestamp = strtotime($trialEndDateStr . ' 23:59:59');
        $isTrialExpired = $trialEndTimestamp < $currentTime;
    }

    if ($t->status === 'active') {
        if ($isTrial) {
            if ($isTrialExpired) {
                $passiveTenants++; // Süresi dolmuş deneme, erişim kısıtlı
            } else {
                $trialTenants++; // Aktif deneme
            }
        } else {
            $activeTenants++; // Gerçek aktif müşteri
        }
    } else {
        $passiveTenants++; // Durumu passive olanlar
    }
}

// Son kayıt olan 5 kurum
$recentTenants = $db->getRows("SELECT customer_id, customer_code, tenant_alias, company_name, company_email, status, trial_end_date, sozlesme_bitis_tarihi, created_at FROM kurumlar WHERE del_status = 1 AND deleted_at IS NULL ORDER BY customer_id DESC LIMIT 5");

// Son 5 sistem hatası
$recentErrors = $db->getRows("SELECT error_id, error_message, severity, created_at FROM sistem_hatalari WHERE del_status = 1 ORDER BY error_id DESC LIMIT 5");

?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Master Yönetim Paneli
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">Sistem</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Kontrol Paneli</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <!-- Welcome Banner -->
                <div class="card card-flush border-0 mb-6" style="background: linear-gradient(135deg, #1e2129 0%, #14151a 100%); border: 1px solid rgba(255, 255, 255, 0.03) !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);">
                    <div class="card-body p-8 d-flex align-items-center justify-content-between">
                        <div>
                            <h1 class="fw-bolder text-white mb-2">Hoş geldiniz, <?= htmlspecialchars($username) ?>!</h1>
                            <p class="fs-6 text-gray-400 m-0">Prolyn SaaS altyapısı sorunsuz çalışıyor. Toplam <strong class="text-primary"><?= $totalTenants ?></strong> müşteri ve izole veritabanı aktif durumda.</p>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="symbol symbol-50px symbol-circle bg-light-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="ki-duotone ki-chart-simple-3 fs-2x text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-6 g-xl-9 mb-6">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush h-lg-100">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2"><?= $totalTenants ?></span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Toplam Kurum / Tenant</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0 pb-5">
                                <span class="badge badge-light-primary fs-7 fw-bold">Sistem Genel</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush h-lg-100">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2"><?= $activeTenants ?></span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Aktif Müşteriler</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0 pb-5">
                                <span class="badge badge-light-success fs-7 fw-bold">Erişim Açık</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush h-lg-100">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2"><?= $trialTenants ?></span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Aktif Deneme Sürümleri</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0 pb-5">
                                <span class="badge badge-light-warning fs-7 fw-bold">30 Günlük Deneme</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush h-lg-100">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-danger me-2 lh-1 ls-n2"><?= $passiveTenants ?></span>
                                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Pasif Kurumlar</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0 pb-5">
                                <span class="badge badge-light-danger fs-7 fw-bold">Kısıtlanmış</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Grid -->
                <div class="row g-6 g-xl-9">
                    <!-- Left: Recent Tenants -->
                    <div class="col-xl-8">
                        <div class="card card-flush h-xl-100 shadow-sm">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800 fs-3">Son Kayıt Olan Kurumlar</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-7">Sisteme en son katılan 5 işletme</span>
                                </h3>
                                <div class="card-toolbar">
                                    <a href="crm/tenants.php" class="btn btn-sm btn-light-primary fw-bold">Tümünü Gör</a>
                                </div>
                            </div>
                            <div class="card-body pt-6">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-150px">Kurum Adı</th>
                                                <th class="min-w-100px">Kurum Kodu</th>
                                                <th class="min-w-125px">Durum</th>
                                                <th class="min-w-125px">Bitiş Tarihi</th>
                                                <th class="text-end min-w-70px">İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            <?php if (empty($recentTenants)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-8">Henüz kayıtlı kurum bulunmamaktadır.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recentTenants as $tenant): 
                                                    $trialEndDateStr = $tenant->trial_end_date;
                                                    $contractDateStr = $tenant->sozlesme_bitis_tarihi ?? '';
                                                    $hasTrialDate = !empty($trialEndDateStr) && $trialEndDateStr !== '0000-00-00' && $trialEndDateStr !== '1970-01-01';
                                                    $hasContractDate = !empty($contractDateStr) && $contractDateStr !== '0000-00-00' && $contractDateStr !== '1970-01-01';
                                                    
                                                    $isTrialExpired = false;
                                                    if ($hasTrialDate) {
                                                        $trialEndTimestamp = strtotime($trialEndDateStr . ' 23:59:59');
                                                        $isTrialExpired = $trialEndTimestamp < time();
                                                    }
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="d-flex flex-column">
                                                                    <a href="crm/tenant_detail.php?id=<?= $tenant->customer_id ?>" class="text-gray-800 text-hover-primary fw-bold mb-1"><?= htmlspecialchars($tenant->company_name ?? '') ?></a>
                                                                    <span class="text-muted fs-7"><?= htmlspecialchars($tenant->company_email ?? '') ?></span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><code class="text-gray-700"><?= htmlspecialchars(!empty($tenant->tenant_alias) ? $tenant->tenant_alias : strtoupper($tenant->customer_code ?? '')) ?></code></td>
                                                        <td>
                                                            <?php if ($tenant->status === 'active'): ?>
                                                                <?php if ($isTrialExpired): ?>
                                                                    <span class="badge badge-light-danger">SÜRESİ DOLMUŞ</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-light-success">Aktif</span>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge badge-light-danger">Pasif</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($hasContractDate): ?>
                                                                <span class="text-success fw-bold" title="Sözleşme Bitiş">
                                                                    <i class="ki-duotone ki-calendar-tick fs-7 text-success me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i><?= date('d.m.Y', strtotime($contractDateStr)) ?>
                                                                </span>
                                                            <?php elseif ($hasTrialDate): ?>
                                                                <span class="<?= $isTrialExpired ? 'text-danger fw-bold' : 'text-warning fw-bold' ?>" title="Deneme Bitiş">
                                                                    <i class="ki-duotone ki-time fs-7 <?= $isTrialExpired ? 'text-danger' : 'text-warning' ?> me-1"><span class="path1"></span><span class="path2"></span></i><?= date('d.m.Y', strtotime($trialEndDateStr)) ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="dist/toolbars/db_control.php" class="btn btn-sm btn-light btn-active-light-primary">Yönet</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Quick Actions & System Logs -->
                    <div class="col-xl-4">
                        <div class="card card-flush h-xl-100 shadow-sm">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800 fs-3">Hızlı Bağlantılar</span>
                                </h3>
                            </div>
                            <div class="card-body pt-4">
                                <div class="d-flex flex-column gap-5">
                                    <a href="crm/tenants.php" class="d-flex align-items-center p-4 border border-dashed border-gray-300 rounded text-hover-primary text-gray-700">
                                        <i class="ki-duotone ki-plus-square fs-2x text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <div>
                                            <div class="fw-bold">Yeni Kurum Ekle</div>
                                            <span class="fs-7 text-muted">Sisteme manuel yeni bir tenant tanımla</span>
                                        </div>
                                    </a>

                                    <a href="dist/toolbars/db_control.php" class="d-flex align-items-center p-4 border border-dashed border-gray-300 rounded text-hover-primary text-gray-700">
                                        <i class="ki-duotone ki-shield-search fs-2x text-success me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <div>
                                            <div class="fw-bold">Veritabanı Durumu</div>
                                            <span class="fs-7 text-muted">Tenant izole veritabanlarını teşhis et</span>
                                        </div>
                                    </a>

                                    <a href="logs/notification_queue.php" class="d-flex align-items-center p-4 border border-dashed border-gray-300 rounded text-hover-primary text-gray-700">
                                        <i class="ki-duotone ki-message-text-2 fs-2x text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <div>
                                            <div class="fw-bold">Bildirim Kuyruğu</div>
                                            <span class="fs-7 text-muted">SMTP / SMS gönderimlerini kontrol et</span>
                                        </div>
                                    </a>
                                </div>

                                <!-- Recent System Errors -->
                                <div class="mt-8">
                                    <h4 class="fw-bold text-gray-800 mb-4 fs-4">Sistem Hataları</h4>
                                    <div class="d-flex flex-column gap-3">
                                        <?php if (empty($recentErrors)): ?>
                                            <span class="text-muted fs-7">Kritik sistem hatası bulunmuyor.</span>
                                        <?php else: ?>
                                            <?php foreach ($recentErrors as $error): ?>
                                                <div class="p-3 bg-light-danger rounded d-flex align-items-start">
                                                    <i class="ki-duotone ki-information-5 fs-3 text-danger me-2 mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                    <div>
                                                        <div class="fs-7 text-danger fw-bold mb-1"><?= htmlspecialchars(mb_strimwidth($error->error_message, 0, 45, '...')) ?></div>
                                                        <span class="fs-8 text-muted"><?= date('d.m.Y H:i', strtotime($error->created_at)) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'config/footer.php'; ?>
