<?php
/**
 * Prolyn Admin Panel - Dashboard
 * Prolyn/p/index.php
 */

require_once 'config/header.php';
require_once 'config/sidebar.php';

// Veritabanı ve istatistikleri çek
$admin_id = $_SESSION['admin_id'] ?? 0;
$adminRow = $db->getRow("SELECT username FROM yoneticiler WHERE admin_id = ?", [$admin_id]);
$username = $adminRow ? $adminRow->username : 'Yönetici';

// İstatistikleri doğru mantıkla (tenants.php ile aynı) hesapla
$allTenants = $db->getRows("SELECT status, trial_end_date, sozlesme_bitis_tarihi FROM kurumlar WHERE deleted_at IS NULL");
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
$recentTenants = $db->getRows("SELECT customer_id, customer_code, tenant_alias, company_name, company_email, status, trial_end_date, sozlesme_bitis_tarihi, created_at FROM kurumlar WHERE deleted_at IS NULL ORDER BY customer_id DESC LIMIT 5");

// Son 5 sistem hatası
$recentErrors = $db->getRows("SELECT id, message AS error_message, created_at FROM sistem_hatalari ORDER BY id DESC LIMIT 5");

// Gelen mesajlar ve potansiyel müşteriler
$yeniMesajSayisi = 0;
$sonMesajlar = [];
$leadTakipBugun = 0;
$leadOzet = ['toplam' => 0, 'sicak' => 0, 'deneme' => 0];
try {
    if (method_exists($db, 'tableExists') && $db->tableExists('iletisim_mesajlari')) {
        $yeniMesajSayisi = (int)$db->getColumn("SELECT COUNT(*) FROM iletisim_mesajlari WHERE status = 'new'");
        $sonMesajlar = (array)$db->getRowsAssoc("SELECT id, ad, email, konu, status, created_at FROM iletisim_mesajlari WHERE status NOT IN ('spam','archived') ORDER BY id DESC LIMIT 6");
    }
    if (method_exists($db, 'tableExists') && $db->tableExists('potansiyel_musteriler')) {
        $leadTakipBugun = (int)$db->getColumn("SELECT COUNT(*) FROM potansiyel_musteriler WHERE sonraki_takip IS NOT NULL AND sonraki_takip <= CURDATE() AND durum NOT IN ('deneme','musteri','olumsuz','ret')");
        $leadOzet = $db->getRowAssoc("SELECT COUNT(*) toplam, SUM(durum IN ('ilgileniyor','demo')) sicak, SUM(durum IN ('deneme','musteri')) deneme FROM potansiyel_musteriler") ?: $leadOzet;
    }
} catch (Throwable $e) {
}

$resetRequestPending = 0;
$resetQueuePending = 0;
$resetTokenOpen = 0;
$recentResetRequests = [];

