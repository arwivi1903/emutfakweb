<?php require_once 'db.php'; ?>
<?php
require_once __DIR__ . '/partials/blog_render.php';
$posts = [];
try {
    $stmt = $pdo->prepare("SELECT title, slug, excerpt, cover_image, published_at, content FROM blog_yazilari WHERE status = 'published' ORDER BY published_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[prolynweb] blog listesi okunamadi: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Blog</title>
    <meta name="description" content="Prolyn blogu: mutfak, dolap ve banyo firmaları başta olmak üzere KOBİ'ler için randevu, montaj, tahsilat ve ön muhasebe üzerine uygulanabilir rehberler.">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Blog">
    <meta property="og:description" content="Prolyn blogu: KOBİ'ler için randevu, montaj, tahsilat ve ön muhasebe üzerine uygulanabilir rehberler.">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=8" rel="stylesheet" type="text/css" />
    <link href="assets/css/blog.css?v=1" rel="stylesheet" type="text/css" />
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
                            <li class="nav-item"><a class="nav-link text-white px-4 text-primary" href="blog.php">Blog</a></li>
                            <li class="nav-item"><a class="nav-link text-white px-4" href="video.php">Videolar</a></li>
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
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">Prolyn <span class="text-gradient-primary">Blog</span></h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 800px;">
                        Randevu, montaj, tahsilat ve ön muhasebe üzerine sahada işe yarayan rehberler.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Blog List-->
        <div class="py-10 py-lg-15">
            <div class="container-xxl">
                <?php if (empty($posts)): ?>
                    <div class="text-center text-gray-400 py-20 fs-5">Henüz yayınlanmış bir yazı bulunmuyor. Yakında burada olacak.</div>
                <?php else: ?>
                    <?php $one = array_shift($posts); ?>
                    <a href="blog_detail.php?slug=<?= urlencode($one['slug']) ?>" class="glass-card blog-featured scroll-reveal">
                        <div class="blog-featured-art" <?= !empty($one['cover_image']) ? 'style="background-image:url(\'' . htmlspecialchars($one['cover_image'], ENT_QUOTES) . '\')"' : '' ?>>
                            <?php if (empty($one['cover_image'])): ?><span>Prolyn<i>.</i></span><?php endif; ?>
                        </div>
                        <div class="blog-featured-body">
                            <span class="blog-pill">Son yazı</span>
                            <h2><?= htmlspecialchars($one['title']) ?></h2>
                            <?php if (!empty($one['excerpt'])): ?><p><?= htmlspecialchars($one['excerpt']) ?></p><?php endif; ?>
                            <span class="blog-card-meta"><?= date('d.m.Y', strtotime($one['published_at'])) ?> · <?= blog_okuma_suresi((string)$one['content']) ?> dk okuma</span>
                            <span class="blog-more">Yazıyı oku <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                    <?php if ($posts): ?>
                    <div class="row g-6 mt-2">
                        <?php foreach ($posts as $i => $post): ?>
                        <div class="col-md-6 col-lg-4 scroll-reveal delay-<?= ($i % 3) + 1 ?>">
                            <a href="blog_detail.php?slug=<?= urlencode($post['slug']) ?>" class="glass-card blog-card h-100">
                                <?php if (!empty($post['cover_image'])): ?>
                                    <img src="<?= htmlspecialchars($post['cover_image']) ?>" class="blog-card-img" alt="" loading="lazy" />
                                <?php endif; ?>
                                <span class="blog-card-meta"><?= date('d.m.Y', strtotime($post['published_at'])) ?> · <?= blog_okuma_suresi((string)$post['content']) ?> dk okuma</span>
                                <h3><?= htmlspecialchars($post['title']) ?></h3>
                                <?php if (!empty($post['excerpt'])): ?><p><?= htmlspecialchars(mb_strimwidth($post['excerpt'], 0, 170, '…')) ?></p><?php endif; ?>
                                <span class="blog-more">Oku <i class="bi bi-arrow-right"></i></span>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <!--end::Blog List-->

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

<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
