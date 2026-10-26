<?php
/**
 * Prolyn Admin Panel - Tenant Add/Edit
 * Prolyn/p/crm/tenant_form.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once '../config/sidebar.php';
require_once __DIR__ . '/../functions/provision.php';

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

// Fetch Cities (İller) from default DB
$iller = [];
try {
    $iller = $db->getRowsAssoc("SELECT * FROM prolyn_default.iller ORDER BY sehiradi ASC");
} catch(Exception $e) {}

// Fetch Districts (İlçeler) if city_id is set
$ilceler = [];
if (!empty($tenant['city_id'])) {
    try {
        $ilceler = $db->getRowsAssoc("SELECT * FROM prolyn_default.ilceler WHERE iller_id = ? ORDER BY ilceadi ASC", [$tenant['city_id']]);
    } catch(Exception $e) {}
}

// Form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_tenant') {
    $customer_id    = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;

    $toUpperText = static function ($value) {
        $value = trim((string) $value);
        return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
    };

    // Genel
    $company_name   = $toUpperText($_POST['company_name']);
    $official_name  = $toUpperText($_POST['official_name'] ?? '');
    $company_email  = trim($_POST['company_email']);
    $company_phone  = trim($_POST['company_phone']);
    $industry       = $toUpperText($_POST['industry']);
    $contact_person = $toUpperText($_POST['contact_person'] ?? '');

    // Finans
    $tax_office = $toUpperText($_POST['tax_office'] ?? '');
    $tax_number = trim($_POST['tax_number'] ?? '');
    $billing_address = $toUpperText($_POST['billing_address'] ?? '');
    $city_id = !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
    $district_id = !empty($_POST['district_id']) ? (int)$_POST['district_id'] : null;


    // Abonelik
    $subscription_type = $_POST['subscription_type'] ?? 'contract';
    $musteri_paketi = trim($_POST['musteri_paketi'] ?? '');

    function firma_vno_digits($value) {
        return preg_replace('/\D+/', '', (string) $value);
    }
    
    function firma_vno_is_valid($value) {
        $value = firma_vno_digits($value);
    
        if ($value === '' || strlen($value) < 10 || strlen($value) > 11) {
            return false;
        }
    
        if (strlen($value) === 10) {
            $digits = array_map('intval', str_split($value));
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $v = ($digits[$i] + (9 - $i)) % 10;
                $vv = ($v * pow(2, 9 - $i)) % 9;
                if ($v !== 0 && $vv === 0) {
                    $vv = 9;
                }
                $sum += $vv;
            }
            $checkDigit = ($sum % 10 === 0) ? 0 : (10 - ($sum % 10));
            return $checkDigit === $digits[9];
        }
    
        if ($value[0] === '0') {
            return false;
        }
    
        $digits = array_map('intval', str_split($value));
        $sumFirst10 = array_sum(array_slice($digits, 0, 10));
    
        return ($sumFirst10 % 10) === $digits[10];
    }
    
    if (!empty($tax_number) && !firma_vno_is_valid($tax_number)) {
        $_SESSION['swal_error'] = "Geçersiz Vergi No veya TC Kimlik No girişi yaptınız.";
        if ($customer_id > 0) {
            header("Location: tenant_form.php?id=$customer_id");
        } else {
            header("Location: tenant_form.php");
        }
        exit;
    }
    
    // Eğer deneme süreciyse standart paketi Başlangıç olarak zorla
    if ($subscription_type == 'trial') {
        $musteri_paketi = 'Başlangıç';
    }

    
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
    $tenant_alias = $toUpperText($_POST['tenant_alias'] ?? '');
    $status         = $_POST['status'] ?? 'active';
    $account_manager_id = !empty($_POST['account_manager_id']) ? (int)$_POST['account_manager_id'] : null;

    if (empty($account_manager_id)) {
        $_SESSION['swal_error'] = "Lütfen bir Temsilci (Admin) seçiniz.";
        if ($customer_id > 0) {
            header("Location: tenant_form.php?id=$customer_id");
        } else {
            header("Location: tenant_form.php");
        }
        exit;
    }

    /**
     * musteri_paketi -> paket_ozellikleri.plan_name
     * Esnek eşleştirme: büyük/küçük harf, boşluk ve "... Paketi" eki önemsizdir.
     * Birebir eşleşme aranırsa 'Pro Paketi' gibi bir değer sessizce 'basic'e
     * düşüyor ve müşteri satın aldığı modülleri göremiyordu.
     */
    if (!function_exists('prolyn_plan_adi_coz')) {
        function prolyn_plan_adi_coz(string $paket): ?string {
            $t = mb_strtolower(trim($paket), 'UTF-8');
            if ($t === '') { return null; }
            $eslesme = [
                'basic'      => ['basic', 'başlangıç', 'baslangic', 'başlangic', 'baslangıç'],
                'pro'        => ['pro', 'profesyonel', 'profosyonel'],
                'enterprise' => ['enterprise', 'kurumsal'],
            ];
            foreach ($eslesme as $plan => $adaylar) {
                foreach ($adaylar as $a) {
                    if (mb_strpos($t, $a) !== false) { return $plan; }
                }
            }
            return null;
        }
    }
    $plan_name = prolyn_plan_adi_coz($musteri_paketi) ?? 'basic';

    /**
     * Paket -> modül eşlemesi.
     *
     * TEK KAYNAK: prolyn_master.paket_ozellikleri tablosu (Panel > Paketler
     * ekranından yönetilir). Burada sabit bir harita tutulursa panelden yapılan
     * her değişiklik, müşteri kaydı her güncellendiğinde sessizce geri alınır —
     * "Yetkisiz Erişim" hatalarının kaynağı buydu.
     *
     * Aşağıdaki dizi yalnızca ACİL DURUM yedeğidir: paket_ozellikleri satırı
     * yoksa veya boşsa devreye girer.
     */
    $fallback_map = [
        'basic'      => ["core", "customer", "appointment", "reports", "current_account", "contract"],
        'pro'        => ["core", "customer", "appointment", "reports", "current_account", "contract", "commercial", "inventory", "accounting", "bank", "statistics", "invoice"],
        'enterprise' => ["core", "customer", "appointment", "reports", "current_account", "contract", "commercial", "inventory", "accounting", "bank", "statistics", "invoice", "planning"]
    ];

    $allowed_modules = null;
    try {
        $planRow = $db->getRowAssoc(
            "SELECT allowed_modules FROM paket_ozellikleri WHERE plan_name = ? LIMIT 1",
            [$plan_name]
        );
        if ($planRow && !empty($planRow['allowed_modules'])) {
            $decoded = json_decode($planRow['allowed_modules'], true);
            if (is_array($decoded) && count($decoded) > 0) {
                $allowed_modules = json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE);
            }
        }
    } catch (Throwable $e) {
        // paket_ozellikleri okunamadi -> yedek haritaya dusulur
    }

    if ($allowed_modules === null) {
        $allowed_modules = json_encode($fallback_map[$plan_name] ?? $fallback_map['basic'], JSON_UNESCAPED_UNICODE);
    }

    // Veritabanı adı belirleme
    $database_name = '';
    $is_new = ($customer_id == 0);
    
    if ($is_new) {
        $stmt_dbs = $db->getRowsAssoc("SHOW DATABASES");
        $max_k = 0;
        foreach ($stmt_dbs as $db_row) {
            $dbName = array_values($db_row)[0];
            if (preg_match('/^prolyn_k(\d+)$/i', $dbName, $matches)) {
                $num = (int)$matches[1];
                if ($num > $max_k) $max_k = $num;
            }
        }
        $stmt_codes = $db->getRowsAssoc("SELECT customer_code FROM kurumlar WHERE customer_code LIKE 'k%'");
        foreach ($stmt_codes as $code_row) {
            $code = $code_row['customer_code'];
            if (preg_match('/^k(\d+)$/i', $code, $matches)) {
                $num = (int)$matches[1];
                if ($num > $max_k) $max_k = $num;
            }
        }
        $next_k = $max_k + 1;
        $database_name = 'prolyn_k' . $next_k;
    } else {
        $existing = $db->getRowAssoc("SELECT database_name FROM kurumlar WHERE customer_id = ?", [$customer_id]);
        $database_name = $existing['database_name'] ?? '';
    }

    if (!$is_new) {
        $db->Update("
            UPDATE kurumlar
            SET company_name=?, official_name=?, company_email=?, company_phone=?, industry=?, status=?, subscription_type=?, trial_start_date=?, trial_end_date=?,
                musteri_paketi=?, contact_person=?, tax_office=?, tax_number=?, billing_address=?, city_id=?, district_id=?, account_manager_id=?, tenant_alias=?, sozlesme_baslangic_tarihi=?, sozlesme_bitis_tarihi=?, allowed_modules=?, 
                contact_name=?, contact_email=?, contact_phone=?, updated_at=NOW()
            WHERE customer_id=?
        ", [$company_name, $official_name, $company_email, $company_phone, $industry, $status, $subscription_type, $trial_start_date, $trial_end_date,
            $musteri_paketi, $contact_person, $tax_office, $tax_number, $billing_address, $city_id, $district_id, $account_manager_id, $tenant_alias, $sozlesme_baslangic_tarihi, $sozlesme_bitis_tarihi, $allowed_modules, 
            $contact_person, $company_email, $company_phone, $customer_id]);
    } else {
        $customer_id = $db->Insert("
            INSERT INTO kurumlar
            (company_name, official_name, company_email, company_phone, database_name, database_password, industry, status, subscription_type, trial_start_date, trial_end_date,
             musteri_paketi, contact_person, tax_office, tax_number, billing_address, city_id, district_id, account_manager_id, tenant_alias, sozlesme_baslangic_tarihi, sozlesme_bitis_tarihi, allowed_modules, contact_name, contact_email, contact_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [$company_name, $official_name, $company_email, $company_phone, '', '', $industry, $status, $subscription_type, $trial_start_date, $trial_end_date,
            $musteri_paketi, $contact_person, $tax_office, $tax_number, $billing_address, $city_id, $district_id, $account_manager_id, $tenant_alias, $sozlesme_baslangic_tarihi, $sozlesme_bitis_tarihi, $allowed_modules, $contact_person, $company_email, $company_phone]);

        // Yeni oluşturulan müşterinin müşteri kodunu güncelle (örn: k27)
        $db->Update("UPDATE kurumlar SET customer_code = CONCAT('k', customer_id) WHERE customer_id = ?", [$customer_id]);


        // VERITABANI OTOMATIK KURULUM (PROVISIONING) - ortak güvenli provisioner
        // DB adı customer_id'den türetilir (yarış durumu yok): prolyn_k{customer_id}
        $prov = provision_tenant([
            'customer_id'     => (int)$customer_id,
            'company_name'    => $company_name,
            'official_name'   => $official_name,
            'tax_office'      => $tax_office,
            'tax_number'      => $tax_number,
            'city_id'         => (int)($city_id ?? 0),
            'district_id'     => (int)($district_id ?? 0),
            'billing_address' => $billing_address,
            'company_phone'   => $company_phone,
            'admin_name'      => $contact_person,
            'admin_email'     => $company_email,
            'admin_password'  => '123456', // manuel eklemede varsayılan; müşteri ilk girişte değiştirmeli
        ]);

        if ($prov['success']) {
            $database_name = $prov['database_name'];
            // Her zaman: DB adı + parola
            $db->Update(
                "UPDATE kurumlar SET database_name = ?, database_password = ? WHERE customer_id = ?",
                [$prov['database_name'], $prov['database_password'], $customer_id]
            );
            // database_user kolonu yoksa sessizce geçer (Database sınıfı hatayı yutar).
            // Kolon için: migrate_tenant_users.php veya ALTER TABLE kurumlar ADD database_user VARCHAR(64).
            $db->Update(
                "UPDATE kurumlar SET database_user = ? WHERE customer_id = ?",
                [$prov['database_user'], $customer_id]
            );
        } else {
            error_log("DB Provisioning failed for customer_id={$customer_id}: " . ($prov['error'] ?? 'bilinmeyen'));
            $_SESSION['swal_error'] = "Kurum kaydı oluşturuldu ancak veritabanı kurulumu başarısız oldu. Lütfen tekrar deneyin veya sistem yöneticisine başvurun.";
        }
    }

    // Fiyat ve şube hakkını paketten dinamik al (TEK KAYNAK: paket_ozellikleri)
    $paket_fiyatlari = $db->getRowsAssoc("SELECT * FROM paket_ozellikleri");
    $price_map    = [];
    $branches_map = [];
    $yillik_map   = [];
    foreach ($paket_fiyatlari as $pf) {
        $price_map[$pf['plan_name']]    = (float)$pf['price'];
        $branches_map[$pf['plan_name']] = array_key_exists('max_branches', $pf) ? (int)$pf['max_branches'] : 0;
        $yillik_map[$pf['plan_name']]   = array_key_exists('yillik_indirim', $pf) ? (int)$pf['yillik_indirim'] : 0;
    }

    $price_per_month = $price_map[$plan_name] ?? 0;
    // 0 = sınırsız. Eskiden burada sabit ['basic'=>1,'pro'=>2,'enterprise'=>5]
    // haritası vardı ve panelde girilen şube limitiyle çelişiyordu.
    $num_branches    = $branches_map[$plan_name] ?? 0;

    $existing_sub = $db->getRowAssoc("SELECT subscription_id FROM abonelikler WHERE customer_id = ? ORDER BY subscription_id DESC LIMIT 1", [$customer_id]);

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

            /* ── YILLIK ÖDEME İNDİRİMİ ─────────────────────────────────
               12 ay ve üzeri sözleşmelerde paketin `yillik_indirim`
               yüzdesi toplam tutara uygulanır (varsayılan %20).
               `price_per_month` liste fiyatı olarak kalır; indirim
               `total_price` üzerinde görünür.                          */
            $yillik_indirim_yuzde = $months >= 12 ? (int)($yillik_map[$plan_name] ?? 0) : 0;
            if ($yillik_indirim_yuzde < 0)   { $yillik_indirim_yuzde = 0; }
            if ($yillik_indirim_yuzde > 100) { $yillik_indirim_yuzde = 100; }

            $liste_tutari = $price_per_month * $months;
            $total_price  = round($liste_tutari * (1 - $yillik_indirim_yuzde / 100), 2);

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

    echo "<script>window.location.href='crm/tenant_form.php?verify=" . $customer_id . "';</script>";
    exit;
}

