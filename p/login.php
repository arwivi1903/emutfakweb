<?php
/**
 * Admin Panel Login Sayfası
 * Prolyn/p/login.php
 */

require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/functions/auth.php';

// Güvenli oturum başlat (Secure/HttpOnly/SameSite çerez bayraklarıyla)
start_secure_session();

require_once __DIR__ . '/functions/combine.php';

// Zaten giriş yapıldıysa index'e yönlendir
if (isset($_SESSION['admin_id'])) {
    header('Location: ' . prolynweb_panel_url('index.php'), true, 302);
    exit;
}

// Login mesajları
$message = '';
$messageType = '';

if (isset($_GET['islem'])) {
    $messages = [
        'hata'          => ['type' => 'danger', 'text' => 'Kullanıcı mail veya şifreniz yanlış'],
        'yetki'         => ['type' => 'primary', 'text' => 'Oturumunuz sona ermiş. Lütfen tekrar giriş yapınız.'],
        'ok'            => ['type' => 'success', 'text' => 'Giriş Başarılı'],
        'bos'           => ['type' => 'danger', 'text' => 'Email ve şifre zorunludur'],
        'kullanici_yok' => ['type' => 'danger', 'text' => 'Kullanıcı mail veya şifreniz yanlış'],
        'pasif'         => ['type' => 'warning', 'text' => 'Kullanıcı pasif veya erişim kapalı'],
        'sifre'         => ['type' => 'danger', 'text' => 'Kullanıcı mail veya şifreniz yanlış'],
        'rate_limit'    => ['type' => 'danger', 'text' => 'Çok fazla başarısız deneme yapıldı. Lütfen bir süre sonra tekrar deneyin.'],
    ];
    
    if (isset($messages[$_GET['islem']])) {
        $messageType = $messages[$_GET['islem']]['type'];
        $message = $messages[$_GET['islem']]['text'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">

<head>
    <base href="<?= htmlspecialchars(prolynweb_panel_url() . '/', ENT_QUOTES, 'UTF-8') ?>" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light dark" />
    <title>Prolyn ERP - Giriş</title>
    <link rel="icon" href="dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="dist/assets/css/custom/panel-tema.css?v=1" rel="stylesheet" type="text/css" />
    <script>
    // Tema: varsayılan AÇIK. Seçim localStorage("data-bs-theme") içinde saklanır (panelle ortak).
    // Boyamadan önce uygulanır; böylece açılışta renk sıçraması olmaz.
    (function () {
        var tema = 'light';
        try {
            var k = localStorage.getItem('data-bs-theme');
            if (k === 'dark' || k === 'light') { tema = k; }
            else if (k === 'system') { tema = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
        } catch (e) { /* depolama kapalı: açık tema */ }
        document.documentElement.setAttribute('data-bs-theme', tema);
    })();
    </script>
</head>

<body id="kt_body" class="app-blank pl-giris">
    <div class="pl-giris-sar" id="kt_app_root">

        <!-- Sol: ürün anlatımı + animasyonlu illüstrasyon (yalnızca geniş ekranda) -->
        <section class="pl-giris-sol" aria-hidden="false">
            <div class="d-flex align-items-center">
                <img src="dist/logo/prolyn-logo-horizontal-sm.png" alt="Prolyn" class="theme-light-show" style="height:36px;width:auto" />
                <img src="dist/logo/prolyn-logo-horizontal-white-sm.png" alt="Prolyn" class="theme-dark-show" style="height:36px;width:auto" />
            </div>
            <h2>Tüm müşteri firmalarınız tek panelde</h2>
            <p>Firmaları, abonelikleri, destek taleplerini ve ödemeleri buradan yönetirsiniz. Her firmanın verisi kendi veritabanında ayrı durur.</p>
            <div class="pl-giris-ill"><?php require __DIR__ . '/partials/ill_giris.php'; ?></div>
            <ul class="pl-giris-liste">
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Müşteri firmalar, abonelikler ve kuponlar</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Destek talepleri, faturalar ve ödemeler</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Paketler, modüller ve web sitesi içeriği</li>
            </ul>
        </section>

        <!-- Sağ: giriş formu -->
        <main class="pl-giris-sag">
            <div class="pl-giris-ust">
                <button type="button" data-pl-tema class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px pl-tema-btn" title="Temayı değiştir (açık / koyu)" aria-label="Temayı değiştir">
                    <i class="ki-duotone ki-night-day theme-light-show fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span></i>
                    <i class="ki-duotone ki-moon theme-dark-show fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>

            <div class="pl-giris-kart">
                <div class="pl-giris-marka d-lg-none">
                    <img src="dist/logo/prolyn-logo-horizontal-sm.png" alt="Prolyn" class="theme-light-show" />
                    <img src="dist/logo/prolyn-logo-horizontal-white-sm.png" alt="Prolyn" class="theme-dark-show" />
                </div>

                <h1>Hoş geldiniz</h1>
                <p class="pl-alt fs-6">Ana yönetim paneline giriş yapın.</p>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> mb-8" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-1 text-<?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></h4>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form class="form w-100" method="POST" action="config/islem.php">
                    <?= csrfTokenField() ?>
                    <div class="fv-row mb-6">
                        <label class="form-label fw-bold fs-6 mb-2" for="loginEmail">E-posta</label>
                        <input type="email" name="email" id="loginEmail"
                            class="form-control form-control-lg"
                            placeholder="Mail adresinizi giriniz" required autocomplete="off" />
                    </div>

                    <div class="fv-row mb-3">
                        <label class="form-label fw-bold fs-6 mb-2" for="loginPassword">Şifre</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="loginPassword"
                                class="form-control form-control-lg pe-12"
                                placeholder="Şifrenizi giriniz" required autocomplete="off" />
                            <button type="button" id="togglePassword" aria-label="Şifreyi göster"
                                class="btn btn-icon position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent"
                                style="width:36px;height:36px;color:var(--pl-muted);">
                                <svg id="pwEyeOn" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg id="pwEyeOff" style="display:none" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mt-8">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold py-4">Giriş Yap</button>
                    </div>
                </form>

                <div class="pl-giris-alt">Yetkiniz yoksa sistem yöneticinizle iletişime geçin.</div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
    var hostUrl = "dist/assets/";
    </script>
    <script src="dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="dist/assets/js/scripts.bundle.js"></script>
    <script src="dist/assets/js/custom/panel-tema.js?v=1"></script>
    <script>
    (function () {
        var btn = document.getElementById('togglePassword');
        var inp = document.getElementById('loginPassword');
        if (!btn || !inp) return;
        btn.addEventListener('click', function () {
            var goster = inp.type === 'password';
            inp.type = goster ? 'text' : 'password';
            document.getElementById('pwEyeOn').style.display = goster ? 'none' : '';
            document.getElementById('pwEyeOff').style.display = goster ? '' : 'none';
            btn.setAttribute('aria-label', goster ? 'Şifreyi gizle' : 'Şifreyi göster');
            inp.focus();
        });
    })();
    </script>
</body>

</html>