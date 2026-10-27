<?php
/**
 * Prolyn - 30 Günlük Deneme Kaydı (register.php)
 * GÜVENLİK: CSRF, honeypot, IP rate-limit, güçlü şifre, güvenli provisioning.
 */
require_once __DIR__ . '/env.php';
require_once 'db.php'; // $pdo (prolyn_master), $appSettings
require_once __DIR__ . '/p/functions/provision.php';
require_once __DIR__ . '/partials/recaptcha.php';
$recaptcha = recaptcha_ayarlar($pdo);
$recaptchaAktif = recaptcha_aktif($recaptcha);

if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = filter_var(prolynweb_env('APP_ENV', 'development'), FILTER_DEFAULT) === 'production'
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Kampanya takip kodu (?ref=L123) — kayıt tamamlanınca lead ile eşleşir
if (isset($_GET['ref']) && preg_match('/^L\d{1,9}$/', (string)$_GET['ref'])) {
    $_SESSION['lead_ref'] = (string)$_GET['ref'];
}

// CSRF token
if (empty($_SESSION['reg_csrf'])) {
    $_SESSION['reg_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = '';
$successData = null;

$selectedPlan = $_GET['plan'] ?? '';

/* Fiyatlandırma sayfasındaki Aylık/Yıllık seçimi buraya `?periyot=yillik`
   olarak gelir. Deneme süreci her hâlükârda 30 gün ve ücretsizdir; seçim
   kurum kaydına not edilir ki sözleşmeye geçişte doğru periyot önerilsin. */
$secilenPeriyot = (($_GET['periyot'] ?? '') === 'yillik') ? 'yillik' : 'aylik';

/** Basit dosya tabanlı IP rate-limit (harici bağımlılık yok). */
function reg_rate_limit_check(int $limit): bool
{
    $dir = __DIR__ . '/p/config/rate_limit';
    if (!is_dir($dir)) { @mkdir($dir, 0770, true); }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $file = $dir . '/reg_' . md5($ip) . '.json';
    $now = time();
    $window = 3600;
    $hits = [];
    if (is_readable($file)) {
        $hits = json_decode((string)@file_get_contents($file), true) ?: [];
    }
    $hits = array_values(array_filter($hits, static fn($t) => ($now - (int)$t) < $window));
    if (count($hits) >= $limit) {
        return false;
    }
    $hits[] = $now;
    @file_put_contents($file, json_encode($hits), LOCK_EX);
    return true;
}

/** Güçlü şifre kuralı: en az 8 karakter, harf + rakam. */
function reg_password_strong(string $p): bool
{
    return strlen($p) >= 8 && preg_match('/[A-Za-zÇĞİÖŞÜçğıöşü]/u', $p) && preg_match('/\d/', $p);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['reg_csrf'], (string)$_POST['csrf_token'])) {
        $message = "Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.";
        $messageType = "danger";
    // 2) Honeypot (bot tuzağı - gizli alan dolduysa bot demektir)
    } elseif (!empty($_POST['website'])) {
        $message = "İstek reddedildi.";
        $messageType = "danger";
    // 3) Google reCAPTCHA (panelden açıldıysa)
    } elseif ($recaptchaAktif && !($__rc = recaptcha_dogrula($recaptcha, (string)($_POST['g_recaptcha_token'] ?? ''), 'register'))[0]) {
        error_log('[register] reCAPTCHA reddetti: ' . $__rc[1] . ' | ip: ' . ($_SERVER['REMOTE_ADDR'] ?? '-'));
        $message = "Güvenlik doğrulaması tamamlanamadı. Sayfayı yenileyip tekrar deneyin.";
        $messageType = "danger";
    // 4) Rate limit
    } elseif (!reg_rate_limit_check((int)prolynweb_env('REGISTER_RATE_LIMIT', 5))) {
        $message = "Çok fazla deneme yaptınız. Lütfen bir süre sonra tekrar deneyin.";
        $messageType = "danger";
    } else {

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

    $toUpperText = static function ($value) {
        $value = trim((string) $value);
        return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
    };

    $company_name = $toUpperText($company_name);
    $official_name = $toUpperText($official_name);
    $contact_name = $toUpperText($contact_name);
    $tax_office = $toUpperText($tax_office);
    $billing_address = $toUpperText($billing_address);

    if (!function_exists('firma_vno_digits')) {
        function firma_vno_digits($value) {
            return preg_replace('/\D+/', '', (string) $value);
        }
    }

    if (!function_exists('firma_vno_is_valid')) {
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
                    if ($v !== 0 && $vv === 0) { $vv = 9; }
                    $sum += $vv;
                }
                $checkDigit = ($sum % 10 === 0) ? 0 : (10 - ($sum % 10));
                return $checkDigit === $digits[9];
            }
            if ($value[0] === '0') { return false; }
            $digits = array_map('intval', str_split($value));
            $sumFirst10 = array_sum(array_slice($digits, 0, 10));
            return ($sumFirst10 % 10) === $digits[10];
        }
    }

    if (empty($company_name) || empty($official_name) || empty($contact_name) || empty($company_email) || empty($company_phone) || empty($admin_password) || empty($tax_number) || empty($tax_office) || empty($billing_address) || $city_id <= 0 || $district_id <= 0) {
        $message = "Lütfen tüm zorunlu alanları doldurunuz.";
        $messageType = "danger";
    } elseif (!filter_var($company_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Geçerli bir e-posta adresi giriniz.";
        $messageType = "danger";
    } elseif (!reg_password_strong($admin_password)) {
        $message = "Şifre en az 8 karakter olmalı ve harf ile rakam içermelidir.";
        $messageType = "danger";
    } elseif (!firma_vno_is_valid($tax_number)) {
        $message = "Geçersiz Vergi No veya TC Kimlik No girişi yaptınız.";
        $messageType = "danger";
    } else {
        try {
            // Benzersizlik kontrolü (E-posta, Telefon, Vergi No)
            $checkUser = $pdo->prepare("SELECT customer_id, company_email, company_phone, tax_number FROM kurumlar WHERE company_email = ? OR (company_phone = ? AND company_phone != '') OR tax_number = ?");
            $checkUser->execute([$company_email, $company_phone, $tax_number]);
            $existing = $checkUser->fetch();

            if ($existing) {
                if (strcasecmp($existing['company_email'], $company_email) === 0) {
                    $message = "Bu e-posta adresiyle daha önce kayıt olunmuş.";
                } elseif ($existing['company_phone'] === $company_phone && !empty($company_phone)) {
                    $message = "Bu telefon numarasıyla daha önce kayıt olunmuş.";
                } elseif ($existing['tax_number'] === $tax_number) {
                    $message = "Bu Vergi No / TC Kimlik No ile daha önce kayıt olunmuş.";
                } else {
                    $message = "Bu bilgilerle daha önce kayıt olunmuş.";
                }
                $messageType = "danger";
            } else {
                $planNameToInsert = !empty($selectedPlan) ? strtolower($selectedPlan) : 'pro';
                if (!in_array($planNameToInsert, ['basic', 'pro', 'enterprise'])) $planNameToInsert = 'pro';

                /* ── Paket modülleri: TEK KAYNAK paket_ozellikleri ────────────────
                   Önceden burada sabit bir modül listesi yazılıydı; seçilen paket
                   ne olursa olsun aynı 10 modül kaydediliyor, planning/bank/
                   statistics hiç gelmiyordu. Ayrıca `musteri_paketi` alanı HİÇ
                   yazılmıyordu — bu yüzden yeni kurumların paketi boş kalıyor,
                   paket limitleri (şube/kullanıcı) hiç uygulanmıyordu.          */
                $allowedModulesJson = null;
                try {
                    $planStmt = $pdo->prepare("SELECT allowed_modules FROM paket_ozellikleri WHERE plan_name = ? LIMIT 1");
                    $planStmt->execute([$planNameToInsert]);
                    $planRow = $planStmt->fetch(PDO::FETCH_ASSOC);
                    if ($planRow && !empty($planRow['allowed_modules'])) {
                        $decoded = json_decode((string)$planRow['allowed_modules'], true);
                        if (is_array($decoded) && !empty($decoded)) {
                            $allowedModulesJson = json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE);
                        }
                    }
                } catch (Exception $e) {
                    // paket_ozellikleri okunamadi -> asagidaki acil yedege dusulur
                }
                if ($allowedModulesJson === null) {
                    // ACİL YEDEK — paket_ozellikleri satırı yoksa kullanılır.
                    $yedekHarita = [
                        'basic'      => ['core','customer','appointment','reports','current_account','contract'],
                        'pro'        => ['core','customer','commercial','appointment','current_account','inventory',
                                         'accounting','invoice','contract','reports','bank','statistics'],
                        'enterprise' => ['core','customer','commercial','appointment','current_account','inventory',
                                         'accounting','invoice','contract','reports','planning','bank','statistics'],
                    ];
                    $allowedModulesJson = json_encode(
                        $yedekHarita[$planNameToInsert] ?? $yedekHarita['basic'], JSON_UNESCAPED_UNICODE);
                }

                // --- 1) Önce kurumlar kaydını oluştur (customer_id = DB adı kaynağı) ---
                // Şemadaki mevcut kolonlara göre dinamik INSERT
                $masterColumnsStmt = $pdo->query("SHOW COLUMNS FROM kurumlar");
                $masterColumns = [];
                foreach ($masterColumnsStmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                    $masterColumns[$col['Field']] = true;
                }

                $valueMap = [
                    'company_name'    => $company_name,
                    'official_name'   => $official_name,
                    'company_email'   => $company_email,
                    'company_phone'   => $company_phone,
                    'status'          => 'active',
                    'contact_person'  => $contact_name,
                    'contact_name'    => $contact_name,
                    'contact_email'   => $company_email,
                    'contact_phone'   => $company_phone,
                    'tax_office'      => $tax_office,
                    'tax_number'      => $tax_number,
                    'billing_address' => $billing_address,
                    'city_id'         => $city_id,
                    'district_id'     => $district_id,
                    'allowed_modules' => $allowedModulesJson,
                    'musteri_paketi'  => $planNameToInsert, // paket bağı — limitlerin çalışması için ŞART
                    'odeme_periyodu'  => $secilenPeriyot,   // kolon yoksa atlanır (dinamik INSERT)
                    'subscription_type' => 'trial',
                    'created_by'      => 0,
                    'database_name'   => '', // provisioning sonrası güncellenecek
                    'database_password' => '', // provisioning sonrası güncellenecek (NOT NULL güvenliği)
                ];
                $exprMap = [
                    'trial_start_date' => 'NOW()',
                    'trial_end_date'   => 'DATE_ADD(NOW(), INTERVAL 30 DAY)',
                ];

                $cols = []; $ph = []; $bind = [];
                foreach ($valueMap as $c => $v) {
                    if (!isset($masterColumns[$c])) continue;
                    $cols[] = "`$c`"; $ph[] = '?'; $bind[] = $v;
                }
                foreach ($exprMap as $c => $expr) {
                    if (!isset($masterColumns[$c])) continue;
                    $cols[] = "`$c`"; $ph[] = $expr;
                }
                if (empty($cols)) {
                    throw new Exception("Kurumlar tablosu için geçerli kolon bulunamadı.");
                }
                $pdo->prepare("INSERT INTO kurumlar (" . implode(',', $cols) . ") VALUES (" . implode(',', $ph) . ")")->execute($bind);
                $customerId = (int)$pdo->lastInsertId();
                if ($customerId <= 0) {
                    throw new Exception("Kurum kaydı oluşturulamadı.");
                }
                $customerCode = "k" . $customerId;

                // --- 2) Provisioning (DB + kısıtlı kullanıcı + tohumlama; hata olursa geri alır) ---
                $prov = provision_tenant([
                    'customer_id'     => $customerId,
                    'company_name'    => $company_name,
                    'official_name'   => $official_name,
                    'tax_office'      => $tax_office,
                    'tax_number'      => $tax_number,
                    'city_id'         => $city_id,
                    'district_id'     => $district_id,
                    'billing_address' => $billing_address,
                    'company_phone'   => $company_phone,
                    'admin_name'      => $contact_name,
                    'admin_email'     => $company_email,
                    'admin_password'  => $admin_password,
                ]);

                if (!$prov['success']) {
                    // Provisioning başarısız → kurumlar kaydını da geri al
                    $pdo->prepare("DELETE FROM kurumlar WHERE customer_id = ?")->execute([$customerId]);
                    throw new Exception("Sistem kurulumu tamamlanamadı. Lütfen daha sonra tekrar deneyin.");
                }

                // --- 3) kurumlar kaydını DB bilgileriyle güncelle ---
                $upd = [];
                $updBind = [];
                foreach (['database_name' => $prov['database_name'], 'customer_code' => $customerCode] as $c => $v) {
                    if (isset($masterColumns[$c])) { $upd[] = "`$c` = ?"; $updBind[] = $v; }
                }
                if (isset($masterColumns['database_user']))     { $upd[] = "`database_user` = ?";     $updBind[] = $prov['database_user']; }
                if (isset($masterColumns['database_password'])) { $upd[] = "`database_password` = ?"; $updBind[] = $prov['database_password']; }
                if (!empty($upd)) {
                    $updBind[] = $customerId;
                    $pdo->prepare("UPDATE kurumlar SET " . implode(', ', $upd) . " WHERE customer_id = ?")->execute($updBind);
                }

                // --- 4) Deneme aboneliği ---
                $stmtAbonelik = $pdo->prepare("INSERT INTO abonelikler (customer_id, plan_name, num_branches, price_per_month, total_price, billing_cycle, start_date, end_date, status, auto_renew) VALUES (?, ?, 1, 0, 0, 1, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'Deneme', 0)");
                $stmtAbonelik->execute([$customerId, $planNameToInsert]);

                // CSRF token'ı yenile (tek kullanım)
                $_SESSION['reg_csrf'] = bin2hex(random_bytes(32));

                // --- 5) Kampanyadan geldiyse lead'i "denemede" olarak eşleştir ---
                try {
                    $leadRef = (int)preg_replace('/\D/', '', (string)($_SESSION['lead_ref'] ?? ($_COOKIE['prolyn_lead'] ?? '')));
                    $leadSt = $pdo->prepare("UPDATE potansiyel_musteriler SET durum = 'deneme', customer_id = ?, son_iletisim = NOW()
                        WHERE (lead_id = ? OR (eposta <> '' AND eposta = ?) OR (telefon <> '' AND telefon = ?)) AND durum <> 'musteri'");
                    $telE164 = preg_replace('/\D/', '', $company_phone);
                    $telE164 = strlen($telE164) === 11 && $telE164[0] === '0' ? '+9' . $telE164 : (strlen($telE164) === 10 ? '+90' . $telE164 : '+' . $telE164);
                    $leadSt->execute([$customerId, $leadRef, strtolower($company_email), $telE164]);
                    if ($leadSt->rowCount() > 0) {
                        $pdo->prepare("INSERT INTO lead_aktiviteler (lead_id, tur, aciklama)
                            SELECT lead_id, 'sistem', ? FROM potansiyel_musteriler WHERE customer_id = ?")
                            ->execute(['Deneme kaydı açtı (kurum #' . $customerId . ').', $customerId]);
                    }
                } catch (Throwable $leadErr) {
                    // lead tabloları yoksa sessiz geç
                }

                $successData = [
                    'customer_code' => $customerCode,
                    'company_name'  => $company_name,
                    'email'         => $company_email,
                ];
            }
        } catch (Exception $e) {
            error_log('[register] ' . $e->getMessage());
            $appDebug = filter_var(prolynweb_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
            $message = $appDebug ? ("Kayıt hatası: " . $e->getMessage()) : "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
            $messageType = "danger";
        }
    }
    } // rate/csrf/honeypot else
}

// Şehirleri çek
$stmtCities = $pdo->query("SELECT * FROM `" . prolynweb_template_db() . "`.iller ORDER BY sehiradi ASC");
$iller = $stmtCities->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>30 Günlük Deneme Sürümü - <?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars('Prolyn ERP\'yi 30 gün ücretsiz deneyin. Kurulum ücreti olmadan bulut ERP, CRM ve ön muhasebe modüllerini test edin.'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="30 Günlük Deneme Sürümü - <?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars('Prolyn ERP\'yi 30 gün ücretsiz deneyin. Kurulum ücreti olmadan bulut ERP, CRM ve ön muhasebe modüllerini test edin.'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="keywords" content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? 'erp, bulut erp, crm, ön muhasebe, ik'); ?>">
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css?v=6" rel="stylesheet" type="text/css" />
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
                                <a class="nav-link text-white px-4" href="blog.php">Blog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="video.php">Videolar</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="pricing.php">Fiyatlandırma</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="contact.php">İletişim</a>
                            </li>
                        </ul>
                        
                        <div class="d-flex align-items-center flex-shrink-0">
                            <a href="<?php echo htmlspecialchars($appPanelUrl); ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold px-6 py-3 me-4">Giriş Yap</a>
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
                                
                                <div class="card bg-black bg-opacity-40 border-0 rounded-4 shadow-sm mb-8 text-start" style="backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
                                    <div class="card-header border-0 pt-8 pb-4 px-8">
                                        <h5 class="text-white mb-0 fs-3 fw-bolder d-flex align-items-center">
                                            <i class="ki-duotone ki-key fs-1 text-primary me-3"><span class="path1"></span><span class="path2"></span></i> Giriş Bilgileriniz
                                        </h5>
                                        <div class="text-gray-400 fs-7 mt-2">Sisteme giriş yaparken aşağıdaki bilgileri kullanacaksınız.</div>
                                    </div>
                                    <div class="card-body p-8 pt-2">
                                        <div class="d-flex flex-column gap-4">
                                            
                                            <!-- Kurum Kodu -->
                                            <div class="d-flex align-items-center p-4 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                                                <div class="w-45px h-45px d-flex align-items-center justify-content-center rounded-circle me-4" style="background: rgba(80, 205, 137, 0.1);">
                                                    <i class="ki-duotone ki-briefcase fs-2" style="color: #50cd89;"><span class="path1"></span><span class="path2"></span></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-gray-500 fs-7 fw-semibold mb-1">Kurum Kodu</div>
                                                    <div class="text-white fs-3 fw-bolder text-uppercase" style="letter-spacing: 1px;"><?= htmlspecialchars($successData['customer_code']) ?></div>
                                                </div>
                                                <div>
                                                    <span class="badge fw-bolder px-3 py-2 fs-7" style="background: rgba(80, 205, 137, 0.15); color: #50cd89; border: 1px solid rgba(80, 205, 137, 0.3);">AKTİF</span>
                                                </div>
                                            </div>

                                            <!-- E-posta -->
                                            <div class="d-flex align-items-center p-4 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                                                <div class="w-45px h-45px d-flex align-items-center justify-content-center rounded-circle me-4" style="background: rgba(0, 158, 247, 0.1);">
                                                    <i class="ki-duotone ki-sms fs-2" style="color: #009ef7;"><span class="path1"></span><span class="path2"></span></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-gray-500 fs-7 fw-semibold mb-1">Yönetici E-posta</div>
                                                    <div class="text-white fs-5 fw-bold"><?= htmlspecialchars($successData['email']) ?></div>
                                                </div>
                                            </div>

                                            <!-- Plan -->
                                            <div class="d-flex align-items-center p-4 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                                                <div class="w-45px h-45px d-flex align-items-center justify-content-center rounded-circle me-4" style="background: rgba(241, 65, 108, 0.1);">
                                                    <i class="ki-duotone ki-crown fs-2" style="color: #f1416c;"><span class="path1"></span><span class="path2"></span></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-gray-500 fs-7 fw-semibold mb-1">Seçilen Plan</div>
                                                    <div class="text-white fs-5 fw-bold">
                                                        <?= htmlspecialchars(!empty($selectedPlan) ? ucfirst($selectedPlan) : 'Pro') ?>
                                                        <span class="badge badge-light-primary ms-2"><?= $secilenPeriyot === 'yillik' ? 'Yıllık' : 'Aylık' ?></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="badge fw-bolder px-3 py-2 fs-7" style="background: rgba(241, 65, 108, 0.15); color: #f1416c; border: 1px solid rgba(241, 65, 108, 0.3);">30 GÜN DENEME</span>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-10">
                                    <a href="<?= htmlspecialchars(rtrim((string)prolynweb_env('APP_PANEL_URL', '/prolyn'), '/')) ?>/login.php?KurumKodu=<?= urlencode($successData['customer_code']) ?>" class="btn btn-success btn-lg w-100 py-4 fs-5 fw-bold" style="background-color: #50cd89;">
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

                                <form action="" method="POST" autocomplete="off" id="registerForm">
                                    <input type="hidden" name="g_recaptcha_token" value="">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['reg_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                                    <!-- Honeypot: gerçek kullanıcılar görmez, botlar doldurur -->
                                    <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                                        <label>Web Siteniz</label>
                                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                                    </div>
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
                                        <label class="form-label fs-6 fw-semibold text-gray-300">Telefon Numarası <span class="text-danger">*</span></label>
                                        <input type="text" name="company_phone" class="form-control form-control-lg glass-input" placeholder="Örn: 0555 123 4567" value="<?= htmlspecialchars($_POST['company_phone'] ?? '') ?>" required>
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
                                        <input type="password" name="admin_password" class="form-control form-control-lg glass-input" placeholder="En az 8 karakter (harf + rakam)" required minlength="8">
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary-glow btn-lg px-10 py-4 fs-5 fw-bold w-100">Kurulumu Başlat & Dene</button>
                                    </div>
                                    <?php if ($recaptchaAktif): ?>
                                    <p class="text-gray-500 fs-7 mt-5 mb-0 text-center recaptcha-note">
                                        <i class="bi bi-shield-check text-success me-1"></i>Bu form Google reCAPTCHA ile korunur. Google
                                        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-gray-400 text-hover-primary">Gizlilik Politikası</a> ve
                                        <a href="https://policies.google.com/terms" target="_blank" rel="noopener" class="text-gray-400 text-hover-primary">Hizmet Şartları</a> geçerlidir.
                                    </p>
                                    <?php endif; ?>
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
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="blog.php"><i class="bi bi-file-text me-2 text-primary"></i>Blog</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="video.php"><i class="bi bi-play-circle me-2 text-primary"></i>Videolar</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="pricing.php"><i class="bi bi-tag me-2 text-primary"></i>Fiyatlandırma</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="contact.php"><i class="bi bi-envelope me-2 text-primary"></i>İletişim</a></li>
            </ul>
            <div class="d-grid gap-3 mt-8">
                <a href="register.php" class="btn btn-primary-glow py-3 fw-bold fs-5 shadow-sm">30 Gün Ücretsiz Dene</a>
                <a href="<?php echo htmlspecialchars($appPanelUrl); ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold py-3 fs-5">Giriş Yap</a>
            </div>
        </div>
    </div>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
<?php if ($recaptchaAktif) { echo recaptcha_istemci_kodu($recaptcha, 'registerForm', 'register', 'Kurulum başlatılıyor…'); } ?>
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

<?php require __DIR__ . '/partials/fab.php'; ?>
<script>
function loadIlceler(cityId) {
    const districtSelect = document.getElementById('district_id');
    
    if (!cityId) {
        districtSelect.innerHTML = '<option value="">Önce İl Seçiniz</option>';
        return;
    }
    
    districtSelect.innerHTML = '<option value="">Yükleniyor...</option>';
    
    fetch('ilceler.php?city_id=' + encodeURIComponent(cityId))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<option value="">İlçe Seçiniz</option>';
                districtSelect.innerHTML = html;
                data.data.forEach(function(ilce) {
                    const o = document.createElement('option');
                    o.value = ilce.ilce_id; o.textContent = ilce.ilceadi;
                    districtSelect.appendChild(o);
                });
                
                // Eğer önceden seçili bir ilçe varsa onu geri yükle (post back durumu için)
                const selectedDistrict = '<?= (int)($_POST["district_id"] ?? 0) ?: '' ?>';
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
