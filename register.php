<?php
require_once 'db.php';

$message = '';
$messageType = '';
$successData = null;

$selectedPlan = $_GET['plan'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name  = trim($_POST['company_name'] ?? '');
    $official_name = trim($_POST['official_name'] ?? '');
    $contact_name  = trim($_POST['contact_name'] ?? '');
    $company_email = trim($_POST['company_email'] ?? '');
    $company_phone = trim($_POST['company_phone'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    
    $tax_office = trim($_POST['tax_office'] ?? '');
    $tax_number = trim($_POST['tax_number'] ?? '');
    $billing_address = trim($_POST['billing_address'] ?? '');
    
    $city_id       = (int)($_POST['city_id'] ?? 0);
    $district_id   = (int)($_POST['district_id'] ?? 0);

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

    if (empty($company_name) || empty($official_name) || empty($contact_name) || empty($company_email) || empty($admin_password) || empty($tax_number) || empty($tax_office) || empty($billing_address) || $city_id <= 0 || $district_id <= 0) {
        $message = "Lütfen tüm zorunlu alanları doldurunuz.";
        $messageType = "danger";
    } elseif (!firma_vno_is_valid($tax_number)) {
        $message = "Geçersiz Vergi No veya TC Kimlik No girişi yaptınız.";
        $messageType = "danger";
    } else {
        try {
            // E-posta adresi kullanımda mı kontrol et
            $checkEmail = $pdo->prepare("SELECT customer_id FROM kurumlar WHERE company_email = ?");
            $checkEmail->execute([$company_email]);
            if ($checkEmail->fetch()) {
                $message = "Bu e-posta adresiyle daha önce kayıt olunmuş.";
                $messageType = "danger";
            } else {
                // Sonraki veri tabanı adını belirle (prolyn_K1, prolyn_K2, vb.)
                $stmt = $pdo->query("SHOW DATABASES");
                $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $maxNum = 0;
                foreach ($databases as $dbName) {
                    if (preg_match('/^prolyn_k(\d+)$/i', $dbName, $matches)) {
                        $num = (int)$matches[1];
                        if ($num > $maxNum) {
                            $maxNum = $num;
                        }
                    }
                }
                $nextNum = $maxNum + 1;
                $newDbName = "prolyn_k" . $nextNum;
                $customerCode = "k" . $nextNum;
                
                // Yeni veritabanını oluştur
                $pdo->exec("CREATE DATABASE `$newDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // prolyn_default'tan şablonu aktar
                $mysqldumpPath = '/Applications/XAMPP/xamppfiles/bin/mysqldump';
                $mysqlPath = '/Applications/XAMPP/xamppfiles/bin/mysql';
                
                // Şifre boşluk veya özel karakter içerebileceği için argümanları güvenli geçelim
                $cmd = sprintf(
                    "%s -u %s -p%s prolyn_default | %s -u %s -p%s %s",
                    escapeshellarg($mysqldumpPath),
                    escapeshellarg($user),
                    escapeshellarg($pass),
                    escapeshellarg($mysqlPath),
                    escapeshellarg($user),
                    escapeshellarg($pass),
                    escapeshellarg($newDbName)
                );
                
                $output = [];
                $returnVar = 0;
                exec($cmd, $output, $returnVar);
                
                if ($returnVar !== 0) {
                    throw new Exception("Şablon veritabanı kopyalanamadı.");
                }
                
                // Yeni tenant veri tabanına bağlan
                $tenantDsn = "mysql:host=$host;dbname=$newDbName;charset=$charset";
                $tenantPdo = new PDO($tenantDsn, $user, $pass, $options);
                
                // Gerekirse permissions kolonunu ekle
                try {
                    $tenantPdo->exec("ALTER TABLE usergrup ADD COLUMN permissions JSON DEFAULT NULL");
                } catch (\Exception $ex) {
                    // Zaten var olabilir
                }
                
                // Default Yetki Grubu ekle
                $stmtGrup = $tenantPdo->prepare("INSERT INTO usergrup (UserGrupID, UserGrupName, UserGrupDesc, UserGrupStatus, permissions) VALUES (1, 'Yönetici', 'Tam Yetkili Yönetici', 1, '{\"all\": true}')");
                $stmtGrup->execute();
                
                $final_city_id = ($city_id > 0) ? $city_id : 0;
                $final_district_id = ($district_id > 0) ? $district_id : 0;

                // Firma kaydı oluştur
                $stmtFirm = $tenantPdo->prepare("INSERT INTO firmalar (FirmaID, CustomerID, FirmaAdi, FirmaDetay, FirmaVergi, FirmaVNO, BayiCity, BayiTown, BayiAdres) VALUES (1, 0, ?, ?, ?, ?, ?, ?, ?)");
                $stmtFirm->execute([$company_name, $official_name, $tax_office, $tax_number, $final_city_id, $final_district_id, $billing_address]);
                $firmaId = $tenantPdo->lastInsertId() ?: 1;
                
                // Şube ekle
                $stmtBranch = $tenantPdo->prepare("INSERT INTO subeler (BranchID, FirmaID, CustomerID, BranchName, BranchShort, BranchStatus) VALUES (1, ?, 1, 'Merkez Şube', 'MRK', 1)");
                $stmtBranch->execute([$firmaId]);
                $branchId = $tenantPdo->lastInsertId() ?: 1;
                
                // Şifreyi BCRYPT ile hashle
                $hashedPassword = password_hash($admin_password, PASSWORD_BCRYPT);
                
                // Yönetici kullanıcısını ekle
                $stmtUser = $tenantPdo->prepare("INSERT INTO users (FirmaID, UserName, UserMail, UserPhone, UserPass, UserAuthority, UserGroupID, BranchID, UserStatus) VALUES (?, ?, ?, ?, ?, 1, 1, ?, 1)");
                $stmtUser->execute([$firmaId, $contact_name, $company_email, $company_phone, $hashedPassword, $branchId]);

                // Nakit Kasa ekle
                $stmtKasa = $tenantPdo->prepare("INSERT INTO nakitkasa (FirmaID, NakitKasaTutar) VALUES (?, 0)");
                $stmtKasa->execute([$firmaId]);
                
                // Master DB'ye Kurum kaydı yap
                $stmtMaster = $pdo->prepare("INSERT INTO kurumlar (customer_code, company_name, official_name, company_email, company_phone, database_name, database_password, status, trial_start_date, trial_end_date, contact_name, contact_email, contact_phone, tax_office, tax_number, billing_address, city_id, district_id, allowed_modules) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), ?, ?, ?, ?, ?, ?, ?, ?, '[\"core\",\"customer\",\"commercial\",\"appointment\",\"current_account\",\"inventory\",\"accounting\",\"invoice\",\"contract\",\"reports\"]')");
                $stmtMaster->execute([$customerCode, $company_name, $official_name, $company_email, $company_phone, $newDbName, $pass, $contact_name, $company_email, $company_phone, $tax_office, $tax_number, $billing_address, $city_id, $district_id]);
                $customerId = $pdo->lastInsertId() ?: 1;
                $successData = [
                    'customer_code' => $customerCode,
                    'company_name'  => $company_name,
                    'email'         => $company_email,
                ];
            }
        } catch (Exception $e) {
            $message = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Şehirleri çek
$stmtCities = $pdo->query("SELECT * FROM prolyn_default.iller ORDER BY sehiradi ASC");
$iller = $stmtCities->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>30 Günlük Deneme Sürümü - <?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($appSettings['seo_description'] ?? 'Kurumsal Kaynak Planlama, CRM, İK ve Ön Muhasebe çözümleri tek bir platformda.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? 'erp, bulut erp, crm, ön muhasebe, ik'); ?>">
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css?v=5" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank">
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Header-->
        <div class="landing-header glass-header" data-kt-sticky="true" data-kt-sticky-name="landing-header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
            <div class="container-xxl">
                <nav class="navbar navbar-dark w-100 py-4 py-lg-6">
                    <!--begin::Logo-->
                    <a class="navbar-brand d-flex align-items-center" href="index.php">
                        <h1 class="text-white fs-1 fw-bolder m-0">Prolyn<span class="text-primary">.</span></h1>
                    </a>
                    <!--end::Logo-->
                    
                    <!-- Mobile Toggle -->
                    <button class="btn btn-icon btn-active-color-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                        <i class="bi bi-list fs-1 text-white"></i>
                    </button>

                    <!-- Desktop Menu -->
                    <div class="d-none d-lg-flex align-items-center ms-auto">
                        <ul class="nav text-white fw-semibold fs-6 me-10">
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="index.php#ozellikler">Özellikler</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="index.php#nasil-calisir">Nasıl Çalışır?</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="moduller.php">Modüller</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="pricing.php">Fiyatlandırma</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="contact.php">İletişim</a>
                            </li>
                        </ul>
                        
                        <div class="d-flex align-items-center flex-shrink-0">
                            <a href="register.php" class="btn btn-primary-glow px-6 py-3 fw-bold">30 Gün Ücretsiz Dene</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Hero-->
        <div class="hero-section text-center">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up">
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">
                        Bulut ERP <span class="text-gradient-primary">30 Günlük Deneme</span> Sürümü
                    </h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 600px;">
                        Kredi kartı gerekmeden, hiçbir ücret ödemeden tüm özellikleri hemen kullanmaya başlayın.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <div class="py-10 mb-20">
            <div class="container-xxl">
                <div class="row g-10 align-items-start">
                    
                    <!-- Information Panel -->
                    <div class="col-lg-5 animate-fade-in-up delay-100 d-none d-lg-block">
                        <div class="glass-card p-10 h-100">
                            <h3 class="text-white fs-2 fw-bold mb-8">Neden Ücretsiz Denemelisiniz?</h3>
                            
                            <ul class="check-list list-unstyled d-flex flex-column gap-6 mb-10">
                                <li>
                                    <i class="ki-duotone ki-check-circle fs-2 text-success me-3"></i>
                                    <div>
                                        <span class="d-block fw-bold text-white fs-5 mb-1">Anında Kurulum</span>
                                        <span class="text-gray-400">Beklemek yok, saniyeler içinde size özel sisteminiz hazır.</span>
                                    </div>
                                </li>
                                <li>
                                    <i class="ki-duotone ki-check-circle fs-2 text-success me-3"></i>
                                    <div>
                                        <span class="d-block fw-bold text-white fs-5 mb-1">Tamamen İzole</span>
                                        <span class="text-gray-400">Her müşteriye özel ayrı bir veritabanı tahsis edilir.</span>
                                    </div>
                                </li>
                                <li>
                                    <i class="ki-duotone ki-check-circle fs-2 text-success me-3"></i>
                                    <div>
                                        <span class="d-block fw-bold text-white fs-5 mb-1">Tüm Modüller Açık</span>
                                        <span class="text-gray-400">30 gün boyunca hiçbir kısıtlama olmadan sistemi tam kapasite kullanın.</span>
                                    </div>
                                </li>
                                <li>
                                    <i class="ki-duotone ki-check-circle fs-2 text-success me-3"></i>
                                    <div>
                                        <span class="d-block fw-bold text-white fs-5 mb-1">Kredi Kartı Yok</span>
                                        <span class="text-gray-400">Deneme süreci için herhangi bir kart bilgisi istemiyoruz.</span>
                                    </div>
                                </li>
                            </ul>
                            
                            <div class="p-6 bg-dark bg-opacity-50 rounded border border-gray-800">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-duotone ki-shield-tick fs-1 text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
                                    <span class="fw-bold text-white fs-5">Güvenli Veri Altyapısı</span>
                                </div>
                                <p class="text-gray-400 mb-0">Verileriniz yüksek güvenlikli sunucularda 256-bit şifreleme ile korunur ve günlük olarak yedeklenir.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Panel -->
                    <div class="col-lg-7 animate-fade-in-up delay-200">
                        <div class="glass-card p-10">
                            <?php if ($successData): ?>
                                <div class="alert alert-success d-flex align-items-center p-6 mb-8 border border-success border-opacity-25" style="background: rgba(80, 205, 137, 0.1);">
                                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-success">Veritabanı Başarıyla Kuruldu!</h4>
                                        <span class="text-gray-300">Hesabınız ve izole veritabanınız 30 günlük ücretsiz deneme için hazırlandı.</span>
                                    </div>
                                </div>
                                
                                <div class="bg-black bg-opacity-40 rounded p-8 mb-8 text-start border border-gray-800">
                                    <h5 class="text-white mb-6 fs-4">Giriş Bilgileriniz</h5>
                                    
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="w-120px text-gray-500">Kurum Kodu</div>
                                        <div class="fw-bolder fs-5 text-white bg-dark px-3 py-1 rounded border border-gray-700"><?= htmlspecialchars($successData['customer_code']) ?></div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="w-120px text-gray-500">E-posta</div>
                                        <div class="fw-bolder fs-5 text-white"><?= htmlspecialchars($successData['email']) ?></div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="w-120px text-gray-500">Seçilen Plan</div>
                                        <div class="fw-bold fs-5 text-primary"><?= htmlspecialchars(!empty($selectedPlan) ? ucfirst($selectedPlan) : 'Pro') ?></div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-10">
                                    <a href="/prolyn/login.php?KurumKodu=<?= urlencode($successData['customer_code']) ?>" class="btn btn-success btn-lg w-100 py-4 fs-5 fw-bold" style="background-color: #50cd89;">
                                        Hemen Sisteme Giriş Yap <i class="ki-duotone ki-arrow-right fs-2 ms-2"><span class="path1"></span><span class="path2"></span></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <h3 class="text-white fs-2 fw-bold mb-8">Hemen Başvurun</h3>
                                
                                <?php if ($message): ?>
                                    <div class="alert alert-<?= $messageType ?> p-5 mb-8 border border-<?= $messageType ?> border-opacity-25" style="background: rgba(var(--bs-<?= $messageType ?>-rgb), 0.1);">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-information fs-2x text-<?= $messageType ?> me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            <div class="fw-semibold text-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <form action="" method="POST">
                                    <div class="row g-8 mb-8">
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold text-gray-300">Şirket / Kurum Adı <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name" class="form-control form-control-lg glass-input text-uppercase" placeholder="Örn: Limitless Teknoloji" required value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold text-gray-300">Resmi Ticaret Unvanı <span class="text-danger">*</span></label>
                                            <input type="text" name="official_name" class="form-control form-control-lg glass-input text-uppercase" placeholder="Örn: Limitless Teknoloji A.Ş." required value="<?= htmlspecialchars($_POST['official_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold text-gray-300">Yönetici Adı Soyadı <span class="text-danger">*</span></label>
                                            <input type="text" name="contact_name" class="form-control form-control-lg glass-input text-uppercase" placeholder="Örn: Ali Yılmaz" required value="<?= htmlspecialchars($_POST['contact_name'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-8">
                                        <label class="form-label fs-6 fw-semibold text-gray-300">E-posta Adresi <span class="text-danger">*</span></label>
                                        <input type="email" name="company_email" class="form-control form-control-lg glass-input" placeholder="ornek@sirket.com" required value="<?= htmlspecialchars($_POST['company_email'] ?? '') ?>">
                                        <div class="form-text text-gray-500 mt-2">Bu adres aynı zamanda sisteme giriş e-postanız olacaktır.</div>
                                    </div>
                                    
                                    <div class="mb-8">
                                        <label class="form-label fs-6 fw-semibold text-gray-300">Telefon Numarası</label>
                                        <input type="text" name="company_phone" class="form-control form-control-lg glass-input" placeholder="Örn: 0555 123 4567" value="<?= htmlspecialchars($_POST['company_phone'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row g-8 mb-8">
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold text-gray-300">İl <span class="text-danger">*</span></label>
                                            <select name="city_id" id="city_id" class="form-select form-select-lg glass-input" required onchange="loadIlceler(this.value)">
                                                <option value="">İl Seçiniz</option>
                                                <?php foreach ($iller as $il): ?>
                                                    <option value="<?php echo $il['iller_id']; ?>" <?= (isset($_POST['city_id']) && $_POST['city_id'] == $il['iller_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($il['sehiradi']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold text-gray-300">İlçe <span class="text-danger">*</span></label>
                                            <select name="district_id" id="district_id" class="form-select form-select-lg glass-input" required>
                                                <option value="">Önce İl Seçiniz</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-7">
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold text-gray-300">Vergi Dairesi <span class="text-danger">*</span></label>
                                            <input type="text" name="tax_office" class="form-control form-control-lg glass-input text-uppercase" placeholder="Örn: Şişli" value="<?php echo htmlspecialchars($_POST['tax_office'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold text-gray-300">Vergi No / TC Kimlik No <span class="text-danger">*</span></label>
                                            <input type="text" name="tax_number" class="form-control form-control-lg glass-input" placeholder="10 veya 11 Haneli" value="<?php echo htmlspecialchars($_POST['tax_number'] ?? ''); ?>" maxlength="11" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold text-gray-300">Açık Adres <span class="text-danger">*</span></label>
                                        <textarea name="billing_address" class="form-control form-control-lg glass-input" placeholder="Mahalle, Cadde, Sokak, Kapı No vb." rows="3" required><?php echo htmlspecialchars($_POST['billing_address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold text-gray-300">Yönetici Giriş Şifresi <span class="text-danger">*</span></label>
                                        <input type="password" name="admin_password" class="form-control form-control-lg glass-input" placeholder="En az 6 karakter" required minlength="6">
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary-glow btn-lg px-10 py-4 fs-5 fw-bold w-100">Kurulumu Başlat & Dene</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--begin::Footer-->
        <div class="mb-0 py-10 border-top border-gray-800 mt-auto" style="background: rgba(19, 22, 31, 0.6); backdrop-filter: blur(10px);">
            <div class="container-xxl">
                <div class="d-flex flex-center">
                    <span class="text-gray-500 fw-semibold">&copy; <?= date('Y') ?> Prolyn ERP. Tüm hakları saklıdır. - <a href="https://echoshift.net" target="_blank" class="text-gray-400 text-hover-primary">Created by EchoShift</a></span>
                </div>
            </div>
        </div>
        <!--end::Footer-->
    <!--end::Root-->

    <!-- Offcanvas Mobile Menu -->
    <div class="offcanvas offcanvas-end offcanvas-glass border-0" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header border-bottom border-gray-800 p-6">
            <h5 class="offcanvas-title text-white fs-2 fw-bolder" id="mobileMenuLabel">Prolyn<span class="text-primary">.</span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
        </div>
        <div class="offcanvas-body p-6">
            <ul class="nav flex-column fs-4 fw-semibold mb-8">
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="index.php#ozellikler"><i class="bi bi-star me-2 text-primary"></i>Özellikler</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="index.php#nasil-calisir"><i class="bi bi-info-circle me-2 text-primary"></i>Nasıl Çalışır?</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="moduller.php"><i class="bi bi-grid me-2 text-primary"></i>Modüller</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="pricing.php"><i class="bi bi-tag me-2 text-primary"></i>Fiyatlandırma</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="contact.php"><i class="bi bi-envelope me-2 text-primary"></i>İletişim</a></li>
            </ul>
            <div class="d-grid mt-8">
                <a href="register.php" class="btn btn-primary-glow py-3 fw-bold fs-5 shadow-sm">30 Gün Ücretsiz Dene</a>
            </div>
        </div>
    </div>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

<!-- Floating Action Buttons -->
<div class="floating-action-buttons">
    <a href="tel:+905555555555" class="fab-btn fab-phone" title="Bizi Arayın">
        <i class="bi bi-telephone-fill"></i>
    </a>
    <a href="https://wa.me/905555555555" class="fab-btn fab-whatsapp" target="_blank" title="WhatsApp Destek">
        <i class="bi bi-whatsapp"></i>
    </a>
    <button class="fab-btn fab-scrolltop" onclick="window.scrollTo({top: 0, behavior: 'smooth'});" title="Yukarı Çık">
        <i class="bi bi-arrow-up-short"></i>
    </button>
</div>

<script>
function loadIlceler(cityId) {
    const districtSelect = document.getElementById('district_id');
    
    if (!cityId) {
        districtSelect.innerHTML = '<option value="">Önce İl Seçiniz</option>';
        return;
    }
    
    districtSelect.innerHTML = '<option value="">Yükleniyor...</option>';
    
    fetch('p/crm/ajax_get_ilceler.php?city_id=' + cityId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<option value="">İlçe Seçiniz</option>';
                data.data.forEach(function(ilce) {
                    html += '<option value="' + ilce.ilce_id + '">' + ilce.ilceadi + '</option>';
                });
                districtSelect.innerHTML = html;
                
                // Eğer önceden seçili bir ilçe varsa onu geri yükle (post back durumu için)
                const selectedDistrict = '<?= isset($_POST["district_id"]) ? $_POST["district_id"] : "" ?>';
                if (selectedDistrict) {
                    districtSelect.value = selectedDistrict;
                }
            } else {
                districtSelect.innerHTML = '<option value="">İlçe yüklenemedi</option>';
                console.error('İlçeler yüklenemedi:', data.message);
            }
        })
        .catch(error => {
            districtSelect.innerHTML = '<option value="">Hata oluştu</option>';
            console.error('AJAX hatası:', error);
        });
}

// Sayfa yüklendiğinde il seçili ise ilçeleri getir
document.addEventListener("DOMContentLoaded", function() {
    const citySelect = document.getElementById('city_id');
    if (citySelect && citySelect.value) {
        loadIlceler(citySelect.value);
    }
});
</script>

</body>
</html>
