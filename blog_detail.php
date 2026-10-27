<?php require_once 'db.php'; ?>
<?php
require_once __DIR__ . '/partials/blog_render.php';
$slug = trim((string)($_GET['slug'] ?? ''));
$post = null;
if ($slug !== '' && preg_match('/^[a-z0-9\-]{1,200}$/', $slug)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_yazilari WHERE slug = ? AND status = 'published' LIMIT 1");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[prolynweb] blog detay okunamadi: ' . $e->getMessage());
    }
}
if (!$post) {
    header('Location: blog.php', true, 302);
    exit;
}

$icerik = blog_render((string)$post['content']);
$okuma = blog_okuma_suresi((string)$post['content']);
$ozet = trim((string)($post['excerpt'] ?? '')) !== '' ? (string)$post['excerpt'] : mb_substr(trim(preg_replace('/[#>*\-\[\]()]+/u', ' ', strip_tags((string)$post['content']))), 0, 160);
$base = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');
$canonical = $base . '/blog_detail.php?slug=' . rawurlencode($post['slug']);
$sektorYazisi = (bool)preg_match('/mutfak|dolap|banyo|montaj|tezgah/iu', $post['title'] . ' ' . $post['content']);
$ctaUrl = $sektorYazisi ? 'mutfak-dolap-yazilimi?utm_source=blog&utm_medium=yazi&utm_campaign=' . rawurlencode($post['slug']) : 'register.php';

$digerYazilar = [];
try {
    $stmt = $pdo->prepare("SELECT title, slug, excerpt, cover_image, published_at, content FROM blog_yazilari WHERE status = 'published' AND slug != ? ORDER BY published_at DESC LIMIT 3");
    $stmt->execute([$slug]);
    $digerYazilar = $stmt->fetchAll();
} catch (Throwable $e) {}

