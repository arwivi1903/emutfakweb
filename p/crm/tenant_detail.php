<?php
/**
 * Prolyn Admin Panel - Tenant Detail Management
 * prolynweb/p/crm/tenant_detail.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once '../config/sidebar.php';

$tenant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($tenant_id === 0) {
    echo "<script>window.location.href='crm/tenants.php';</script>";
    exit;
}

// POST İşlemleri (Kullanıcı / Not Ekleme ve Düzenleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // NOT İŞLEMLERİ
    if ($_POST['action'] === 'save_note') {
        $note_text = trim($_POST['note_text']);
        $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
        $admin_id  = $_SESSION['admin_id'] ?? 1; // Fallback
        
        $db->Insert("INSERT INTO kurum_notlari (customer_id, admin_id, note_text, is_urgent) VALUES (?, ?, ?, ?)", 
            [$tenant_id, $admin_id, $note_text, $is_urgent]);
            
        echo "<script>window.location.href='crm/tenant_detail.php?id={$tenant_id}&tab=notes&success=note_saved';</script>";
        exit;
    }

    // TRIAL → PAKET GEÇİŞ İŞLEMİ
    if ($_POST['action'] === 'convert_trial') {
        $new_paket = trim($_POST['new_paket']); // Başlangıç, Pro, Kurumsal
        $sozlesme_baslangic = date('Y-m-d'); // Bugünden başlar
        $sozlesme_sure = (int)($_POST['sozlesme_sure'] ?? 12); // Ay cinsinden
        $sozlesme_bitis = date('Y-m-d', strtotime("+{$sozlesme_sure} months"));

        // Paket adını plan_name'e çevir (paket_ozellikleri tablosu için)
        $plan_map = [
            'Başlangıç' => 'basic',
            'Pro' => 'pro',
            'Kurumsal' => 'enterprise'
        ];
        $plan_name = $plan_map[$new_paket] ?? 'basic';

        // Modül haritası
        $module_map = [
            'basic' => '["core", "customer", "appointment", "reports"]',
            'pro' => '["core", "customer", "appointment", "reports", "commercial", "inventory", "accounting"]',
            'enterprise' => '["core", "customer", "appointment", "reports", "commercial", "inventory", "accounting", "invoice", "contract"]'
        ];
        $allowed_modules = $module_map[$plan_name] ?? '[]';

        // kurumlar tablosunu güncelle
        $db->Update("
            UPDATE kurumlar 
            SET musteri_paketi = ?,
                sozlesme_baslangic_tarihi = ?,
                sozlesme_bitis_tarihi = ?,
                allowed_modules = ?,
                updated_at = NOW()
            WHERE customer_id = ?
        ", [$new_paket, $sozlesme_baslangic, $sozlesme_bitis, $allowed_modules, $tenant_id]);

        // customers tablosunu da güncelle (login sistemi bu tabloyu kullanır)
        try {
            $db->Update("
                UPDATE customers 
                SET musteri_paketi = ?,
                    sozlesme_bitis_tarihi = ?,
                    allowed_modules = ?,
                    updated_at = NOW()
                WHERE customer_id = ?
            ", [$new_paket, $sozlesme_bitis, $allowed_modules, $tenant_id]);
        } catch (Exception $e) {
            // customers tablosu farklı yapıda olabilir, sessiz geç
        }

        echo "<script>window.location.href='crm/tenant_detail.php?id={$tenant_id}&tab=info&success=converted';</script>";
        exit;
    }
}

// Tenant Bilgilerini Çek
$tenant = $db->getRowAssoc("SELECT * FROM kurumlar WHERE customer_id = ?", [$tenant_id]);
if (!$tenant) {
    echo "<script>window.location.href='crm/tenants.php';</script>";
    exit;
}

// Tenant DB'ye bağlan ve istatistikleri / kullanıcıları al
$users = [];
$stats = [
    'user_count' => 0,
    'branch_count' => 0,
    'customer_count' => 0
];

if (!empty($tenant['database_name'])) {
    try {
        $tenant_db = new Database($tenant['database_name']);
        
        // Gerçek kullanıcıları çek
        $users = $tenant_db->getRowsAssoc("SELECT * FROM users ORDER BY UserID ASC");
        
        // İstatistikler
        $stats['user_count'] = count($users);
        $branches = $tenant_db->getRowsAssoc("SELECT COUNT(*) as cnt FROM subeler");
        $stats['branch_count'] = $branches[0]['cnt'] ?? 0;
        $customers = $tenant_db->getRowsAssoc("SELECT COUNT(*) as cnt FROM musteriler");
        $stats['customer_count'] = $customers[0]['cnt'] ?? 0;
    } catch (Exception $e) {
        // Hata durumunda sessiz kal
    }
}
// Notları Çek
$notes = $db->getRowsAssoc("
    SELECT n.*, a.full_name as admin_name 
    FROM kurum_notlari n
    LEFT JOIN yoneticiler a ON n.admin_id = a.admin_id
    WHERE n.customer_id = ? 
    ORDER BY n.created_at DESC
", [$tenant_id]);

$active_tab = $_GET['tab'] ?? 'info';
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Kurum Detayları: <?php echo htmlspecialchars($tenant['company_name']); ?>
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted"><a href="crm/tenants.php" class="text-muted text-hover-primary">Müşteri Listesi</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Detaylar</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="crm/tenants.php" class="btn btn-sm fw-bold btn-secondary"><i class="ki-duotone ki-arrow-left fs-2"></i> Geri Dön</a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>İşleminiz başarıyla kaydedildi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Profil Header -->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap">
                            <div class="me-7 mb-4">
                                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                    <?php if($tenant['logo_url']): ?>
                                        <img src="<?php echo htmlspecialchars($tenant['logo_url']); ?>" alt="image" />
                                    <?php else: ?>
                                        <span class="symbol-label bg-light-primary text-primary fs-1 fw-bold"><?php echo mb_substr($tenant['company_name'], 0, 1); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="text-gray-900 fs-2 fw-bold me-1"><?php echo htmlspecialchars($tenant['company_name']); ?></span>
                                            <?php if ($tenant['status'] === 'active'): ?>
                                                <i class="ki-duotone ki-verify fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                            <span class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                <i class="ki-duotone ki-sms fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                <?php echo htmlspecialchars($tenant['company_email']); ?>
                                            </span>
                                            <span class="d-flex align-items-center text-gray-400 text-hover-primary mb-2">
                                                <i class="ki-duotone ki-abstract-41 fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                DB: <?php echo htmlspecialchars($tenant['database_name'] ?? 'Yok'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex my-4">
                                        <?php 
                                            if ($tenant['status'] === 'active') $badge = 'badge-light-success';
                                            elseif ($tenant['status'] === 'suspended') $badge = 'badge-light-danger';
                                            else $badge = 'badge-light-warning';
                                        ?>
                                        <span class="badge <?php echo $badge; ?> fs-5 text-uppercase px-4 py-3"><?php echo htmlspecialchars($tenant['status']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nav Tabs -->
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5 <?php echo $active_tab == 'info' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_info">Kurum Bilgileri</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5 <?php echo $active_tab == 'users' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_users">Kullanıcılar (<?php echo count($users); ?>)</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5 <?php echo $active_tab == 'notes' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab_notes">CRM Notları (<?php echo count($notes); ?>)</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content">
                    
                    <!-- TAB 1: INFO -->
                    <div class="tab-pane fade <?php echo $active_tab == 'info' ? 'show active' : ''; ?>" id="tab_info" role="tabpanel">
                        <div class="card shadow-sm mb-5 mb-xl-10">
                            <div class="card-header cursor-pointer">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Kurum Detayları</h3>
                                </div>
                            </div>
                            <div class="card-body p-9">
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Telefon Numarası</label>
                                    <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800"><?php echo htmlspecialchars($tenant['company_phone'] ?? '-'); ?></span></div>
                                </div>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Sektör / Endüstri</label>
                                    <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800"><?php echo htmlspecialchars($tenant['industry'] ?? '-'); ?></span></div>
                                </div>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Adres</label>
                                    <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800"><?php echo htmlspecialchars($tenant['company_address'] ?? 'Belirtilmemiş'); ?></span></div>
                                </div>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Yetkili Kişi (Contact)</label>
                                    <div class="col-lg-8">
                                        <span class="fw-bold fs-6 text-gray-800"><?php echo htmlspecialchars($tenant['contact_name'] ?? '-'); ?></span><br>
                                        <span class="text-muted"><?php echo htmlspecialchars($tenant['contact_email'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Kayıt Tarihi</label>
                                    <div class="col-lg-8"><span class="fw-bold fs-6 text-gray-800"><?php echo date('d.m.Y H:i', strtotime($tenant['created_at'])); ?></span></div>
                                </div>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Sistem İstatistikleri</label>
                                    <div class="col-lg-8">
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 mb-1">
                                                <i class="ki-duotone ki-profile-user fs-4 text-primary me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                <strong><?php echo $stats['user_count'] ?? 0; ?></strong> Aktif Kullanıcı
                                            </span>
                                            <span class="text-gray-800 mb-1">
                                                <i class="ki-duotone ki-shop fs-4 text-warning me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                <strong><?php echo $stats['branch_count'] ?? 0; ?></strong> Kayıtlı Şube
                                            </span>
                                            <span class="text-gray-800">
                                                <i class="ki-duotone ki-basket fs-4 text-success me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                <strong><?php echo $stats['customer_count'] ?? 0; ?></strong> Müşteri / Kayıt
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    $allowed_modules_raw = $tenant['allowed_modules'] ?? '';
                                    if (!empty($allowed_modules_raw)): 
                                        $module_names = [
                                            'core' => 'Temel CRM',
                                            'customer' => 'Müşteri Yönetimi',
                                            'commercial' => 'Ticari (Satış/Alış)',
                                            'appointment' => 'Randevu & Takvim',
                                            'current_account' => 'Cari Hesap',
                                            'inventory' => 'Stok & Depo',
                                            'accounting' => 'Ön Muhasebe',
                                            'invoice' => 'Fatura',
                                            'contract' => 'Sözleşme',
                                            'reports' => 'Raporlar'
                                        ];
                                        $modules = json_decode($allowed_modules_raw, true);
                                        if (!is_array($modules)) {
                                            $modules = [];
                                        }
                                        
                                        if (empty($modules)) {
                                            $allowed_modules_text = 'Henüz modül atanmamış';
                                            $text_class = 'text-muted fst-italic';
                                        } else {
                                            $modules_tr = array_map(function($m) use ($module_names) {
                                                return $module_names[$m] ?? ucfirst($m);
                                            }, $modules);
                                            $allowed_modules_text = implode(', ', $modules_tr);
                                            $text_class = 'text-gray-800';
                                        }
                                ?>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">İzin Verilen Modüller</label>
                                    <div class="col-lg-8">
                                        <span class="fw-bold fs-6 <?php echo $text_class; ?>"><?php echo htmlspecialchars($allowed_modules_text); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if($tenant['trial_start_date'] || $tenant['trial_end_date']): ?>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Deneme Dönemi</label>
                                    <div class="col-lg-8">
                                        <?php 
                                            $trial_start = $tenant['trial_start_date'] ? date('d.m.Y', strtotime($tenant['trial_start_date'])) : '—';
                                            $trial_end = $tenant['trial_end_date'] ? date('d.m.Y', strtotime($tenant['trial_end_date'])) : '—';
                                            $is_trial_expired = $tenant['trial_end_date'] && strtotime($tenant['trial_end_date']) < time();
                                        ?>
                                        <span class="fw-bold fs-6 <?php echo $is_trial_expired ? 'text-danger' : 'text-info'; ?>">
                                            <?php echo $trial_start; ?> — <?php echo $trial_end; ?>
                                        </span>
                                        <?php if($is_trial_expired): ?>
                                            <span class="badge badge-light-danger ms-2">Süresi Dolmuş</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if($tenant['sozlesme_baslangic_tarihi'] || $tenant['sozlesme_bitis_tarihi']): ?>
                                <div class="row mb-7">
                                    <label class="col-lg-4 fw-semibold text-muted">Sözleşme Dönemi</label>
                                    <div class="col-lg-8">
                                        <?php 
                                            $sozl_start = $tenant['sozlesme_baslangic_tarihi'] ? date('d.m.Y', strtotime($tenant['sozlesme_baslangic_tarihi'])) : '—';
                                            $sozl_end = $tenant['sozlesme_bitis_tarihi'] ? date('d.m.Y', strtotime($tenant['sozlesme_bitis_tarihi'])) : '—';
                                            $is_sozl_expired = $tenant['sozlesme_bitis_tarihi'] && strtotime($tenant['sozlesme_bitis_tarihi']) < time();
                                        ?>
                                        <span class="fw-bold fs-6 <?php echo $is_sozl_expired ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo $sozl_start; ?> — <?php echo $sozl_end; ?>
                                        </span>
                                        <?php if($is_sozl_expired): ?>
                                            <span class="badge badge-light-danger ms-2">Süresi Dolmuş</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php
                        // Trial durumu hesapla
                        $is_trial = !empty($tenant['trial_end_date']) && empty($tenant['sozlesme_bitis_tarihi']);
                        $is_trial_active = $is_trial && strtotime($tenant['trial_end_date']) >= strtotime(date('Y-m-d'));
                        $is_trial_expired_flag = $is_trial && strtotime($tenant['trial_end_date']) < strtotime(date('Y-m-d'));
                        $has_contract = !empty($tenant['sozlesme_bitis_tarihi']);
                        ?>

                        <?php if($is_trial_expired_flag): ?>
                        <!-- Trial Süresi Dolmuş Uyarısı -->
                        <div class="card shadow-sm mb-5 mb-xl-10 border border-danger">
                            <div class="card-body p-7">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-50px me-5">
                                        <span class="symbol-label bg-light-danger">
                                            <i class="ki-duotone ki-timer fs-1 text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="text-danger fw-bold fs-4">Deneme Süresi Dolmuş!</span>
                                        <span class="text-muted fw-semibold d-block fs-7">Bu müşterinin trial süresi sona erdi. Sisteme giriş yapamaz durumda.</span>
                                    </div>
                                </div>

                                <div class="separator separator-dashed mb-5"></div>
                                <h4 class="fw-bold text-gray-800 mb-5"><i class="ki-duotone ki-rocket fs-3 text-primary me-2"><span class="path1"></span><span class="path2"></span></i> Pakete Geçiş Yap</h4>

                                <form action="crm/tenant_detail.php?id=<?php echo $tenant_id; ?>" method="POST">
                                    <?= csrfTokenField() ?>
                                    <input type="hidden" name="action" value="convert_trial">
                                    <div class="row g-5">
                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold">Yeni Paket</label>
                                            <select name="new_paket" class="form-select form-select-solid" required>
                                                <option value="Başlangıç">Başlangıç (5 Kullanıcı)</option>
                                                <option value="Pro" selected>Pro (25 Kullanıcı)</option>
                                                <option value="Kurumsal">Kurumsal (Sınırsız)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Sözleşme Süresi</label>
                                            <select name="sozlesme_sure" class="form-select form-select-solid">
                                                <option value="1">1 Ay</option>
                                                <option value="3">3 Ay</option>
                                                <option value="6">6 Ay</option>
                                                <option value="12" selected>12 Ay (1 Yıl)</option>
                                                <option value="24">24 Ay (2 Yıl)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="ki-duotone ki-check-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Üyeliği Başlat
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php elseif($is_trial_active): ?>
                        <!-- Trial Aktif Bilgi Kartı -->
                        <div class="card shadow-sm mb-5 mb-xl-10 border border-info">
                            <div class="card-body p-7">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-50px me-5">
                                        <span class="symbol-label bg-light-info">
                                            <i class="ki-duotone ki-time fs-1 text-info"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="text-info fw-bold fs-4">Deneme Süreci Devam Ediyor</span>
                                        <?php 
                                            $remaining = (strtotime($tenant['trial_end_date']) - time()) / 86400;
                                            $remaining = max(0, ceil($remaining));
                                        ?>
                                        <span class="text-muted fw-semibold d-block fs-7">Kalan süre: <strong class="text-dark"><?php echo $remaining; ?> gün</strong></span>
                                    </div>
                                </div>

                                <div class="separator separator-dashed mb-5"></div>
                                <h4 class="fw-bold text-gray-800 mb-5"><i class="ki-duotone ki-rocket fs-3 text-primary me-2"><span class="path1"></span><span class="path2"></span></i> Hemen Pakete Geçiş Yap</h4>

                                <form action="crm/tenant_detail.php?id=<?php echo $tenant_id; ?>" method="POST">
                                    <?= csrfTokenField() ?>
                                    <input type="hidden" name="action" value="convert_trial">
                                    <div class="row g-5">
                                        <div class="col-md-5">
                                            <label class="form-label fw-semibold">Yeni Paket</label>
                                            <select name="new_paket" class="form-select form-select-solid" required>
                                                <option value="Başlangıç">Başlangıç (5 Kullanıcı)</option>
                                                <option value="Pro" selected>Pro (25 Kullanıcı)</option>
                                                <option value="Kurumsal">Kurumsal (Sınırsız)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Sözleşme Süresi</label>
                                            <select name="sozlesme_sure" class="form-select form-select-solid">
                                                <option value="1">1 Ay</option>
                                                <option value="3">3 Ay</option>
                                                <option value="6">6 Ay</option>
                                                <option value="12" selected>12 Ay (1 Yıl)</option>
                                                <option value="24">24 Ay (2 Yıl)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ki-duotone ki-check-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Üyeliği Başlat
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- TAB 2: USERS -->
                    <div class="tab-pane fade <?php echo $active_tab == 'users' ? 'show active' : ''; ?>" id="tab_users" role="tabpanel">
                        <div class="card shadow-sm mb-5 mb-xl-10">
                            <div class="card-header pt-6">
                                <div class="card-title"><h3 class="fw-bold m-0">Kurum Kullanıcıları</h3></div>
                                <div class="card-toolbar">
                                    <a href="crm/tenant_user_edit.php?tenant_id=<?php echo $tenant_id; ?>" class="btn btn-sm btn-primary">
                                        <i class="ki-duotone ki-plus fs-2"></i> Yeni Kullanıcı Ekle
                                    </a>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th>Ad Soyad / E-Posta</th>
                                                <th>Rol</th>
                                                <th>Durum</th>
                                                <th>Son Giriş</th>
                                                <th class="text-end">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                            <?php if (!empty($users)): ?>
                                                <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold mb-1"><?php echo htmlspecialchars($user['UserName']); ?></span>
                                                            <span class="text-muted fs-8"><?php echo htmlspecialchars($user['UserMail']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            $role_badge = 'badge-light-primary';
                                                            $role_text = 'Standart';
                                                            if($user['UserAuthority'] == 1) { $role_badge = 'badge-light-danger'; $role_text = 'Sistem Yöneticisi'; }
                                                            elseif($user['UserAuthority'] == 2) { $role_badge = 'badge-light-warning'; $role_text = 'Yönetici'; }
                                                            else { $role_text = 'Kullanıcı ('.$user['UserAuthority'].')'; }
                                                        ?>
                                                        <span class="badge <?php echo $role_badge; ?> text-uppercase"><?php echo $role_text; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if($user['UserStatus'] == 1): ?>
                                                            <span class="badge badge-light-success">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-light-danger">Pasif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">ERP DB'den okundu</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="crm/tenant_user_edit.php?tenant_id=<?php echo $tenant_id; ?>&user_id=<?php echo $user['UserID']; ?>" class="btn btn-icon btn-light-primary btn-sm" title="Düzenle">
                                                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="5" class="text-center text-muted py-5">Sistemde bu kuruma ait kayıtlı kullanıcı bulunmuyor.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: NOTES -->
                    <div class="tab-pane fade <?php echo $active_tab == 'notes' ? 'show active' : ''; ?>" id="tab_notes" role="tabpanel">
                        
                        <div class="card shadow-sm mb-5">
                            <div class="card-header pt-6 border-0">
                                <div class="card-title"><h3 class="fw-bold m-0">Yeni Not Ekle</h3></div>
                            </div>
                            <div class="card-body pt-0">
                                <form action="crm/tenant_detail.php" method="POST">
                            <?= csrfTokenField() ?>
                                    <input type="hidden" name="action" value="save_note">
                                    <div class="fv-row mb-5">
                                        <textarea class="form-control form-control-solid" rows="3" name="note_text" placeholder="Müşteri hakkında notunuzu buraya yazın..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-check form-switch form-check-custom form-check-danger form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="is_urgent" id="inp_is_urgent" value="1" />
                                            <label class="form-check-label fw-bold text-danger" for="inp_is_urgent">
                                                Acil / Önemli Not
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Notu Kaydet</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Not Listesi -->
                        <div class="card shadow-sm">
                            <div class="card-header pt-6">
                                <div class="card-title"><h3 class="fw-bold m-0">Geçmiş Notlar</h3></div>
                            </div>
                            <div class="card-body pt-5">
                                <?php if(!empty($notes)): ?>
                                    <div class="timeline">
                                        <?php foreach($notes as $note): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-line w-40px"></div>
                                                <div class="timeline-icon symbol symbol-circle symbol-40px <?php echo $note['is_urgent'] ? 'bg-light-danger' : 'bg-light-primary'; ?>">
                                                    <i class="ki-duotone <?php echo $note['is_urgent'] ? 'ki-information text-danger' : 'ki-message-text-2 text-primary'; ?> fs-2 mt-2">
                                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                                    </i>
                                                </div>
                                                <div class="timeline-content mt-n1">
                                                    <div class="pe-3 mb-5">
                                                        <div class="fs-5 fw-semibold mb-2 <?php echo $note['is_urgent'] ? 'text-danger' : 'text-gray-800'; ?>">
                                                            <?php echo nl2br(htmlspecialchars($note['note_text'])); ?>
                                                        </div>
                                                        <div class="d-flex align-items-center mt-1 fs-7">
                                                            <div class="text-muted me-2 fs-8">Ekleyen:</div>
                                                            <div class="text-primary fw-bold me-4"><?php echo htmlspecialchars($note['admin_name'] ?? 'Sistem'); ?></div>
                                                            <div class="text-muted fs-8">
                                                                <i class="ki-duotone ki-calendar fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                                <?php echo date('d.m.Y H:i', strtotime($note['created_at'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-5">Bu kurum için henüz bir not girilmemiş.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../config/footer.php'; ?>