try {
    if (method_exists($db, 'tableExists') && $db->tableExists('sifre_sifirlama_talepleri')) {
        $resetRequestPending = (int)$db->getColumn(
            "SELECT COUNT(*) FROM sifre_sifirlama_talepleri WHERE request_status IN ('pending', 'in_review', 'matched')"
        );

        $recentResetRequests = $db->getRowsAssoc(
            "SELECT t.request_id, t.kurum_kodu, t.request_email, t.request_status, t.mail_queue_status, t.created_at,
                    k.company_name
             FROM sifre_sifirlama_talepleri t
             LEFT JOIN kurumlar k ON k.customer_id = t.customer_id
             WHERE t.request_status NOT IN ('kurum_not_found', 'email_not_found')
             ORDER BY t.request_id DESC
             LIMIT 6"
        );
    }

    if (method_exists($db, 'tableExists') && $db->tableExists('mail_kuyrugu')) {
        $resetQueuePending = (int)$db->getColumn(
            "SELECT COUNT(*) FROM mail_kuyrugu WHERE status IN ('pending', 'queued')"
        );
    }

    if (method_exists($db, 'tableExists') && $db->tableExists('sifre_sifirlama')) {
        $resetTokenOpen = (int)$db->getColumn(
            "SELECT COUNT(*) FROM sifre_sifirlama WHERE used = 0 AND expires_at >= NOW()"
        );
    }
} catch (Throwable $e) {
    $resetRequestPending = 0;
    $resetQueuePending = 0;
    $resetTokenOpen = 0;
    $recentResetRequests = [];
}

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

                <!-- İş akışı -->
                <div class="row g-6 g-xl-9 mb-6">
                    <?php foreach ([
                        [$yeniMesajSayisi, 'Okunmamış Mesaj', 'crm/messages.php', 'Mesajlara git', 'primary', 'ki-sms'],
                        [$leadTakipBugun, 'Bugün Takip (Lead)', 'crm/leads.php?takip=bugun', 'Takip listesi', 'warning', 'ki-calendar-tick'],
                        [$resetRequestPending, 'Şifre Talebi (Bekleyen)', 'settings/password_reset.php', 'Talep havuzu', 'danger', 'ki-key'],
                        [$resetQueuePending, 'Mail Kuyruğu (Bekleyen)', 'settings/email_queue.php', 'Kuyruğu gör', 'info', 'ki-send'],
                    ] as $k): ?>
                    <div class="col-sm-6 col-xl-3">
                        <a href="<?= $k[2] ?>" class="card card-flush h-100 border border-dashed border-<?= $k[4] ?> text-decoration-none">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-<?= $k[4] ?> me-2 lh-1 ls-n2"><?= (int)$k[0] ?></span>
                                    <span class="text-gray-500 pt-1 fw-semibold fs-6"><?= $k[1] ?></span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0 pb-5">
                                <span class="btn btn-sm btn-light-<?= $k[4] ?> fw-bold"><?= $k[3] ?></span>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Gelen mesajlar + potansiyel müşteri özeti -->
                <div class="row g-6 g-xl-9 mb-6">
                    <div class="col-xl-8">
                        <div class="card card-flush h-100 shadow-sm">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800 fs-3">Son Gelen Mesajlar</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-7">prolyn.net iletişim formu</span>
                                </h3>
                                <div class="card-toolbar"><a href="crm/messages.php" class="btn btn-sm btn-light-primary fw-bold">Tümü</a></div>
                            </div>
                            <div class="card-body pt-2">
                                <?php if (!$sonMesajlar): ?>
                                    <div class="text-center text-muted py-10">Henüz mesaj yok.</div>
                                <?php else: foreach ($sonMesajlar as $m): $yeni = $m['status'] === 'new'; ?>
                                    <a href="crm/messages.php?id=<?= (int)$m['id'] ?>" class="d-flex align-items-center py-4 border-bottom border-gray-200 text-decoration-none">
                                        <span class="bullet bullet-dot <?= $yeni ? 'bg-primary' : 'bg-gray-300' ?> h-10px w-10px me-4 flex-shrink-0"></span>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between gap-3">
                                                <span class="<?= $yeni ? 'fw-bolder text-gray-900' : 'fw-semibold text-gray-700' ?> fs-6 text-truncate"><?= htmlspecialchars((string)$m['konu']) ?></span>
                                                <span class="text-muted fs-8 text-nowrap"><?= date('d.m.Y H:i', strtotime($m['created_at'])) ?></span>
                                            </div>
                                            <div class="text-muted fs-7 text-truncate"><?= htmlspecialchars((string)$m['ad']) ?> · <?= htmlspecialchars((string)$m['email']) ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush h-100 shadow-sm">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800 fs-3">Potansiyel Müşteriler</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-7">Kampanya satış hattı</span>
                                </h3>
                            </div>
                            <div class="card-body pt-2">
                                <?php foreach ([['Toplam lead', (int)($leadOzet['toplam'] ?? 0), 'gray-800'], ['İlgilenen / demo', (int)($leadOzet['sicak'] ?? 0), 'primary'], ['Deneme / müşteri', (int)($leadOzet['deneme'] ?? 0), 'success'], ['Bugün takip', $leadTakipBugun, 'warning']] as $l): ?>
                                <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-gray-200">
                                    <span class="text-gray-600 fw-semibold"><?= $l[0] ?></span>
                                    <span class="fs-3 fw-bold text-<?= $l[2] ?>"><?= $l[1] ?></span>
                                </div>
                                <?php endforeach; ?>
                                <div class="d-flex gap-2 mt-6">
                                    <a href="crm/leads.php" class="btn btn-sm btn-primary flex-grow-1">Lead listesi</a>
                                    <a href="crm/leads.php?takip=bugun" class="btn btn-sm btn-light-warning flex-grow-1">Bugün takip</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-6 g-xl-9 mb-6">
                    <div class="col-12">
                        <div class="card card-flush shadow-sm">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800 fs-3">Son Şifre Sıfırlama Talepleri</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-7">Kurum kodu/alias, email ve talep durumu</span>
                                </h3>
                                <div class="card-toolbar">
                                    <a href="settings/password_reset.php" class="btn btn-sm btn-light-warning fw-bold">Talep Yönetimi</a>
                                </div>
                            </div>
                            <div class="card-body pt-4">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-7 gy-4">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase">
                                                <th>ID</th>
                                                <th>Kurum</th>
                                                <th>Email</th>
                                                <th>Durum</th>
                                                <th>Kuyruk</th>
                                                <th>Tarih</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-700">
                                            <?php if (empty($recentResetRequests)): ?>
                                                <tr><td colspan="6" class="text-center text-muted py-7">Henüz talep kaydı yok.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($recentResetRequests as $req): ?>
                                                    <?php
                                                        $st = (string)($req['request_status'] ?? 'pending');
                                                        $stClass = 'badge-light-secondary';
                                                        if ($st === 'matched') $stClass = 'badge-light-success';
                                                        if ($st === 'pending' || $st === 'in_review') $stClass = 'badge-light-warning';
                                                        if ($st === 'completed') $stClass = 'badge-light-primary';
                                                        if ($st === 'email_not_found' || $st === 'kurum_not_found' || $st === 'rejected') $stClass = 'badge-light-danger';
                                                    ?>
                                                    <tr>
                                                        <td>#<?= (int)($req['request_id'] ?? 0) ?></td>
                                                        <td>
                                                            <div class="fw-bold"><?= htmlspecialchars((string)($req['company_name'] ?: '-')) ?></div>
                                                            <div class="text-muted">Kod/Alias: <?= htmlspecialchars((string)($req['kurum_kodu'] ?? '-')) ?></div>
                                                        </td>
                                                        <td><?= htmlspecialchars((string)($req['request_email'] ?? '-')) ?></td>
                                                        <td><span class="badge <?= $stClass ?>"><?= htmlspecialchars($st) ?></span></td>
                                                        <td><span class="badge badge-light-info"><?= htmlspecialchars((string)($req['mail_queue_status'] ?? 'pending')) ?></span></td>
                                                        <td><?= htmlspecialchars((string)($req['created_at'] ?? '-')) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                                        <?php 
                                                            $codeParts = [];
                                                            if (!empty($tenant->customer_code)) $codeParts[] = strtoupper($tenant->customer_code);
                                                            if (!empty($tenant->tenant_alias)) $codeParts[] = $tenant->tenant_alias;
                                                        ?>
                                                        <td><code class="text-gray-700"><?= htmlspecialchars(implode(' / ', $codeParts)) ?></code></td>
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