$kapak = (string)($post['cover_image'] ?? '');
$kapakUrl = $kapak === '' ? null : (preg_match('#^https?://#', $kapak) ? $kapak : $base . '/' . ltrim($kapak, '/'));
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> | Prolyn Blog</title>
    <meta name="description" content="<?= htmlspecialchars($ozet) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="<?= htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ozet) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <?php if ($kapakUrl): ?><meta property="og:image" content="<?= htmlspecialchars($kapakUrl) ?>"><?php endif; ?>
    <?php if (!empty($post['published_at'])): ?><meta property="article:published_time" content="<?= date('c', strtotime($post['published_at'])) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'BlogPosting',
                'headline' => $post['title'],
                'description' => $ozet,
                'image' => $kapakUrl,
                'datePublished' => !empty($post['published_at']) ? date('c', strtotime($post['published_at'])) : null,
                'dateModified' => !empty($post['updated_at']) ? date('c', strtotime($post['updated_at'])) : null,
                'wordCount' => $icerik['kelime'],
                'inLanguage' => 'tr-TR',
                'author' => ['@type' => 'Organization', 'name' => $appSettings['site_name'] ?? 'Prolyn', 'url' => $base],
                'publisher' => ['@type' => 'Organization', 'name' => $appSettings['site_name'] ?? 'Prolyn', 'url' => $base],
                'mainEntityOfPage' => $canonical,
            ]),
            ['@type' => 'BreadcrumbList', 'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana sayfa', 'item' => $base . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $base . '/blog'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $canonical],
            ]],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
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

        <!--begin::Post-->
        <article class="blog-post">
            <div class="container-xxl">
                <nav class="blog-crumb" aria-label="Sayfa yolu"><a href="index.php">Ana sayfa</a><span>/</span><a href="blog.php">Blog</a></nav>
                <header class="blog-head">
                    <h1><?= htmlspecialchars($post['title']) ?></h1>
                    <?php if (trim((string)($post['excerpt'] ?? '')) !== ''): ?><p class="blog-lead"><?= htmlspecialchars($post['excerpt']) ?></p><?php endif; ?>
                    <div class="blog-meta">
                        <span><i class="bi bi-calendar3"></i> <?= !empty($post['published_at']) ? date('d.m.Y', strtotime($post['published_at'])) : '' ?></span>
                        <span><i class="bi bi-clock"></i> <?= $okuma ?> dk okuma</span>
                        <?php if (!empty($post['updated_at']) && !empty($post['published_at']) && strtotime($post['updated_at']) > strtotime($post['published_at']) + 86400): ?>
                        <span><i class="bi bi-arrow-repeat"></i> Güncellendi: <?= date('d.m.Y', strtotime($post['updated_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                </header>
                <?php if ($kapak !== ''): ?>
                    <img src="<?= htmlspecialchars($kapak) ?>" class="blog-cover" alt="<?= htmlspecialchars($post['title']) ?>" loading="eager" />
                <?php endif; ?>

                <div class="blog-layout">
                    <?php if (count($icerik['toc']) >= 2): ?>
                    <aside class="blog-toc" aria-label="İçindekiler">
                        <div class="blog-toc-title">İçindekiler</div>
                        <ol>
                            <?php foreach ($icerik['toc'] as $t): ?><li><a href="#<?= htmlspecialchars($t['id']) ?>"><?= htmlspecialchars($t['text']) ?></a></li><?php endforeach; ?>
                        </ol>
                    </aside>
                    <?php endif; ?>

                    <div class="blog-main">
                        <div class="blog-body"><?= $icerik['html'] ?></div>

                        <div class="blog-share">
                            <span>Paylaş:</span>
                            <a href="https://wa.me/?text=<?= rawurlencode($post['title'] . ' ' . $canonical) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile paylaş"><i class="bi bi-whatsapp"></i></a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($canonical) ?>" target="_blank" rel="noopener" aria-label="LinkedIn'de paylaş"><i class="bi bi-linkedin"></i></a>
                            <a href="https://x.com/intent/post?url=<?= rawurlencode($canonical) ?>&text=<?= rawurlencode($post['title']) ?>" target="_blank" rel="noopener" aria-label="X'te paylaş"><i class="bi bi-twitter-x"></i></a>
                            <button type="button" class="blog-copy" data-url="<?= htmlspecialchars($canonical) ?>" aria-label="Bağlantıyı kopyala"><i class="bi bi-link-45deg"></i></button>
                        </div>

                        <div class="blog-cta">
                            <?php if ($sektorYazisi): ?>
                                <div>
                                    <h2>Mutfak, dolap ve banyo firmaları için Prolyn</h2>
                                    <p>Ölçü ve montaj randevuları, eksik malzeme, sözleşme, taksit ve cari takibi tek ekranda. 30 gün ücretsiz; kurulum ve müşteri listesi aktarımı bizden.</p>
                                </div>
                                <a href="<?= htmlspecialchars($ctaUrl) ?>" class="btn btn-primary-glow px-7 py-3 fw-bold">Sektör sayfasını incele</a>
                            <?php else: ?>
                                <div>
                                    <h2>Prolyn'i 30 gün ücretsiz deneyin</h2>
                                    <p>Müşteri, randevu, cari, kasa ve sözleşme takibi tek panelde. Kredi kartı gerekmez.</p>
                                </div>
                                <a href="<?= htmlspecialchars($ctaUrl) ?>" class="btn btn-primary-glow px-7 py-3 fw-bold">Ücretsiz dene</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($digerYazilar): ?>
                <section class="blog-related">
                    <h2>Diğer yazılar</h2>
                    <div class="row g-6">
                        <?php foreach ($digerYazilar as $d): ?>
                        <div class="col-md-4">
                            <a href="blog_detail.php?slug=<?= urlencode($d['slug']) ?>" class="glass-card blog-card h-100">
                                <span class="blog-card-meta"><?= !empty($d['published_at']) ? date('d.m.Y', strtotime($d['published_at'])) : '' ?> · <?= blog_okuma_suresi((string)$d['content']) ?> dk</span>
                                <h3><?= htmlspecialchars($d['title']) ?></h3>
                                <?php if (!empty($d['excerpt'])): ?><p><?= htmlspecialchars(mb_strimwidth($d['excerpt'], 0, 140, '…')) ?></p><?php endif; ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </article>
        <!--end::Post-->

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


<script>
document.querySelectorAll('.blog-copy').forEach(function (b) {
  b.addEventListener('click', function () {
    var u = b.getAttribute('data-url');
    (navigator.clipboard ? navigator.clipboard.writeText(u) : Promise.reject()).then(function () {
      b.classList.add('ok'); b.innerHTML = '<i class="bi bi-check2"></i>';
      setTimeout(function () { b.classList.remove('ok'); b.innerHTML = '<i class="bi bi-link-45deg"></i>'; }, 1800);
    }).catch(function () { window.prompt('Bağlantıyı kopyalayın:', u); });
  });
});
</script>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
