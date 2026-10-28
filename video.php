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
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Videolar</title>
    <meta name="description" content="Prolyn ERP tanıtım ve eğitim videoları.">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Videolar">
    <meta property="og:description" content="Prolyn ERP tanıtım ve eğitim videoları.">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="p/dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="p/dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="p/dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800&display=swap" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=12" rel="stylesheet" type="text/css" />
    <link href="assets/css/tema.css?v=2" rel="stylesheet" type="text/css" />
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
<?php $navActive = 'video'; require __DIR__ . '/partials/site_header.php'; ?>

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

<?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>
    <!--end::Root-->

<?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

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

<?php require __DIR__ . '/partials/cookie_banner.php'; ?>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