if (isset($_GET['verify'])) {
    $verify_id = (int)$_GET['verify'];
    $dbFirma = $db->getRow("
        SELECT k.*, a.plan_name, a.price_per_month, a.total_price, a.num_branches, a.billing_cycle, a.start_date, a.end_date
        FROM kurumlar k
        LEFT JOIN abonelikler a ON k.customer_id = a.customer_id
        WHERE k.customer_id = ? LIMIT 1
    ", [$verify_id]);

    if ($dbFirma) {
        $iller_row = [];
        $ilceler_row = [];
        if (!empty($dbFirma->city_id)) {
            $iller_row = $db->getRowAssoc("SELECT sehiradi FROM prolyn_default.iller WHERE iller_id = ?", [$dbFirma->city_id]);
        }
        if (!empty($dbFirma->district_id)) {
            $ilceler_row = $db->getRowAssoc("SELECT ilceadi FROM prolyn_default.ilceler WHERE ilce_id = ?", [$dbFirma->district_id]);
        }
        $city_name = $iller_row['sehiradi'] ?? 'Belirtilmemiş';
        $district_name = $ilceler_row['ilceadi'] ?? 'Belirtilmemiş';
        ?>
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
            <div class="d-flex flex-column flex-column-fluid">
                <!-- Toolbar -->
                <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                                <i class="fas fa-check-circle text-success fs-2x me-2"></i> Kurum Başarıyla Oluşturuldu
                            </h1>
                        </div>
                        <div class="d-flex align-items-center gap-2 gap-lg-3">
                            <a href="crm/tenants.php" class="btn btn-sm fw-bold btn-primary">Müşteri Listesine Dön</a>
                        </div>
                    </div>
                </div>
                <!-- Content -->
                <div id="kt_app_content" class="app-content flex-column-fluid">
                    <div id="kt_app_content_container" class="app-container container-xxl">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h3 class="card-title">📊 <?= htmlspecialchars($dbFirma->company_name) ?> Özeti</h3>
                            </div>
                            <div class="card-body">
                                <div class="row mb-7">
                                    <div class="col-lg-6">
                                        <h4 class="text-gray-800 fw-bold mb-4">🏢 Kurum Bilgileri</h4>
                                        <table class="table table-row-dashed table-row-gray-300 gy-3">
                                            <tr><td class="fw-bold text-gray-600">Kurum Adı:</td><td><?= htmlspecialchars($dbFirma->company_name) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Yetkili Kişi:</td><td><?= htmlspecialchars($dbFirma->contact_person) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">E-Posta:</td><td><?= htmlspecialchars($dbFirma->company_email) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Telefon:</td><td><?= htmlspecialchars($dbFirma->company_phone) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Vergi D. / No:</td><td><?= htmlspecialchars($dbFirma->tax_office . ' / ' . $dbFirma->tax_number) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">İl / İlçe:</td><td><?= htmlspecialchars($city_name . ' / ' . $district_name) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Fatura Adresi:</td><td><?= htmlspecialchars($dbFirma->billing_address) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Veritabanı:</td><td><span class="badge badge-light-primary fs-6"><?= htmlspecialchars($dbFirma->database_name) ?></span></td></tr>
                                        </table>
                                    </div>
                                    <div class="col-lg-6">
                                        <h4 class="text-gray-800 fw-bold mb-4">💳 Abonelik Bilgileri</h4>
                                        <?php if($dbFirma->plan_name): ?>
                                        <table class="table table-row-dashed table-row-gray-300 gy-3">
                                            <tr><td class="fw-bold text-gray-600">Plan Adı:</td><td><span class="badge badge-light-success fs-6"><?= htmlspecialchars($dbFirma->plan_name) ?></span></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Şube Sayısı:</td><td><?= htmlspecialchars($dbFirma->num_branches) ?></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Periyot:</td><td><?= htmlspecialchars($dbFirma->billing_cycle) ?> Ay</td></tr>
                                            <tr><td class="fw-bold text-gray-600">Aylık Tutar:</td><td><?= number_format((float)$dbFirma->price_per_month, 2, ',', '.') ?> TL</td></tr>
                                            <?php
                                                $ay        = (int)$dbFirma->billing_cycle;
                                                $listeTut  = (float)$dbFirma->price_per_month * $ay;
                                                $odenecek  = (float)$dbFirma->total_price;
                                                $indirimTL = $listeTut - $odenecek;
                                                $indirimYuzde = $listeTut > 0 ? round($indirimTL / $listeTut * 100) : 0;
                                            ?>
                                            <?php if ($indirimTL > 0.009): ?>
                                            <tr><td class="fw-bold text-gray-600">Liste Tutarı:</td><td class="text-muted"><s><?= number_format($listeTut, 2, ',', '.') ?> TL</s></td></tr>
                                            <tr><td class="fw-bold text-gray-600">Yıllık Ödeme İndirimi:</td><td><span class="badge badge-light-success fs-6">%<?= $indirimYuzde ?> · -<?= number_format($indirimTL, 2, ',', '.') ?> TL</span></td></tr>
                                            <?php endif; ?>
                                            <tr><td class="fw-bold text-gray-600">Toplam Tutar:</td><td class="fw-bold text-dark"><?= number_format($odenecek, 2, ',', '.') ?> TL</td></tr>
                                            <tr><td class="fw-bold text-gray-600">Başlangıç - Bitiş:</td><td><?= date('d.m.Y', strtotime($dbFirma->start_date)) ?> - <?= date('d.m.Y', strtotime($dbFirma->end_date)) ?></td></tr>
                                        </table>
                                        <?php else: ?>
                                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                            <i class="ki-duotone ki-information fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            <div class="d-flex flex-stack flex-grow-1">
                                                <div class="fw-semibold">
                                                    <h4 class="text-gray-900 fw-bold">Abonelik Bulunamadı</h4>
                                                    <div class="fs-6 text-gray-700">Bu kurum için henüz bir abonelik tanımlanmamış.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        require_once '../config/footer.php';
        exit;
    }
}

/* ── Paket seçenekleri: etiketler paket_ozellikleri'nden üretilir ──────
   Eskiden bu açılır liste sabit metinliydi ("Pro (25 Kullanıcı) - 999 ₺/Ay")
   ve Panel > Paketler'de girilen gerçek fiyat/limitle çelişiyordu.        */
$paketSecenekleri = [];
try {
    $paketSatirlari = $db->getRowsAssoc("SELECT * FROM paket_ozellikleri ORDER BY price ASC");
    foreach ($paketSatirlari as $ps) {
        $gorunenAd = trim((string)($ps['plan_display_name'] ?? '')) !== ''
            ? (string)$ps['plan_display_name']
            : (string)$ps['plan_name'];

        $kul  = array_key_exists('max_total_users', $ps) ? (int)$ps['max_total_users'] : 0;
        $sube = array_key_exists('max_branches', $ps) ? (int)$ps['max_branches'] : 0;
        $fiyat = (float)($ps['price'] ?? 0);

        $parcalar = [];
        $parcalar[] = $kul  > 0 ? $kul . ' Kullanıcı'  : 'Sınırsız Kullanıcı';
        $parcalar[] = $sube > 0 ? $sube . ' Şube'      : 'Sınırsız Şube';

        $paketSecenekleri[] = [
            'deger'  => $gorunenAd,
            'etiket' => $gorunenAd . ' (' . implode(', ', $parcalar) . ') - '
                        . number_format($fiyat, 2, ',', '.') . ' ₺/Ay',
        ];
    }
} catch (Throwable $e) {
    // paket_ozellikleri okunamazsa açılır liste boş kalır; aşağıda yedek kullanılır
}
if (empty($paketSecenekleri)) {
    $paketSecenekleri = [
        ['deger' => 'Başlangıç', 'etiket' => 'Başlangıç'],
        ['deger' => 'Pro',       'etiket' => 'Pro'],
        ['deger' => 'Kurumsal',  'etiket' => 'Kurumsal'],
    ];
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
                                    <div class="fv-row mb-7">
                                        <label class="required form-label">Temsilci (Admin)</label>
                                        <select class="form-select mb-2" name="account_manager_id" data-control="select2" required>
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
                                                    <input type="text" name="company_name" class="form-control mb-2 text-uppercase" value="<?php echo htmlspecialchars($tenant['company_name'] ?? ''); ?>" required />
                                                </div>
                                                <div class="col-md-6 fv-row mt-3 mt-md-0">
                                                    <label class="required form-label">Resmi Ticaret Unvanı</label>
                                                    <input type="text" name="official_name" class="form-control mb-2 text-uppercase" value="<?php echo htmlspecialchars($tenant['official_name'] ?? ''); ?>" required />
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Sektör</label>
                                                    <input type="text" name="industry" class="form-control mb-2 text-uppercase" value="<?php echo htmlspecialchars($tenant['industry'] ?? ''); ?>" />
                                                </div>
                                            </div>
                                            <div class="row mb-7">
                                                <div class="col-md-4 fv-row">
                                                    <label class="form-label">Yetkili Kişi</label>
                                                    <input type="text" name="contact_person" class="form-control mb-2 text-uppercase" value="<?php echo htmlspecialchars($tenant['contact_person'] ?? ''); ?>" />
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
                                                <div class="col-md-6 mb-4">
                                                    <label class="form-label text-gray-700 fw-bold">Vergi Dairesi</label>
                                                    <input type="text" name="tax_office" class="form-control mb-2 text-uppercase" value="<?php echo htmlspecialchars($tenant['tax_office'] ?? ''); ?>" required />
                                                </div>
                                                <div class="col-md-6 mb-4">
                                                    <label class="form-label text-gray-700 fw-bold">Vergi No / TC Kimlik No</label>
                                                    <input type="text" name="tax_number" class="form-control mb-2" value="<?php echo htmlspecialchars($tenant['tax_number'] ?? ''); ?>" maxlength="11" required />
                                                </div>
                                            </div>
                                            <div class="row mb-7">
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">İl</label>
                                                    <select class="form-select mb-2" name="city_id" id="city_id" data-control="select2" required>
                                                        <option value="">İl Seçiniz</option>
                                                        <?php foreach ($iller as $il): ?>
                                                            <option value="<?php echo $il['iller_id']; ?>" <?php echo ($tenant['city_id'] ?? '') == $il['iller_id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($il['sehiradi']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">İlçe</label>
                                                    <select class="form-select mb-2" name="district_id" id="district_id" data-control="select2" required>
                                                        <option value="">Önce İl Seçiniz</option>
                                                        <?php foreach ($ilceler as $ilce): ?>
                                                            <option value="<?php echo $ilce['ilce_id']; ?>" <?php echo ($tenant['district_id'] ?? '') == $ilce['ilce_id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($ilce['ilceadi']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="fv-row">
                                                <label class="form-label">Fatura Adresi</label>
                                                <textarea name="billing_address" class="form-control mb-2" rows="3" required><?php echo htmlspecialchars($tenant['billing_address'] ?? ''); ?></textarea>
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
                                                <div class="col-md-6 fv-row" id="contract_package_wrapper" style="<?php echo $sub_type == 'contract' ? 'display:block;' : 'display:none;'; ?>">
                                                    <label class="required form-label">Müşteri Paketi</label>
                                                    <select class="form-select mb-2" name="musteri_paketi_contract" id="musteri_paketi_contract" data-control="select2">
                                                        <option value="">Seçiniz</option>
                                                        <?php foreach ($paketSecenekleri as $ps): ?>
                                                        <option value="<?= htmlspecialchars($ps['deger'], ENT_QUOTES, 'UTF-8') ?>" <?php echo ($tenant['musteri_paketi'] ?? '') === $ps['deger'] ? 'selected' : ''; ?>><?= htmlspecialchars($ps['etiket'], ENT_QUOTES, 'UTF-8') ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="text-muted fs-7 mt-1">Fiyat ve limitler Panel &gt; Paketler ekranından gelir.</div>
                                                </div>
                                                <div class="col-md-6 fv-row" id="trial_package_wrapper" style="<?php echo $sub_type == 'trial' ? 'display:block;' : 'display:none;'; ?>">
                                                    <label class="required form-label">Müşteri Paketi</label>
                                                    <select class="form-select mb-2 bg-secondary" name="musteri_paketi_trial" id="musteri_paketi_trial" readonly>
                                                        <option value="Başlangıç" selected>Başlangıç (5 Kullanıcı) - Ücretsiz Deneme</option>
                                                    </select>
                                                    <div class="text-muted fs-7 mt-1">Deneme sürecinde varsayılan olarak Başlangıç paketi atanır.</div>
                                                </div>
                                                
                                                <input type="hidden" name="musteri_paketi" id="musteri_paketi_hidden" value="<?php echo htmlspecialchars($tenant['musteri_paketi'] ?? 'Başlangıç'); ?>" />
                                            </div>

                                            
                                            <div class="row mb-7" id="trial_dates_container" style="<?php echo $sub_type == 'trial' ? 'display:flex;' : 'display:none;'; ?>">
                                                <div class="col-md-12 mb-3">
                                                    <span class="badge badge-light-info fw-bold fs-7">Deneme (Trial) Dönemi</span>
                                                </div>
                                                <?php
                                                    $default_trial_start = date('Y-m-d');
                                                    $default_trial_end = date('Y-m-d', strtotime('+30 days'));
                                                ?>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Deneme Başlangıç Tarihi</label>
                                                    <input type="date" name="trial_start_date" class="form-control mb-2" value="<?php echo !empty($tenant['trial_start_date']) ? $tenant['trial_start_date'] : $default_trial_start; ?>" />
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Deneme Bitiş Tarihi</label>
                                                    <input type="date" name="trial_end_date" class="form-control mb-2" value="<?php echo !empty($tenant['trial_end_date']) ? $tenant['trial_end_date'] : $default_trial_end; ?>" />
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-7" id="contract_dates_container" style="<?php echo $sub_type == 'contract' ? 'display:flex;' : 'display:none;'; ?>">
                                                <div class="col-md-12 mb-3">
                                                    <span class="badge badge-light-success fw-bold fs-7">Sözleşme Dönemi</span>
                                                    <div class="text-muted fs-8 mt-1">Sözleşme başlangıç ve bitiş tarihi girildiğinde, sistem otomatik olarak "abonelikler" tablosunda fatura kaydı oluşturacaktır.</div>
                                                </div>
                                                <?php
                                                    $default_contract_start = date('Y-m-d');
                                                    $default_contract_end = date('Y-m-d', strtotime('+1 year'));
                                                ?>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Sözleşme Başlangıç Tarihi</label>
                                                    <input type="date" name="sozlesme_baslangic_tarihi" class="form-control mb-2" value="<?php echo !empty($tenant['sozlesme_baslangic_tarihi']) ? $tenant['sozlesme_baslangic_tarihi'] : $default_contract_start; ?>" />
                                                </div>
                                                <div class="col-md-6 fv-row">
                                                    <label class="form-label">Sözleşme Bitiş Tarihi</label>
                                                    <input type="date" name="sozlesme_bitis_tarihi" class="form-control mb-2" value="<?php echo !empty($tenant['sozlesme_bitis_tarihi']) ? $tenant['sozlesme_bitis_tarihi'] : $default_contract_end; ?>" />
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
                                                <input type="text" name="tenant_alias" class="form-control mb-2" placeholder="Örn: sirketadi" value="<?php echo htmlspecialchars($tenant['tenant_alias'] ?? ''); ?>" required />
                                                <div class="text-muted fs-7">Uygulama içinde yönlendirme veya tanımlama amaçlı kısa kod.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="d-flex justify-content-end mt-5">
                                <a href="crm/tenants.php" class="btn btn-light me-5">İptal</a>
                                <button type="submit" id="tenant_submit_btn" class="btn btn-primary">
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
    
    const trialPackageWrapper = document.getElementById('trial_package_wrapper');
    const contractPackageWrapper = document.getElementById('contract_package_wrapper');
    const hiddenPackageInput = document.getElementById('musteri_paketi_hidden');
    const contractSelect = document.getElementById('musteri_paketi_contract');

    function toggleDates() {
        const trialStartInput = document.querySelector('input[name="trial_start_date"]');
        const trialEndInput = document.querySelector('input[name="trial_end_date"]');
        const contractStartInput = document.querySelector('input[name="sozlesme_baslangic_tarihi"]');
        const contractEndInput = document.querySelector('input[name="sozlesme_bitis_tarihi"]');
        
        if (radioTrial.checked) {
            // Show Trial UI
            trialContainer.style.display = 'flex';
            contractContainer.style.display = 'none';
            trialPackageWrapper.style.display = 'block';
            contractPackageWrapper.style.display = 'none';
            
            // Set hidden input
            hiddenPackageInput.value = 'Başlangıç';
            
            // Set required
            trialStartInput.setAttribute('required', 'required');
            trialEndInput.setAttribute('required', 'required');
            contractStartInput.removeAttribute('required');
            contractEndInput.removeAttribute('required');
            contractSelect.removeAttribute('required');
            
            // Auto set dates if empty
            if (!trialStartInput.value) {
                const today = new Date();
                // toLocaleDateString('en-CA') gives YYYY-MM-DD in local time
                const startDateStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                trialStartInput.value = startDateStr;
                
                const nextMonth = new Date(today);
                nextMonth.setDate(nextMonth.getDate() + 30);
                const endDateStr = nextMonth.getFullYear() + '-' + String(nextMonth.getMonth() + 1).padStart(2, '0') + '-' + String(nextMonth.getDate()).padStart(2, '0');
                trialEndInput.value = endDateStr;
            }


        } else {
            // Show Contract UI
            trialContainer.style.display = 'none';
            contractContainer.style.display = 'flex';
            trialPackageWrapper.style.display = 'none';
            contractPackageWrapper.style.display = 'block';
            
            // Set required
            trialStartInput.removeAttribute('required');
            trialEndInput.removeAttribute('required');
            contractStartInput.setAttribute('required', 'required');
            contractEndInput.setAttribute('required', 'required');
            contractSelect.setAttribute('required', 'required');
            
            // Set hidden input to whatever the contract select has
            hiddenPackageInput.value = contractSelect.value;
        }
    }

    if (radioTrial && radioContract) {
        radioTrial.addEventListener('change', toggleDates);
        radioContract.addEventListener('change', toggleDates);
    }
    
    if (contractSelect) {
        if (typeof $ !== 'undefined') {
            $(contractSelect).on('change', function() {
                if (radioContract.checked) {
                    hiddenPackageInput.value = this.value;
                }
            });
        } else {
            contractSelect.addEventListener('change', function() {
                if (radioContract.checked) {
                    hiddenPackageInput.value = this.value;
                }
            });
        }
    }
    
    // Initialize dates on load if trial is selected and it's a new record
    if (radioTrial && radioTrial.checked) {
        toggleDates();
    }
    
    // City to District AJAX
    const citySelect = document.getElementById('city_id');
    const districtSelect = document.getElementById('district_id');
    if (citySelect && districtSelect) {
        $(citySelect).on('change', function() {
            const cityId = this.value;
            
            // Clear current options
            districtSelect.innerHTML = '<option value="">Önce İl Seçiniz</option>';
            $(districtSelect).trigger('change.select2'); // if using select2
            
            if (cityId) {
                fetch('crm/ajax_get_ilceler.php?city_id=' + cityId)
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            districtSelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
                            res.data.forEach(function(item) {
                                const option = document.createElement('option');
                                option.value = item.ilce_id;
                                option.textContent = item.ilceadi;
                                districtSelect.appendChild(option);
                            });
                            $(districtSelect).trigger('change.select2');
                        }
                    })
                    .catch(err => console.error(err));
            }
        });
    }
    // Form validation to enable/disable submit button
    const form = document.querySelector('form.form');
    const submitBtn = document.getElementById('tenant_submit_btn');
    
    function validateForm() {
        if (form && submitBtn) {
            submitBtn.disabled = !form.checkValidity();
        }
    }

    if (form) {
        // Native inputs
        form.addEventListener('input', validateForm);
        form.addEventListener('change', validateForm);
        
        // Select2 triggers
        if (typeof $ !== 'undefined') {
            $('select[data-control="select2"]').on('change', validateForm);
        }
        
        // Run once on load
        validateForm();
        
        // Also run when radio buttons for trial/contract change since required attributes change
        if (radioTrial && radioContract) {
            radioTrial.addEventListener('change', validateForm);
            radioContract.addEventListener('change', validateForm);
        }
    }
});
</script>

<?php require_once '../config/footer.php'; ?>
