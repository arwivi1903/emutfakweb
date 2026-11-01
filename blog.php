<?php require_once 'db.php'; ?>
<?php
/**
 * Prolyn Blog - liste: kategori süzgeci, arama ve sayfalama.
 * Liste sorgusu içerik metnini çekmez (yalnızca uzunluğunu); okuma süresi bundan tahmin edilir.
 */
require_once __DIR__ . '/partials/blog_render.php';

const BLOG_SAYFA_BOYUTU = 9;
$base = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');

$kategori = mb_substr(trim((string)($_GET['kategori'] ?? '')), 0, 60);
$arama = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 80);
$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));

$kategoriler = [];   // ad => yazı sayısı
$kategoriDestegi = true;
try {
    $kategoriler = $pdo->query("SELECT kategori, COUNT(*) AS adet FROM blog_yazilari
                                WHERE status = 'published' AND kategori IS NOT NULL AND kategori <> ''
                                GROUP BY kategori ORDER BY adet DESC, kategori ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {
    // Migrasyon (2026_10_31_blog_video_gelistirme.php) henüz çalışmadıysa kategorisiz çalış.
    $kategoriDestegi = false;
    error_log('[prolynweb] blog kategori sutunu yok: ' . $e->getMessage());
}
if ($kategori !== '' && !isset($kategoriler[$kategori])) { $kategori = ''; }

// Dinamik parçalar yalnızca sabit SQL metinleridir; kullanıcı girdisi her zaman bağlı parametredir.
$kosul = ["status = 'published'"];
$par = [];
if ($kategori !== '') { $kosul[] = 'kategori = ?'; $par[] = $kategori; }
if ($arama !== '') {
    $like = '%' . addcslashes($arama, '\\%_') . '%';
    $kosul[] = '(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
    array_push($par, $like, $like, $like);
}
$where = implode(' AND ', $kosul);
$sec = $kategoriDestegi ? 'kategori' : 'NULL AS kategori';

$toplam = 0; $posts = [];
try {
    $st = $pdo->prepare("SELECT COUNT(*) FROM blog_yazilari WHERE $where");
    $st->execute($par);
    $toplam = (int)$st->fetchColumn();
    $sayfaSayisi = max(1, (int)ceil($toplam / BLOG_SAYFA_BOYUTU));
    $sayfa = min($sayfa, $sayfaSayisi);
    $ofset = ($sayfa - 1) * BLOG_SAYFA_BOYUTU;
    $st = $pdo->prepare("SELECT title, slug, excerpt, $sec, cover_image, published_at, CHAR_LENGTH(content) AS uzunluk
                         FROM blog_yazilari WHERE $where ORDER BY published_at DESC, id DESC LIMIT " . BLOG_SAYFA_BOYUTU . " OFFSET " . (int)$ofset);
    $st->execute($par);
    $posts = $st->fetchAll();
} catch (Throwable $e) {
    error_log('[prolynweb] blog listesi okunamadi: ' . $e->getMessage());
    $sayfaSayisi = 1;
}

$filtreli = ($kategori !== '' || $arama !== '');
$ilkSayfa = ($sayfa === 1 && !$filtreli);
$one = ($ilkSayfa && $posts) ? array_shift($posts) : null;

/** Süzgeçleri koruyarak liste adresi üretir. */
function blog_url(string $kategori, string $arama, int $sayfa = 1): string
{
    $q = array_filter(['kategori' => $kategori, 'q' => $arama, 'sayfa' => $sayfa > 1 ? $sayfa : null], static fn($v) => $v !== '' && $v !== null);
    return 'blog.php' . ($q ? '?' . http_build_query($q) : '');
}
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$baslik = $kategori !== '' ? $kategori . ' yazıları' : 'Blog';
$aciklama = $kategori !== ''
    ? "Prolyn blogu, $kategori: mutfak, dolap ve banyo firmaları başta olmak üzere KOBİ'ler için uygulanabilir rehberler."
    : "Prolyn blogu: mutfak, dolap ve banyo firmaları başta olmak üzere KOBİ'ler için randevu, montaj, tahsilat ve ön muhasebe üzerine uygulanabilir rehberler.";
$canonical = $base . '/' . blog_url($kategori, '', $sayfa);
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
    <title><?= $e($baslik) ?><?= $sayfa > 1 ? ' - Sayfa ' . $sayfa : '' ?> | <?= $e($appSettings['site_name'] ?? 'Prolyn ERP') ?></title>
    <meta name="description" content="<?= $e($aciklama) ?>">
    <meta name="robots" content="<?= $arama !== '' ? 'noindex, follow' : 'index, follow' ?>">
    <link rel="canonical" href="<?= $e($canonical) ?>">
<?php if ($sayfa > 1): ?>    <link rel="prev" href="<?= $e($base . '/' . blog_url($kategori, '', $sayfa - 1)) ?>">
<?php endif; ?>
<?php if ($sayfa < ($sayfaSayisi ?? 1)): ?>    <link rel="next" href="<?= $e($base . '/' . blog_url($kategori, '', $sayfa + 1)) ?>">
<?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $e($appSettings['site_name'] ?? 'Prolyn ERP') ?>">
    <meta property="og:title" content="<?= $e($baslik) ?>">
    <meta property="og:description" content="<?= $e($aciklama) ?>">
    <meta property="og:url" content="<?= $e($canonical) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="p/dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="p/dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="p/dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800&display=swap" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=15" rel="stylesheet" type="text/css" />
    <link href="assets/css/tema.css?v=7" rel="stylesheet" type="text/css" />
    <link href="assets/css/blog.css?v=4" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank">
    <div class="d-flex flex-column flex-root" id="kt_app_root">
<?php $navActive = 'blog'; require __DIR__ . '/partials/site_header.php'; ?>

        <!--begin::Hero-->
        <div class="hero-section hero-compact text-center">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up">
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">Prolyn <span class="text-gradient-primary">Blog</span></h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-8 mx-auto" style="max-width: 800px;">
                        Randevu, montaj, tahsilat ve ön muhasebe üzerine sahada işe yarayan rehberler.
                    </div>
                    <form action="blog.php" method="get" class="blog-search" role="search">
                        <?php if ($kategori !== ''): ?><input type="hidden" name="kategori" value="<?= $e($kategori) ?>"><?php endif; ?>
                        <label class="visually-hidden" for="blog-q">Blogda ara</label>
                        <input id="blog-q" type="search" name="q" value="<?= $e($arama) ?>" maxlength="80" placeholder="Blogda ara: taksit, çek, randevu…">
                        <button type="submit" aria-label="Ara"><i class="bi bi-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <div class="py-10 py-lg-15">
            <div class="container-xxl">
<?php if ($kategoriler): ?>
                <nav class="blog-cats" aria-label="Blog kategorileri">
                    <a href="blog.php" class="<?= $kategori === '' ? 'on' : '' ?>">Tümü</a>
<?php foreach ($kategoriler as $k => $adet): ?>
                    <a href="<?= $e(blog_url($k, '')) ?>" class="<?= $kategori === $k ? 'on' : '' ?>"><?= $e($k) ?> <span><?= (int)$adet ?></span></a>
<?php endforeach; ?>
                </nav>
<?php endif; ?>
<?php if ($arama !== ''): ?>
                <p class="blog-result-info"><b>“<?= $e($arama) ?>”</b> için <?= $toplam ?> sonuç<?= $kategori !== '' ? ' (' . $e($kategori) . ')' : '' ?>. <a href="<?= $e(blog_url($kategori, '')) ?>">Aramayı temizle</a></p>
<?php endif; ?>

<?php if (!$one && !$posts): ?>
                <div class="text-center text-gray-400 py-20 fs-5">
                    <?= $filtreli ? 'Bu süzgece uyan yazı bulunamadı.' : 'Henüz yayınlanmış bir yazı bulunmuyor. Yakında burada olacak.' ?>
                    <?php if ($filtreli): ?><div class="mt-5"><a href="blog.php" class="btn btn-primary-glow px-6 py-2">Tüm yazılar</a></div><?php endif; ?>
                </div>
<?php else: ?>
<?php if ($one): ?>
                <a href="blog_detail.php?slug=<?= urlencode($one['slug']) ?>" class="glass-card blog-featured scroll-reveal">
                    <div class="blog-featured-art" <?= !empty($one['cover_image']) ? 'style="background-image:url(\'' . $e($one['cover_image']) . '\')"' : '' ?>>
                        <?php if (empty($one['cover_image'])): ?><span>Prolyn<i>.</i></span><?php endif; ?>
                    </div>
                    <div class="blog-featured-body">
                        <span class="blog-pill"><?= $e(trim((string)$one['kategori']) !== '' ? $one['kategori'] : 'Son yazı') ?></span>
                        <h2><?= $e($one['title']) ?></h2>
                        <?php if (!empty($one['excerpt'])): ?><p><?= $e($one['excerpt']) ?></p><?php endif; ?>
                        <span class="blog-card-meta"><?= date('d.m.Y', strtotime($one['published_at'])) ?> · <?= blog_okuma_suresi_uzunluk((int)$one['uzunluk']) ?> dk okuma</span>
                        <span class="blog-more">Yazıyı oku <i class="bi bi-arrow-right"></i></span>
                    </div>
                </a>
<?php endif; ?>
<?php if ($posts): ?>
                <div class="row g-6 <?= $one ? 'mt-2' : '' ?>">
<?php foreach ($posts as $i => $post): ?>
                    <div class="col-md-6 col-lg-4 scroll-reveal delay-<?= ($i % 3) + 1 ?>">
                        <a href="blog_detail.php?slug=<?= urlencode($post['slug']) ?>" class="glass-card blog-card h-100">
                            <?php if (!empty($post['cover_image'])): ?>
                                <img src="<?= $e($post['cover_image']) ?>" class="blog-card-img" alt="<?= $e($post['title']) ?>" loading="lazy" />
                            <?php endif; ?>
                            <?php if (trim((string)$post['kategori']) !== ''): ?><span class="blog-pill blog-pill-sm"><?= $e($post['kategori']) ?></span><?php endif; ?>
                            <span class="blog-card-meta"><?= date('d.m.Y', strtotime($post['published_at'])) ?> · <?= blog_okuma_suresi_uzunluk((int)$post['uzunluk']) ?> dk okuma</span>
                            <h3><?= $e($post['title']) ?></h3>
                            <?php if (!empty($post['excerpt'])): ?><p><?= $e(mb_strimwidth($post['excerpt'], 0, 170, '…')) ?></p><?php endif; ?>
                            <span class="blog-more">Devamını oku <i class="bi bi-arrow-right"></i></span>
                        </a>
                    </div>
<?php endforeach; ?>
                </div>
<?php endif; ?>

<?php if (($sayfaSayisi ?? 1) > 1): ?>
                <nav class="blog-pager" aria-label="Sayfalama">
<?php if ($sayfa > 1): ?><a href="<?= $e(blog_url($kategori, $arama, $sayfa - 1)) ?>" rel="prev" aria-label="Önceki sayfa"><i class="bi bi-chevron-left"></i></a><?php endif; ?>
<?php for ($n = 1; $n <= $sayfaSayisi; $n++): ?>
                    <a href="<?= $e(blog_url($kategori, $arama, $n)) ?>" class="<?= $n === $sayfa ? 'on' : '' ?>" <?= $n === $sayfa ? 'aria-current="page"' : '' ?>><?= $n ?></a>
<?php endfor; ?>
<?php if ($sayfa < $sayfaSayisi): ?><a href="<?= $e(blog_url($kategori, $arama, $sayfa + 1)) ?>" rel="next" aria-label="Sonraki sayfa"><i class="bi bi-chevron-right"></i></a><?php endif; ?>
                </nav>
<?php endif; ?>
<?php endif; ?>

                <div class="blog-cta mt-15">
                    <div>
                        <h2>Videolarla de öğrenebilirsiniz</h2>
                        <p>Kurulum, sözleşme, randevu ve tahsilat adımlarını kısa video rehberlerle izleyin.</p>
                    </div>
                    <a href="video.php" class="btn btn-primary-glow px-7 py-3 fw-bold"><i class="bi bi-play-circle me-2"></i>Videolara git</a>
                </div>
            </div>
        </div>

<?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>

<?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <script src="assets/js/site.js?v=6"></script>

<?php require __DIR__ . '/partials/cookie_banner.php'; ?>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
