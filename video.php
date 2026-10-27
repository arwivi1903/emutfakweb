<?php require_once 'db.php'; ?>
<?php
$videos = [];
try {
    $stmt = $pdo->prepare("SELECT youtube_video_id, title, thumbnail_url, author_name FROM videolar WHERE status = 'published' ORDER BY order_no ASC, id DESC");
    $stmt->execute();
    $videos = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[prolynweb] video listesi okunamadi: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Videolar</title>
    <meta name="description" content="Prolyn ERP tanıtım ve eğitim videoları.">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Videolar">
    <meta property="og:description" content="Prolyn ERP tanıtım ve eğitim videoları.">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=8" rel="stylesheet" type="text/css" />
    <style>
        .video-thumb { position: relative; cursor: pointer; overflow: hidden; border-radius: 1rem 1rem 0 0; aspect-ratio: 16/9; background: #000; }
        .video-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
        .video-thumb:hover img { transform: scale(1.05); }
        .video-play-btn { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        .video-play-btn i { font-size: 3.5rem; color: #fff; text-shadow: 0 4px 20px rgba(0,0,0,.6); transition: transform .2s ease; }
        .video-thumb:hover .video-play-btn i { transform: scale(1.15); }
        .video-thumb iframe { width: 100%; height: 100%; border: 0; display: block; }
    </style>
</head>
<body id="kt_body" class="app-blank">
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Header-->
        <div class="landing-header glass-header" data-kt-sticky="true" data-kt-sticky-name="landing-header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
            <div class="container-xxl">
                <nav class="navbar navbar-dark w-100 py-4 py-lg-6">
                    <a class="navbar-brand d-flex align-items-center" href="index.php">
                        <h1 class="text-white fs-1 fw-bolder m-0">Prolyn<span class="text-primary">.</span></h1>
                    </a>
                    <button class="btn btn-icon btn-active-color-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                        <i class="bi bi-list fs-1 text-white"></i>
                    </button>
                    <div class="d-none d-lg-flex align-items-center ms-auto">
                        <ul class="nav text-white fw-semibold fs-6 me-10">
                            <li class="nav-item"><a class="nav-link text-white px-4" href="index.php#ozellikler">Özellikler</a></li>
                            <li class="nav-item"><a class="nav-link text-white px-4" href="index.php#nasil-calisir">Nasıl Çalışır?</a></li>
                            <li class="nav-item"><a class="nav-link text-white px-4" href="moduller.php">Modüller</a></li>
                            <li class="nav-item"><a class="nav-link text-white px-4" href="blog.php">Blog</a></li>
                            <li class="nav-item"><a class="nav-link text-white px-4 text-primary" href="video.php">Videolar</a></li>
                            <li class="nav-item"><a class="nav-link text-white px-4" href="pricing.php">Fiyatlandırma</a></li>
                            <li class="nav-item"><a class="nav-link text-white px-4" href="contact.php">İletişim</a></li>
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
        <div class="hero-section hero-compact text-center">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up">
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">Prolyn <span class="text-gradient-primary">Videolar</span></h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 800px;">
                        Tanıtım ve eğitim videolarımızı
                        <a href="https://www.youtube.com/@echoLabs2" target="_blank" class="text-primary text-hover-primary">YouTube kanalımızdan</a>
                        izleyebilirsiniz.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Video Grid-->
        <div class="py-10 py-lg-15">
            <div class="container-xxl">
                <?php if (empty($videos)): ?>
                    <div class="text-center text-gray-400 py-20 fs-5">
                        Henüz yayınlanmış bir video bulunmuyor. Yakında burada olacak.
                    </div>
                <?php else: ?>
                    <div class="row g-10">
                        <?php foreach ($videos as $i => $v): ?>
                        <div class="col-md-6 col-lg-4 scroll-reveal delay-<?php echo ($i % 3) + 1; ?>">
                            <div class="glass-card h-100 overflow-hidden">
                                <div class="video-thumb" data-video-id="<?php echo htmlspecialchars($v['youtube_video_id']); ?>">
                                    <img src="<?php echo htmlspecialchars($v['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($v['title']); ?>" loading="lazy" />
                                    <div class="video-play-btn"><i class="bi bi-play-circle-fill"></i></div>
                                </div>
                                <div class="p-6">
                                    <h4 class="text-white fs-5 fw-bold mb-1"><?php echo htmlspecialchars($v['title']); ?></h4>
                                    <?php if (!empty($v['author_name'])): ?>
                                        <span class="text-muted fs-8"><?php echo htmlspecialchars($v['author_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!--end::Video Grid-->

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
                            <a href="index.php#sss" class="footer-link fw-semibold">SSS</a>
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
                            <a href="https://www.youtube.com/@echoLabs2" target="_blank" class="btn btn-icon btn-light-primary btn-sm rounded-circle">
                                <i class="bi bi-youtube fs-4"></i>
                            </a>
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
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <script src="assets/js/site.js?v=6"></script>
    <script>
        document.querySelectorAll('.video-thumb').forEach(function (el) {
            el.addEventListener('click', function () {
                var videoId = el.getAttribute('data-video-id');
                if (!videoId) { return; }
                el.innerHTML = '<iframe src="https://www.youtube.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1" title="YouTube video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }, { once: true });
        });
    </script>

<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
