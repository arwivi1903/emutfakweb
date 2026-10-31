<?php require_once 'db.php'; ?>
<?php
/**
 * Prolyn - Video rehberleri
 * Videolar YouTube'da "Listelenmemiş" (unlisted) yüklenir; yalnızca bu sayfada, panelden eklenenler görünür.
 * Oynatıcı youtube-nocookie ile ve ancak ziyaretçi tıklayınca yüklenir (hız + gizlilik).
 */
$base = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');

$videolar = [];
$kategoriler = [];
try {
    $videolar = $pdo->query("SELECT id, youtube_video_id, title, description, kategori, sure, thumbnail_url, created_at
                             FROM videolar WHERE status = 'published' ORDER BY order_no ASC, id DESC")->fetchAll();
} catch (Throwable $e) {
    // Migrasyon (2026_10_31_blog_video_gelistirme.php) henüz çalışmadıysa eski şemayla oku.
    error_log('[prolynweb] video listesi (yeni sema) okunamadi: ' . $e->getMessage());
    try {
        $videolar = $pdo->query("SELECT id, youtube_video_id, title, NULL AS description, NULL AS kategori, NULL AS sure, thumbnail_url, created_at
                                 FROM videolar WHERE status = 'published' ORDER BY order_no ASC, id DESC")->fetchAll();
    } catch (Throwable $e2) {
        error_log('[prolynweb] video listesi okunamadi: ' . $e2->getMessage());
    }
}
foreach ($videolar as $v) {
    $k = trim((string)($v['kategori'] ?? ''));
    if ($k !== '') { $kategoriler[$k] = ($kategoriler[$k] ?? 0) + 1; }
}
$secili = trim((string)($_GET['kategori'] ?? ''));
if ($secili !== '' && !isset($kategoriler[$secili])) { $secili = ''; }
$liste = $secili === '' ? $videolar : array_values(array_filter($videolar, static fn($v) => trim((string)$v['kategori']) === $secili));

/** "04:35" / "1:02:10" -> ISO 8601 süre (PT4M35S). Geçersizse null. */
function video_iso_sure(?string $s): ?string
{
    if (!$s || !preg_match('/^(?:(\d{1,2}):)?(\d{1,2}):(\d{2})$/', $s, $m)) { return null; }
    return 'PT' . ((int)$m[1] ? (int)$m[1] . 'H' : '') . (int)$m[2] . 'M' . (int)$m[3] . 'S';
}
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$sayfaBaslik = 'Tanıtım ve Eğitim Videoları';
$sayfaAciklama = 'Prolyn ile ilk kurulum, müşteri ve sözleşme, randevu, tahsilat ve kasa işlemlerini adım adım gösteren kısa video rehberleri.';
$jsonLd = ['@context' => 'https://schema.org', '@graph' => [[
    '@type' => 'ItemList',
    'name' => 'Prolyn video rehberleri',
    'itemListElement' => array_map(static fn($v, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $v['title']], $liste, array_keys($liste)),
]]];
foreach ($liste as $v) {
    $jsonLd['@graph'][] = array_filter([
        '@type' => 'VideoObject',
        'name' => $v['title'],
        'description' => trim((string)$v['description']) !== '' ? $v['description'] : $v['title'],
        'thumbnailUrl' => $v['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . $v['youtube_video_id'] . '/hqdefault.jpg',
        'embedUrl' => 'https://www.youtube-nocookie.com/embed/' . $v['youtube_video_id'],
        'uploadDate' => date('c', strtotime((string)$v['created_at'])),
        'duration' => video_iso_sure($v['sure'] ?? null),
        'inLanguage' => 'tr-TR',
    ]);
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
    <title><?= $e($sayfaBaslik) ?> | <?= $e($appSettings['site_name'] ?? 'Prolyn ERP') ?></title>
    <meta name="description" content="<?= $e($sayfaAciklama) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= $e($base . '/video') ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $e($appSettings['site_name'] ?? 'Prolyn ERP') ?>">
    <meta property="og:title" content="<?= $e($sayfaBaslik) ?>">
    <meta property="og:description" content="<?= $e($sayfaAciklama) ?>">
    <meta property="og:url" content="<?= $e($base . '/video') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <link rel="icon" href="p/dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="p/dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="p/dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800&display=swap" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=13" rel="stylesheet" type="text/css" />
    <link href="assets/css/tema.css?v=5" rel="stylesheet" type="text/css" />
    <link href="assets/css/video.css?v=1" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank">
    <div class="d-flex flex-column flex-root" id="kt_app_root">
<?php $navActive = 'video'; require __DIR__ . '/partials/site_header.php'; ?>

        <!--begin::Hero-->
        <div class="hero-section hero-compact text-center">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up">
                    <span class="blog-pill mb-5 d-inline-block">Video rehberler</span>
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">Tanıtım <span class="text-gradient-primary">Videoları</span></h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 800px;">
                        Prolyn'i videolarla tanıyın: kurulumdan tahsilata, her işlemi birkaç dakikada ekranda görün.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <div class="py-10 py-lg-15">
            <div class="container-xxl">
<?php if (empty($videolar)): ?>
                <div class="text-center text-gray-400 py-20 fs-5">Video rehberlerimiz hazırlanıyor. Çok yakında burada olacak.</div>
<?php else: ?>
<?php if ($kategoriler): ?>
                <nav class="video-chips" aria-label="Video kategorileri">
                    <a href="video.php" class="<?= $secili === '' ? 'on' : '' ?>">Tümü <span><?= count($videolar) ?></span></a>
<?php foreach ($kategoriler as $k => $adet): ?>
                    <a href="video.php?kategori=<?= urlencode($k) ?>" class="<?= $secili === $k ? 'on' : '' ?>"><?= $e($k) ?> <span><?= $adet ?></span></a>
<?php endforeach; ?>
                </nav>
<?php endif; ?>

                <div class="video-layout">
<?php if (count($liste) > 2): ?>
                    <aside class="video-index" aria-label="Hızlı erişim">
                        <div class="video-index-title">Bu sayfada</div>
                        <ol>
<?php foreach ($liste as $v): ?>
                            <li><a href="#video-<?= (int)$v['id'] ?>"><?= $e($v['title']) ?></a></li>
<?php endforeach; ?>
                        </ol>
                    </aside>
<?php endif; ?>

                    <div class="video-list">
<?php foreach ($liste as $i => $v): ?>
                        <?php $kapak = $v['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . $v['youtube_video_id'] . '/hqdefault.jpg'; ?>
                        <section class="glass-card video-item" id="video-<?= (int)$v['id'] ?>">
                            <div class="video-item-head">
                                <span class="video-no"><?= $i + 1 ?></span>
                                <div>
                                    <h2><?= $e($v['title']) ?></h2>
                                    <?php if (trim((string)$v['kategori']) !== '' || trim((string)$v['sure']) !== ''): ?>
                                    <div class="video-meta">
                                        <?php if (trim((string)$v['kategori']) !== ''): ?><span><i class="bi bi-folder2"></i> <?= $e($v['kategori']) ?></span><?php endif; ?>
                                        <?php if (trim((string)$v['sure']) !== ''): ?><span><i class="bi bi-clock"></i> <?= $e($v['sure']) ?></span><?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (trim((string)$v['description']) !== ''): ?><p class="video-desc"><?= nl2br($e($v['description'])) ?></p><?php endif; ?>
                            <button type="button" class="video-player" data-video-id="<?= $e($v['youtube_video_id']) ?>" data-title="<?= $e($v['title']) ?>" aria-label="<?= $e($v['title']) ?> videosunu oynat">
                                <img src="<?= $e($kapak) ?>" alt="" loading="<?= $i < 2 ? 'eager' : 'lazy' ?>" width="640" height="360">
                                <span class="video-play"><i class="bi bi-play-fill"></i></span>
                            </button>
                        </section>
<?php endforeach; ?>
<?php if (!$liste): ?>
                        <div class="text-center text-gray-400 py-10">Bu kategoride henüz video yok.</div>
<?php endif; ?>
                    </div>
                </div>

                <div class="blog-cta mt-15">
                    <div>
                        <h2>Daha fazla bilgi mi istiyorsunuz?</h2>
                        <p>Firmanıza özel canlı bir tanıtım için bize yazın ya da 30 gün ücretsiz deneyerek kendiniz görün.</p>
                    </div>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="contact.php" class="btn btn-outline btn-outline-light px-7 py-3 fw-bold">İletişime geç</a>
                        <a href="register.php" class="btn btn-primary-glow px-7 py-3 fw-bold">Ücretsiz dene</a>
                    </div>
                </div>
<?php endif; ?>
            </div>
        </div>

<?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>

<?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <script src="assets/js/site.js?v=6"></script>
    <script>
    // Oynatıcı yalnızca tıklanınca yüklenir. Video ID'si sunucudan gelir; yine de biçimi doğrulanır.
    document.querySelectorAll('.video-player').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-video-id') || '';
            if (!/^[A-Za-z0-9_-]{11}$/.test(id)) { return; }
            var f = document.createElement('iframe');
            f.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&rel=0&modestbranding=1';
            f.title = btn.getAttribute('data-title') || 'Prolyn video';
            f.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen';
            f.allowFullscreen = true;
            f.referrerPolicy = 'strict-origin-when-cross-origin';
            var kutu = document.createElement('div');
            kutu.className = 'video-player is-playing';
            kutu.appendChild(f);
            btn.replaceWith(kutu);
        }, { once: true });
    });
    </script>

<?php require __DIR__ . '/partials/cookie_banner.php'; ?>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
