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
<html lang="tr">

<head>
    <base href="<?= htmlspecialchars(prolynweb_panel_url() . '/', ENT_QUOTES, 'UTF-8') ?>" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Prolyn ERP - Giriş</title>
    <link rel="shortcut icon" href="dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">
    <script>
    var defaultThemeMode = "light";
    var themeMode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }
        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
    </script>

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <style>
        body {
            background:
                radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.22), transparent 28%),
                radial-gradient(circle at 85% 18%, rgba(16, 185, 129, 0.18), transparent 24%),
                radial-gradient(circle at 50% 100%, rgba(14, 165, 233, 0.14), transparent 34%),
                linear-gradient(180deg, #07111f 0%, #0b1220 38%, #101827 100%);
            min-height: 100vh;
        }

        .login-shell {
            position: relative;
            overflow: hidden;
        }

        .login-shell::before,
        .login-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            filter: blur(8px);
        }

        .login-shell::before {
            width: 380px;
            height: 380px;
            left: -160px;
            top: -140px;
            background: rgba(59, 130, 246, 0.14);
        }

        .login-shell::after {
            width: 320px;
            height: 320px;
            right: -130px;
            bottom: -120px;
            background: rgba(34, 197, 94, 0.10);
        }

        .auth-shell {
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: rgba(8, 15, 31, 0.84);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 28px 90px rgba(0, 0, 0, 0.42);
            overflow: hidden;
        }

        .auth-card::before {
            content: '';
            display: block;
            height: 6px;
            background: linear-gradient(90deg, #2563eb 0%, #10b981 50%, #0ea5e9 100%);
        }

        .brand-mark {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #fff;
            font-weight: 800;
            font-size: 1.2rem;
            letter-spacing: 0.04em;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.28);
        }

        .hero-badge {
            border: 1px solid rgba(37, 99, 235, 0.16);
            background: rgba(37, 99, 235, 0.14);
            color: #dbeafe;
        }

        .surface-soft {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .text-soft {
            color: rgba(226, 232, 240, 0.82) !important;
        }

        .text-muted-soft {
            color: rgba(148, 163, 184, 0.95) !important;
        }

        .login-divider {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        .login-surface {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.18), transparent 26%),
                linear-gradient(180deg, rgba(10, 16, 31, 0.96) 0%, rgba(8, 15, 31, 0.96) 100%);
        }

        .prolyn-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 112px;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .prolyn-auth-input {
            background: #ffffff !important;
            color: #0f172a !important;
            caret-color: #0f172a;
            -webkit-text-fill-color: #0f172a;
        }

        .prolyn-auth-input::placeholder {
            color: #94a3b8;
            opacity: 1;
        }

        .prolyn-auth-input:-webkit-autofill,
        .prolyn-auth-input:-webkit-autofill:hover,
        a .prolyn-auth-input:-webkit-autofill:focus,
        .prolyn-auth-input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
            -webkit-text-fill-color: #0f172a !important;
            caret-color: #0f172a;
            transition: background-color 9999s ease-out 0s;
        }
        </style>
        <div class="d-flex flex-column flex-root auth-shell login-shell">
            <div class="d-flex flex-column flex-column-fluid py-10 py-lg-14">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-xl-6 col-xxl-5">
                            <div class="auth-card rounded-5 login-surface">
                                <div class="p-10 p-lg-14">
                                    <div class="d-flex align-items-center justify-content-between mb-10">
                                        <div class="d-flex align-items-center gap-4">
                                            <div class="brand-mark">P</div>
                                            <div>
                                                <div class="text-white fw-bolder fs-2 mb-1"><a href="http://prolyn.net"
                                                        rel="noopener noreferrer">Prolyn</a></div>
                                                <div class="text-muted-soft fw-semibold fs-7">Ana admin girişi</div>
                                            </div>
                                        </div>
                                        <span class="prolyn-chip">Secure Access</span>
                                    </div>

                                    <div class="mb-10">
                                        <h1 class="text-white fw-bolder fs-1 fs-lg-2qx mb-3">Hoş geldiniz</h1>
                                        <p class="text-soft fs-6 fs-lg-5 mb-0">Prolyn ana yönetim paneline güvenli
                                            şekilde giriş yapın.</p>
                                    </div>

                                    <?php if ($message): ?>
                                    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> mb-8"
                                        role="alert">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1 text-<?php echo htmlspecialchars($messageType); ?>">
                                                    <?php echo htmlspecialchars($message); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <form class="form w-100" method="POST" action="config/islem.php">
                                        <?= csrfTokenField() ?>
                                        <div class="fv-row mb-6">
                                            <label class="form-label fw-bold text-white fs-6 mb-2">E-posta</label>
                                            <input type="email" name="email"
                                                class="form-control form-control-solid form-control-lg border-0 prolyn-auth-input"
                                                placeholder="Mail adresinizi giriniz" required autocomplete="off" />
                                        </div>

                                        <div class="fv-row mb-3">
                                            <label class="form-label fw-bold text-white fs-6 mb-2">Şifre</label>
                                            <div class="position-relative">
                                                <input type="password" name="password" id="loginPassword"
                                                    class="form-control form-control-solid form-control-lg border-0 prolyn-auth-input pe-12"
                                                    placeholder="Şifrenizi giriniz" required autocomplete="off" />
                                                <button type="button" id="togglePassword" aria-label="Şifreyi göster"
                                                    class="btn btn-icon position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent"
                                                    style="width:36px;height:36px;color:#9aa0b4;">
                                                    <svg id="pwEyeOn" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg id="pwEyeOff" style="display:none" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="d-grid mt-8">
                                            <button type="submit" class="btn btn-primary btn-lg fw-bold py-4">
                                                Giriş Yap
                                            </button>
                                        </div>
                                    </form>

                                    <div class="mt-8 pt-8 border-top login-divider">
                                        <div class="row g-3 text-center text-white fw-semibold fs-7">
                                            <div class="col-4">
                                                <div class="px-3 py-3 rounded-3 surface-soft text-white">Hızlı</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="px-3 py-3 rounded-3 surface-soft text-white">Güvenli</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="px-3 py-3 rounded-3 surface-soft text-white">Kurumsal</div>
                                            </div>
                                        </div>
                                        <div class="text-center text-muted-soft fs-7 mt-8">
                                            Yetkiniz yoksa sistem yöneticinizle iletişime geçin.
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

    <!-- Scripts -->
    <script>
    var hostUrl = "dist/assets/";
    </script>
    <script src="dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="dist/assets/js/scripts.bundle.js"></script>
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