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
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
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

<?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>
    <!--end::Root-->

<?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

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
<?php require __DIR__ . '/partials/cookie_banner.php'; ?>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
