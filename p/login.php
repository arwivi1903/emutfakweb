<?php
/**
 * Admin Panel Login Sayfası
 * prolynweb/p/login.php
 */

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zaten giriş yapıldıysa index'e yönlendir
if (isset($_SESSION['admin_id'])) {
    header('Location: /prolynweb/p/index.php', true, 302);
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
    <base href="/prolynweb/p/"/>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Prolyn Admin Paneli - Giriş</title>
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
            body { background-image: url('dist/assets/media/auth/bg4.jpg'); }
            [data-bs-theme="dark"] body { background-image: url('dist/assets/media/auth/bg4-dark.jpg'); }
        </style>
        <div class="d-flex flex-column flex-column-fluid flex-lg-row">
            <!-- Sol Taraf -->
            <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                <div class="d-flex flex-center flex-lg-start flex-column">
                    <a href="index.php" class="mb-7">
                        <img alt="Logo" src="dist/assets/media/logos/custom-3.svg" />
                    </a>
                    <h2 class="text-white fw-normal m-0">İşletmeniz için tasarlanmış marka</h2>
                </div>
            </div>

            <!-- Sağ Taraf - Login Formu -->
            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
                <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
                    <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">
                        
                        <!-- Hata/Başarı Mesajı -->
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> mb-10" role="alert">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <h4 class="mb-1 text-<?php echo htmlspecialchars($messageType); ?>">
                                            <?php echo htmlspecialchars($message); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Login Başlığı -->
                        <div class="text-center mb-11">
                            <h1 class="text-dark fw-bolder mb-3">Prolyn Admin Paneli</h1>
                            <div class="text-gray-400 fw-semibold fs-6">Giriş Yapınız</div>
                        </div>

                        <!-- Login Formu -->
                        <form class="form w-100" method="POST" action="config/islem.php">
                            <!-- Email -->
                            <div class="fv-row mb-8">
                                <label class="form-label fw-bold text-dark fs-6 mb-2">Email</label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    class="form-control bg-light border-0" 
                                    placeholder="admin@prolyn.com"
                                    required 
                                    autocomplete="off" />
                            </div>

                            <!-- Şifre -->
                            <div class="fv-row mb-3">
                                <label class="form-label fw-bold text-dark fs-6 mb-2">Şifre</label>
                                <input 
                                    type="password" 
                                    name="password" 
                                    class="form-control bg-light border-0" 
                                    placeholder="Şifrenizi Giriniz"
                                    required 
                                    autocomplete="off" />
                            </div>

                            <!-- Giriş Yap Butonu -->
                            <div class="d-grid mb-10">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <span class="indicator-label fw-bold">Giriş Yap</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>var hostUrl = "dist/assets/";</script>
    <script src="dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="dist/assets/js/scripts.bundle.js"></script>
</body>
</html>
