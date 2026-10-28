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
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Blog</title>
    <meta name="description" content="Prolyn blogu: mutfak, dolap ve banyo firmaları başta olmak üzere KOBİ'ler için randevu, montaj, tahsilat ve ön muhasebe üzerine uygulanabilir rehberler.">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP'); ?> - Blog">
    <meta property="og:description" content="Prolyn blogu: KOBİ'ler için randevu, montaj, tahsilat ve ön muhasebe üzerine uygulanabilir rehberler.">
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
    <link href="assets/css/blog.css?v=1" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank">
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
<?php $navActive = 'blog'; require __DIR__ . '/partials/site_header.php'; ?>

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

<?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>
    <!--end::Root-->

<?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <script src="assets/js/site.js?v=6"></script>

<?php require __DIR__ . '/partials/cookie_banner.php'; ?>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
