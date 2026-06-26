<?php
/**
 * Prolyn Admin Panel - Tenant Add/Edit
 * prolynweb/p/crm/tenant_form.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once '../config/sidebar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tenant = [];

if ($id > 0) {
    $tenant = $db->getRowAssoc("SELECT * FROM kurumlar WHERE customer_id = ?", [$id]);
    if (!$tenant) {
        echo "<script>window.location.href='crm/tenants.php';</script>";
        exit;
    }
}

// Fetch Account Managers (yoneticiler) for dropdown
$managers = [];
try {
    $managers = $db->getRowsAssoc("SELECT admin_id, full_name FROM yoneticiler WHERE deleted_at IS NULL");
} catch(Exception $e) {}

// Form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_tenant') {
    $customer_id    = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;

    // Genel
    $company_name   = trim($_POST['company_name']);
    $company_email  = trim($_POST['company_email']);
    $company_phone  = trim($_POST['company_phone']);
    $industry       = trim($_POST['industry']);
    $contact_person = trim($_POST['contact_person'] ?? '');

    // Finans
    $tax_office = trim($_POST['tax_office'] ?? '');
    $tax_number = trim($_POST['tax_number'] ?? '');
    $billing_address = trim($_POST['billing_address'] ?? '');

    // Abonelik
    $subscription_type = $_POST['subscription_type'] ?? 'contract';
    $musteri_paketi = trim($_POST['musteri_paketi'] ?? '');
    
    $trial_start_date = null;
    $trial_end_date = null;
    $sozlesme_baslangic_tarihi = null;
    $sozlesme_bitis_tarihi = null;

    if ($subscription_type == 'trial') {
        $trial_start_date = !empty($_POST['trial_start_date']) ? date('Y-m-d', strtotime($_POST['trial_start_date'])) : null;
        $trial_end_date = !empty($_POST['trial_end_date']) ? date('Y-m-d', strtotime($_POST['trial_end_date'])) : null;
    } else {
        $sozlesme_baslangic_tarihi = !empty($_POST['sozlesme_baslangic_tarihi']) ? date('Y-m-d', strtotime($_POST['sozlesme_baslangic_tarihi'])) : null;
        $sozlesme_bitis_tarihi = !empty($_POST['sozlesme_bitis_tarihi']) ? date('Y-m-d', strtotime($_POST['sozlesme_bitis_tarihi'])) : null;
    }

    // Teknik
    $tenant_alias = trim($_POST['tenant_alias'] ?? '');
    $status         = $_POST['status'] ?? 'active';
    $account_manager_id = !empty($_POST['account_manager_id']) ? (int)$_POST['account_manager_id'] : null;

    // Modüller Haritası
    $plan_map = [
        'Başlangıç' => 'basic',
        'Pro' => 'pro',
        'Kurumsal' => 'enterprise'
    ];
    $plan_name = $plan_map[$musteri_paketi] ?? 'basic';

    $module_map = [
        'basic' => '["core", "customer", "appointment", "reports"]',
        'pro' => '["core", "customer", "appointment", "reports", "commercial", "inventory", "accounting"]',
        'enterprise' => '["core", "customer", "appointment", "reports", "commercial", "inventory", "accounting", "invoice", "contract"]'
    ];
    $allowed_modules = $module_map[$plan_name];

    // Veritabanı adı belirleme
    $database_name = '';
    $is_new = ($customer_id == 0);
    
    if ($is_new) {
        $max_db_row = $db->getRowAssoc("SELECT MAX(CAST(SUBSTRING(database_name, 9) AS UNSIGNED)) as max_k FROM kurumlar WHERE database_name LIKE 'prolyn_k%'");
        $next_k = (!empty($max_db_row['max_k'])) ? (int)$max_db_row['max_k'] + 1 : 1;
        $database_name = 'prolyn_k' . $next_k;
    } else {
        $existing = $db->getRowAssoc("SELECT database_name FROM kurumlar WHERE customer_id = ?", [$customer_id]);
        $database_name = $existing['database_name'] ?? '';
    }

    if (!$is_new) {
        $db->Update("
            UPDATE kurumlar
            SET company_name=?, company_email=?, company_phone=?, industry=?, status=?, subscription_type=?, trial_start_date=?, trial_end_date=?,
                musteri_paketi=?, contact_person=?, tax_office=?, tax_number=?, billing_address=?, account_manager_id=?, tenant_alias=?, sozlesme_baslangic_tarihi=?, sozlesme_bitis_tarihi=?, allowed_modules=?, updated_at=NOW()
            WHERE customer_id=?
        ", [$company_name, $company_email, $company_phone, $industry, $status, $subscription_type, $trial_start_date, $trial_end_date,
            $musteri_paketi, $contact_person, $tax_office, $tax_number, $billing_address, $account_manager_id, $tenant_alias, $sozlesme_baslangic_tarihi, $sozlesme_bitis_tarihi, $allowed_modules, $customer_id]);
    } else {
        $customer_id = $db->Insert("
            INSERT INTO kurumlar
            (company_name, company_email, company_phone, database_name, industry, status, subscription_type, trial_start_date, trial_end_date,
             musteri_paketi, contact_person, tax_office, tax_number, billing_address, account_manager_id, tenant_alias, sozlesme_baslangic_tarihi, sozlesme_bitis_tarihi, allowed_modules)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [$company_name, $company_email, $company_phone, $database_name, $industry, $status, $subscription_type, $trial_start_date, $trial_end_date,
            $musteri_paketi, $contact_person, $tax_office, $tax_number, $billing_address, $account_manager_id, $tenant_alias, $sozlesme_baslangic_tarihi, $sozlesme_bitis_tarihi, $allowed_modules]);

        // VERITABANI OTOMATIK KURULUM (PROVISIONING)
        try {
            $pdo_master = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', 'RootPass1903!');
            $pdo_master->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 1. DB Yarat
            $pdo_master->exec("CREATE DATABASE `$database_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // 2. Default schema'yı içeri aktar
            $sql_file = __DIR__ . '/../database/prolyn_default.sql';
            if (file_exists($sql_file)) {
                $mysql_path = file_exists("/Applications/XAMPP/bin/mysql") ? "/Applications/XAMPP/bin/mysql" : "mysql";
                $cmd = sprintf("%s -u root -p'RootPass1903!' %s < %s", $mysql_path, escapeshellarg($database_name), escapeshellarg($sql_file));
                shell_exec($cmd);
                
                // 3. Tablolara varsayılan verileri bas
                $pdo_tenant = new PDO("mysql:host=127.0.0.1;dbname=$database_name;charset=utf8mb4", 'root', 'RootPass1903!');
                $pdo_tenant->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // firmalar (ID = 1 olması beklenir)
                $stmt_firma = $pdo_tenant->prepare("INSERT INTO firmalar (FirmaAdi, FirmaDetay, FirmaVergi, FirmaVNO, BayiCity, BayiTown, BayiAdres, CustomerID) VALUES (?, '', ?, ?, '', '', ?, ?)");
                $stmt_firma->execute([$company_name, $tax_office, $tax_number, $billing_address, $customer_id]);
                $firma_id = $pdo_tenant->lastInsertId();
                if (!$firma_id) $firma_id = 1;
                
                // subeler
                $stmt_sube = $pdo_tenant->prepare("INSERT INTO subeler (FirmaID, CustomerID, BranchName, BranchStatus) VALUES (?, ?, 'Merkez', 1)");
                $stmt_sube->execute([$firma_id, $customer_id]);
                $sube_id = $pdo_tenant->lastInsertId();
                
                // users (Yetkili E-Posta / 123456)
                $default_pass = md5('123456');
                $stmt_user = $pdo_tenant->prepare("INSERT INTO users (FirmaID, BranchID, UserName, UserMail, UserPhone, UserPass, UserAuthority, UserStatus) VALUES (?, ?, ?, ?, ?, ?, 1, 1)");
                $stmt_user->execute([$firma_id, $sube_id, $contact_person, $company_email, $company_phone, $default_pass]);
            }
        } catch (Exception $e) {
            error_log("DB Provisioning failed for $database_name: " . $e->getMessage());
        }
    }

    // Fatura/Abonelik Hesaplaması
    $price_map = ['basic' => 499, 'pro' => 999, 'enterprise' => 2499];
    $branches_map = ['basic' => 1, 'pro' => 2, 'enterprise' => 5];

    $price_per_month = $price_map[$plan_name];
    $num_branches = $branches_map[$plan_name];

    $existing_sub = $db->getRowAssoc("SELECT subscription_id FROM abonelikler WHERE customer_id = ? AND del_status = 1 ORDER BY subscription_id DESC LIMIT 1", [$customer_id]);

    if ($subscription_type == 'trial') {
        // Deneme sürümü için fatura tutarı 0
        $total_price = 0;
        $price_per_month = 0;
        $months = 1; // deneme süresi genelde 1 aydır ama önemli değil
        
        if ($existing_sub) {
            $db->Update("
                UPDATE abonelikler
                SET plan_name = ?, price_per_month = ?, total_price = ?, billing_cycle = ?, num_branches = ?, start_date = ?, end_date = ?, updated_at = NOW(), status = 'Deneme'
                WHERE subscription_id = ?
            ", [$plan_name, $price_per_month, $total_price, $months, $num_branches, $trial_start_date, $trial_end_date, $existing_sub['subscription_id']]);
        } else {
            $db->Insert("
                INSERT INTO abonelikler
                (customer_id, plan_name, num_branches, price_per_month, total_price, billing_cycle, start_date, end_date, status, auto_renew)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Deneme', 0)
            ", [$customer_id, $plan_name, $num_branches, $price_per_month, $total_price, $months, $trial_start_date, $trial_end_date]);
        }
    } else {
        // Sözleşmeli hesaplama
        if ($sozlesme_baslangic_tarihi && $sozlesme_bitis_tarihi) {
            $d1 = new DateTime($sozlesme_baslangic_tarihi);
            $d2 = new DateTime($sozlesme_bitis_tarihi);
            $interval = $d1->diff($d2);
            $months = ($interval->y * 12) + $interval->m;
            if ($interval->d > 15) $months++;
            if ($months == 0) $months = 1;

            $total_price = $price_per_month * $months;

            if ($existing_sub) {
                $db->Update("
                    UPDATE abonelikler
                    SET plan_name = ?, price_per_month = ?, total_price = ?, billing_cycle = ?, num_branches = ?, start_date = ?, end_date = ?, updated_at = NOW(), status = 'active'
                    WHERE subscription_id = ?
                ", [$plan_name, $price_per_month, $total_price, $months, $num_branches, $sozlesme_baslangic_tarihi, $sozlesme_bitis_tarihi, $existing_sub['subscription_id']]);
            } else {
                $db->Insert("
                    INSERT INTO abonelikler
                    (customer_id, plan_name, num_branches, price_per_month, total_price, billing_cycle, start_date, end_date, status, auto_renew)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', 1)
                ", [$customer_id, $plan_name, $num_branches, $price_per_month, $total_price, $months, $sozlesme_baslangic_tarihi, $sozlesme_bitis_tarihi]);
            }
        }
    }

    echo "<script>window.location.href='tenants.php';</script>";
    exit;
}
    echo "<script>window.location.href='crm/tenants.php?success=saved';</script>";
    exit;
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        <?php echo $id > 0 ? 'Kurum Düzenle: ' . htmlspecialchars($tenant['company_name']) : 'Yeni Kurum Ekle'; ?>
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="../index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted"><a href="crm/tenants.php" class="text-muted text-hover-primary">Müşteri Listesi</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark"><?php echo $id > 0 ? 'Kurum Düzenle' : 'Yeni Kurum Ekle'; ?></li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="crm/tenants.php" class="btn btn-sm fw-bold btn-secondary"><i class="ki-duotone ki-arrow-left fs-2"></i> Geri Dön</a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <form action="crm/tenant_form.php?id=<?php echo $id; ?>" method="POST" class="form">
                    <?= csrfTokenField() ?>
                    <input type="hidden" name="action" value="save_tenant">
                    <input type="hidden" name="customer_id" value="<?php echo $id; ?>">

                    <div class="d-flex flex-column flex-lg-row">
                        <!-- Sol Taraf (Statik Durumlar) -->
                        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                            <!-- Durum Kartı -->
                            <div class="card card-flush py-4">
                                <div class="card-header border-0">
                                    <div class="card-title"><h2>Kurum Durumu</h2></div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="fv-row">
                                        <label class="required form-label">Hesap Durumu</label>
                                        <select class="form-select mb-2" name="status" data-control="select2" data-hide-search="true">
                                            <option value="active" <?php echo ($tenant['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="passive" <?php echo ($tenant['status'] ?? '') === 'passive' ? 'selected' : ''; ?>>Pasif (Dondurulmuş)</option>
                                            <option value="suspended" <?php echo ($tenant['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Askıya Alınmış</option>
                                        </select>
                                    </div>
                                    <div class="fv-row mt-7">
                                        <label class="form-label">Temsilci (Admin)</label>
                                        <select class="form-select mb-2" name="account_manager_id" data-control="select2">
                                            <option value="">Seçiniz</option>
                                            <?php foreach($managers as $mgr): ?>
                                                <option value="<?php echo $mgr['admin_id']; ?>" <?php echo ($tenant['account_manager_id'] ?? '') == $mgr['admin_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($mgr['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sağ Taraf (Sekmeli İçerik) -->
                        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                            
                            <!-- Sekme Menüsü -->
                            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2">
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_tenant_general">Genel Bilgiler</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_tenant_financial">Finans & Fatura</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_tenant_subscription">Abonelik & Paket</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_tenant_tech">Teknik Kurulum</a>
                                </li>
                            </ul>

                            <!-- Sekme İçerikleri -->
                            <div class="tab-content">
                                
                                <!-- GENEL BİLGİLER -->
                                <div class="tab-pane fade show active" id="kt_tenant_general" role="tab-panel">
                                    <div class="card card-flush py-4">
                                        <div class="card-header border-0"><div class="card-title"><h2>Genel Bilgiler</h2></div></div>
                                        <div class="card-body pt-0">
                                            <div class="row mb-7">
                                                <div class="col-md-6 fv-row">
                                                    <label class="required form-label">Kurum / Firma Adı</label>
                                                    <input type="text" name="company_name" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['company_name'] ?? ''); ?>" required />
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Sektör</label>
                                                    <input type="text" name="industry" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['industry'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                            <div class="row mb-7">
                                                <div class="col-md-4 fv-row">
                                                    <label class="form-label">Yetkili Kişi</label>
                                                    <input type="text" name="contact_person" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['contact_person'] ?? ''); ?>" />
                                                </div>
                                                <div class="col-md-4 fv-row">
                                                    <label class="required form-label">Kurum E-Posta</label>
                                                    <input type="email" name="company_email" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['company_email'] ?? ''); ?>" required />
                                                </div>
                                                <div class="col-md-4 fv-row">
                                                    <label class="form-label">Kurum Telefon</label>
                                                    <input type="text" name="company_phone" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['company_phone'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- FİNANS & FATURA -->
                                <div class="tab-pane fade" id="kt_tenant_financial" role="tab-panel">
                                    <div class="card card-flush py-4">
                                        <div class="card-header border-0"><div class="card-title"><h2>Finans ve Fatura Bilgileri</h2></div></div>
                                        <div class="card-body pt-0">
                                            <div class="row mb-7">
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Vergi Dairesi</label>
                                                    <input type="text" name="tax_office" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['tax_office'] ?? ''); ?>" />
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Vergi Numarası</label>
                                                    <input type="text" name="tax_number" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['tax_number'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                            <div class="fv-row">
                                                <label class="form-label">Fatura Adresi</label>
                                                <textarea name="billing_address" class="form-control mb-2" rows="3"><?php echo htmlspecialchars($tenant['billing_address'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <!-- ABONELİK VE PAKET -->
                                <div class="tab-pane fade" id="kt_tenant_subscription" role="tab-panel">
                                    <div class="card card-flush py-4">
                                        <div class="card-header border-0"><div class="card-title"><h2>Abonelik ve Paket Bilgileri</h2></div></div>
                                        <div class="card-body pt-0">
                                            
                                            <div class="row mb-7">
                                                <div class="col-md-12 mb-5">
                                                    <label class="required form-label d-block">Sözleşme Tipi</label>
                                                    <?php $sub_type = $tenant['subscription_type'] ?? 'contract'; ?>
                                                    <div class="form-check form-check-custom form-check-solid form-check-inline">
                                                        <input class="form-check-input" type="radio" name="subscription_type" value="trial" id="sub_trial" <?php echo $sub_type == 'trial' ? 'checked' : ''; ?> />
                                                        <label class="form-check-label" for="sub_trial">Deneme Süreci (Trial)</label>
                                                    </div>
                                                    <div class="form-check form-check-custom form-check-solid form-check-inline">
                                                        <input class="form-check-input" type="radio" name="subscription_type" value="contract" id="sub_contract" <?php echo $sub_type == 'contract' ? 'checked' : ''; ?> />
                                                        <label class="form-check-label" for="sub_contract">Sözleşmeli (Aktif Abonelik)</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-7">
                                                <div class="col-md-6 fv-row">
                                                    <label class="required form-label">Müşteri Paketi</label>
                                                    <select class="form-select mb-2" name="musteri_paketi" data-control="select2" required>
                                                        <option value="">Seçiniz</option>
                                                        <option value="Başlangıç" <?php echo ($tenant['musteri_paketi'] ?? '') === 'Başlangıç' ? 'selected' : ''; ?>>Başlangıç (5 Kullanıcı) - 499 ₺/Ay</option>
                                                        <option value="Pro" <?php echo ($tenant['musteri_paketi'] ?? '') === 'Pro' ? 'selected' : ''; ?>>Pro (25 Kullanıcı) - 999 ₺/Ay</option>
                                                        <option value="Kurumsal" <?php echo ($tenant['musteri_paketi'] ?? '') === 'Kurumsal' ? 'selected' : ''; ?>>Kurumsal (Sınırsız) - 2.499 ₺/Ay</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-7" id="trial_dates_container" style="<?php echo $sub_type == 'trial' ? 'display:flex;' : 'display:none;'; ?>">
                                                <div class="col-md-12 mb-3">
                                                    <span class="badge badge-light-info fw-bold fs-7">Deneme (Trial) Dönemi</span>
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Deneme Başlangıç Tarihi</label>
                                                    <input type="date" name="trial_start_date" class="form-control mb-2" value="<?php echo !empty($tenant['trial_start_date']) ? $tenant['trial_start_date'] : ''; ?>" />
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Deneme Bitiş Tarihi</label>
                                                    <input type="date" name="trial_end_date" class="form-control mb-2" value="<?php echo !empty($tenant['trial_end_date']) ? $tenant['trial_end_date'] : ''; ?>" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-7" id="contract_dates_container" style="<?php echo $sub_type == 'contract' ? 'display:flex;' : 'display:none;'; ?>">
                                                <div class="col-md-12 mb-3">
                                                    <span class="badge badge-light-success fw-bold fs-7">Sözleşme Dönemi</span>
                                                    <div class="text-muted fs-8 mt-1">Sözleşme başlangıç ve bitiş tarihi girildiğinde, sistem otomatik olarak "abonelikler" tablosunda fatura kaydı oluşturacaktır.</div>
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Sözleşme Başlangıç Tarihi</label>
                                                    <input type="date" name="sozlesme_baslangic_tarihi" class="form-control mb-2" value="<?php echo !empty($tenant['sozlesme_baslangic_tarihi']) ? $tenant['sozlesme_baslangic_tarihi'] : ''; ?>" />
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Sözleşme Bitiş Tarihi</label>
                                                    <input type="date" name="sozlesme_bitis_tarihi" class="form-control mb-2" value="<?php echo !empty($tenant['sozlesme_bitis_tarihi']) ? $tenant['sozlesme_bitis_tarihi'] : ''; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <!-- TEKNİK KURULUM -->
                                <div class="tab-pane fade" id="kt_tenant_tech" role="tab-panel">
                                    <div class="card card-flush py-4">
                                        <div class="card-header border-0"><div class="card-title"><h2>Teknik Bilgiler</h2></div></div>
                                        <div class="card-body pt-0">
                                            <div class="fv-row mb-7">
                                                <label class="form-label">Veritabanı Adı</label>
                                                <?php if($id > 0): ?>
                                                    <input type="text" name="database_name" class="form-control mb-2 bg-secondary" value="<?php echo htmlspecialchars($tenant['database_name'] ?? ''); ?>" readonly />
                                                <?php else: ?>
                                                    <input type="text" class="form-control mb-2 bg-secondary" value="Otomatik Atanacak (örn: prolyn_k7)" readonly disabled />
                                                <?php endif; ?>
                                                <div class="text-muted fs-7">Veritabanı adı sistem tarafından otomatik olarak yönetilmektedir.</div>
                                            </div>
                                            <div class="fv-row mb-7">
                                                <label class="form-label">Kurum Kodu (Alias)</label>
                                                <input type="text" name="tenant_alias" class="form-control mb-2" placeholder="Örn: sirketadi" value="<?php echo htmlspecialchars($tenant['tenant_alias'] ?? ''); ?>" />
                                                <div class="text-muted fs-7">Uygulama içinde yönlendirme veya tanımlama amaçlı kısa kod.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="d-flex justify-content-end mt-5">
                                <a href="crm/tenants.php" class="btn btn-light me-5">İptal</a>
                                <button type="submit" class="btn btn-primary">
                                    <span class="indicator-label"><i class="ki-duotone ki-check fs-2"></i> Kurumu Kaydet</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioTrial = document.getElementById('sub_trial');
    const radioContract = document.getElementById('sub_contract');
    const trialContainer = document.getElementById('trial_dates_container');
    const contractContainer = document.getElementById('contract_dates_container');

    function toggleDates() {
        if (radioTrial.checked) {
            trialContainer.style.display = 'flex';
            contractContainer.style.display = 'none';
        } else {
            trialContainer.style.display = 'none';
            contractContainer.style.display = 'flex';
        }
    }

    if (radioTrial && radioContract) {
        radioTrial.addEventListener('change', toggleDates);
        radioContract.addEventListener('change', toggleDates);
    }
});
</script>

<?php require_once '../config/footer.php'; ?>
