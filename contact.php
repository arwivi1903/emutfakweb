<?php
require_once __DIR__ . '/env.php';
require_once 'db.php'; // $pdo, $appSettings
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
if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}

$contactMsg = '';
$contactType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body    = trim($_POST['message'] ?? '');
    $hp      = $_POST['website'] ?? '';

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['contact_csrf'], (string)$_POST['csrf_token'])) {
        $contactMsg = 'Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.';
        $contactType = 'danger';
    } elseif (!empty($hp)) {
        // Honeypot dolu → bot
        $contactMsg = 'İstek reddedildi.';
        $contactType = 'danger';
    } elseif ($recaptchaAktif && !($__rc = recaptcha_dogrula($recaptcha, (string)($_POST['g_recaptcha_token'] ?? ''), 'contact'))[0]) {
        error_log('[contact] reCAPTCHA reddetti: ' . $__rc[1] . ' | ip: ' . ($_SERVER['REMOTE_ADDR'] ?? '-'));
        $contactMsg = 'Güvenlik doğrulaması tamamlanamadı. Sayfayı yenileyip tekrar deneyin.';
        $contactType = 'danger';
    } elseif ($name === '' || $subject === '' || $body === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactMsg = 'Lütfen tüm alanları doğru şekilde doldurun.';
        $contactType = 'danger';
    } elseif (mb_strlen($body) > 5000 || mb_strlen($subject) > 255 || mb_strlen($name) > 191) {
        $contactMsg = 'Girdiğiniz metin çok uzun.';
        $contactType = 'danger';
    } else {
        try {
            // Mesaj tablosu (yoksa oluştur)
            $pdo->exec("CREATE TABLE IF NOT EXISTS iletisim_mesajlari (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ad VARCHAR(191) NOT NULL,
                email VARCHAR(191) NOT NULL,
                konu VARCHAR(255) NOT NULL,
                mesaj TEXT NOT NULL,
                ip VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'new',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_status (status),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $ins = $pdo->prepare("INSERT INTO iletisim_mesajlari (ad, email, konu, mesaj, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->execute([
                $name, $email, $subject, $body,
                $_SERVER['REMOTE_ADDR'] ?? null,
                mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
            ]);

            // Bildirim e-postasını kuyruğa ekle (mail worker gönderecek). Best-effort.
            $notifyTo = trim((string)prolynweb_env('CONTACT_NOTIFY_TO', '')) ?: prolynweb_env('MAIL_FROM', 'noreply@prolyn.net');
            try {
                $mailSubject = 'Yeni İletişim Mesajı: ' . $subject;
                $mailBody = "Ad: {$name}\nE-posta: {$email}\nKonu: {$subject}\n\n{$body}";
                $pdo->prepare("INSERT INTO mail_kuyrugu (channel, recipient, subject, message, source, status, attempts, created_at) VALUES ('email', ?, ?, ?, 'iletisim_formu', 'pending', 0, NOW())")
                    ->execute([$notifyTo, $mailSubject, $mailBody]);
            } catch (\Throwable $qe) {
                // mail_kuyrugu tablosu yoksa yoksay; mesaj yine DB'ye kaydedildi.
                error_log('[contact] mail kuyruk hatası: ' . $qe->getMessage());
            }

            // CSRF yenile
            $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
            $contactMsg = 'Mesajınız başarıyla alındı. En kısa sürede size dönüş yapacağız.';
            $contactType = 'success';
        } catch (\Throwable $e) {
            error_log('[contact] ' . $e->getMessage());
            $contactMsg = 'Mesajınız gönderilemedi. Lütfen daha sonra tekrar deneyin.';
            $contactType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn - Yeni Nesil Bulut ERP Sistemi'); ?> - İletişim</title>
    <meta name="description" content="<?php echo htmlspecialchars('Prolyn ERP ekibiyle iletişime geçin: sorularınız, demo talepleriniz ve kurumsal teklif istekleriniz için bize ulaşın.'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn - Yeni Nesil Bulut ERP Sistemi'); ?> - İletişim">
    <meta property="og:description" content="<?php echo htmlspecialchars('Prolyn ERP ekibiyle iletişime geçin: sorularınız, demo talepleriniz ve kurumsal teklif istekleriniz için bize ulaşın.'); ?>">
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
                        Bizimle <span class="text-gradient-primary">İletişime Geçin</span>
                    </h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 600px;">
                        Sorularınız veya özel kurumsal talepleriniz için uzman ekibimizle görüşün. Size yardımcı olmaktan mutluluk duyarız.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Contact-->
        <div class="py-10 mb-20">
            <div class="container-xxl">
                <div class="row g-10 justify-content-center">
                    
                    <div class="col-lg-4 animate-fade-in-up delay-100">
                        <div class="d-flex flex-column gap-8">
<?php
                            $cEmail = trim((string)($appSettings['contact_email'] ?? '')) ?: 'info@prolyn.net';
                            $cPhone = trim((string)($appSettings['contact_phone'] ?? ''));
                            $cAddr  = trim((string)($appSettings['contact_address'] ?? ''));
                            $cTel   = preg_replace('/[^0-9+]/', '', $cPhone);
                            ?>
                            <div class="glass-card p-8">
                                <div class="feature-icon-box text-primary">
                                    <i class="ki-duotone ki-sms fs-2x"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                                <h4 class="text-white fs-4 fw-bold mb-2">E-posta</h4>
                                <a href="mailto:<?= htmlspecialchars($cEmail, ENT_QUOTES, 'UTF-8') ?>" class="text-gray-400 fs-5 text-hover-primary"><?= htmlspecialchars($cEmail, ENT_QUOTES, 'UTF-8') ?></a>
                            </div>
                            
                            <?php if ($cPhone !== ''): ?>
                            <div class="glass-card p-8">
                                <div class="feature-icon-box text-success">
                                    <i class="ki-duotone ki-phone fs-2x"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                                <h4 class="text-white fs-4 fw-bold mb-2">Telefon</h4>
                                <a href="tel:<?= htmlspecialchars($cTel, ENT_QUOTES, 'UTF-8') ?>" class="text-gray-400 fs-5 text-hover-primary"><?= htmlspecialchars($cPhone, ENT_QUOTES, 'UTF-8') ?></a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($cAddr !== ''): ?>
                            <div class="glass-card p-8">
                                <div class="feature-icon-box text-info">
                                    <i class="ki-duotone ki-geolocation fs-2x"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                                <h4 class="text-white fs-4 fw-bold mb-2">Adres</h4>
                                <p class="text-gray-400 fs-5 mb-0"><?= nl2br(htmlspecialchars($cAddr, ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-8 animate-fade-in-up delay-200">
                        <div class="glass-card p-10 h-100">
                            <h3 class="text-white fs-2 fw-bold mb-8">Mesaj Gönderin</h3>
                            <?php if (!empty($contactMsg)): ?>
                                <div class="alert alert-<?= htmlspecialchars($contactType) ?> p-5 mb-8">
                                    <?= htmlspecialchars($contactMsg) ?>
                                </div>
                            <?php endif; ?>
                            <form action="contact.php" method="POST" autocomplete="off" id="contactForm">
                                <input type="hidden" name="g_recaptcha_token" value="">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['contact_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                                <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                                    <label>Web Siteniz</label>
                                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                                </div>
                                <div class="row g-8 mb-8">
                                    <div class="col-md-6">
                                        <label class="form-label fs-6 fw-semibold text-gray-300">Adınız Soyadınız</label>
                                        <input type="text" name="name" class="form-control glass-input form-control-lg" placeholder="Örn: Ali Yılmaz" maxlength="191" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fs-6 fw-semibold text-gray-300">E-posta Adresiniz</label>
                                        <input type="email" name="email" class="form-control glass-input form-control-lg" placeholder="ornek@sirket.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="mb-8">
                                    <label class="form-label fs-6 fw-semibold text-gray-300">Konu</label>
                                    <input type="text" name="subject" class="form-control glass-input form-control-lg" placeholder="Neden ulaşıyorsunuz?" maxlength="255" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                                </div>
                                <div class="mb-10">
                                    <label class="form-label fs-6 fw-semibold text-gray-300">Mesajınız</label>
                                    <textarea name="message" class="form-control glass-input form-control-lg" rows="6" maxlength="5000" placeholder="Nasıl yardımcı olabiliriz?" required></textarea>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                                    <?php if ($recaptchaAktif): ?>
                                    <p class="text-gray-500 fs-7 mb-0 recaptcha-note" style="max-width:420px">
                                        <i class="bi bi-shield-check text-success me-1"></i>Bu form Google reCAPTCHA ile korunur. Google
                                        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-gray-400 text-hover-primary">Gizlilik Politikası</a> ve
                                        <a href="https://policies.google.com/terms" target="_blank" rel="noopener" class="text-gray-400 text-hover-primary">Hizmet Şartları</a> geçerlidir.
                                    </p>
                                    <?php else: ?><span></span><?php endif; ?>
                                    <button type="submit" class="btn btn-primary-glow px-10 py-4 fs-5 fw-bold">Mesajı Gönder</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Contact-->

        <!--begin::Footer-->
        <div class="mb-0 py-10 border-top border-gray-800 mt-auto" style="background: rgba(19, 22, 31, 0.6); backdrop-filter: blur(10px);">
            <div class="container-xxl">
                <div class="row pt-9 pb-5">
                    <div class="col-lg-4 mb-10 mb-lg-0">
                        <h1 class="text-white fs-2x fw-bolder mb-5">Prolyn<span class="text-primary">.</span></h1>
                        <p class="text-gray-500 fs-6 fw-semibold lh-lg mb-5">
                            <?php echo htmlspecialchars($appSettings['website_footer_text'] ?? 'Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız. Hız, güvenlik ve esnekliği bir arada sunuyoruz.'); ?>
                        </p>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold mb-5">Ürün</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="index.php#ozellikler" class="footer-link fw-semibold">Özellikler</a>
                            <a href="index.php#nasil-calisir" class="footer-link fw-semibold">Nasıl Çalışır?</a>
                            <a href="moduller.php" class="footer-link fw-semibold">Modüller</a>
                            <a href="blog.php" class="footer-link fw-semibold">Blog</a>
                            <a href="video.php" class="footer-link fw-semibold">Videolar</a>
                            <a href="pricing.php" class="footer-link fw-semibold">Fiyatlandırma</a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold mb-5">Destek</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="contact.php" class="footer-link fw-semibold">İletişim</a>
                            <a href="#" class="footer-link fw-semibold">SSS</a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h4 class="text-white fw-bold mb-5">Bizi Takip Edin</h4>
                        <div class="d-flex gap-4">
                            <?php if(!empty($appSettings['social_twitter'])): ?>
                                <a href="<?php echo htmlspecialchars($appSettings['social_twitter']); ?>" target="_blank" class="btn btn-icon btn-light-primary btn-sm rounded-circle">
                                    <i class="bi bi-twitter-x fs-4"></i>
                                </a>
                            <?php endif; ?>
                            <?php if(!empty($appSettings['social_linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($appSettings['social_linkedin']); ?>" target="_blank" class="btn btn-icon btn-light-primary btn-sm rounded-circle">
                                    <i class="bi bi-linkedin fs-4"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-center border-top border-gray-800 pt-10">
                    <span class="text-gray-500 fw-semibold">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>. Tüm hakları saklıdır. - <a href="https://echoshift.net" target="_blank" class="text-gray-400 text-hover-primary">Created by EchoShift</a></span>
                </div>
            </div>
        </div>
        <!--end::Footer-->
    </div>
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
<?php if ($recaptchaAktif) { echo recaptcha_istemci_kodu($recaptcha, 'contactForm', 'contact', 'Gönderiliyor…'); } ?>
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
