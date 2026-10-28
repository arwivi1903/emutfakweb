<?php
require_once 'db.php';
require_once __DIR__ . '/partials/paket_veri.php';
require_once __DIR__ . '/partials/modul_icerik.php';
$packages = prolynweb_paketler($pdo);
$moduller = prolynweb_modul_icerik();
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light" data-tema="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require __DIR__ . '/partials/tema_head.php'; ?>
    <title>Prolyn Modül Detayları: Her Modül Ne Yapar, Hangi Pakette?</title>
    <meta name="description"
        content="<?php echo htmlspecialchars('Prolyn modülleri tek tek: müşteri, randevu, sözleşme, cari, kasa, çek, banka, personel giderleri, raporlar ve daha fazlası. Neyin hangi pakette olduğunu görün.'); ?>">
    <meta name="keywords"
        content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? 'erp, bulut erp, crm, ön muhasebe, ik'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title"
        content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn - Yeni Nesil Bulut ERP Sistemi'); ?> - Modüller">
    <meta property="og:description"
        content="<?php echo htmlspecialchars('Prolyn modülleri tek tek: müşteri, randevu, sözleşme, cari, kasa, çek, banka, personel giderleri, raporlar ve daha fazlası. Neyin hangi pakette olduğunu görün.'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="p/dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="p/dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="p/dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800&display=swap" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css?v=12" rel="stylesheet" type="text/css" />
    <link href="assets/css/tema.css?v=2" rel="stylesheet" type="text/css" />
    <link href="assets/css/modul.css?v=1" rel="stylesheet" type="text/css" />
</head>

<body id="kt_body" class="app-blank">
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <?php $navActive = 'moduller'; require __DIR__ . '/partials/site_header.php'; ?>

        <!--begin::Hero-->
        <div class="hero-section hero-compact">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up" style="max-width:760px">
                    <h1 class="pw-h fw-bolder fs-2hx fs-lg-3x mb-5" style="color:var(--pw-ink)">Prolyn modülleri tek tek
                    </h1>
                    <p class="bolum-giris" style="max-width:100%">Her modülün ne yaptığını, hangi rolün kullandığını ve
                        hangi pakette
                        açıldığını burada bulabilirsiniz. <br>Çalışma mantığını görmek için <a
                            href="index.php#ozellikler" style="color:var(--pw-link);text-decoration:underline">ana
                            sayfadaki modül anlatımlarına</a>
                        da göz atabilirsiniz.
                    </p>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <div class="mod-nav-sar" aria-label="Modüller">
            <div class="container-xxl">
                <nav class="mod-nav" aria-label="Modüllere git">
                    <a href="#paket-tablosu" style="--m:var(--pw-primary)"><i></i>Paket tablosu</a>
                    <?php foreach ($moduller as $m): ?>
                    <a href="#<?= $m['slug'] ?>" data-mod="<?= $m['slug'] ?>"
                        style="--m:var(--m-<?= $m['renk'] ?>)"><i></i><?= htmlspecialchars($m['ad']) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>

        <!--begin::Paket tablosu-->
        <div class="py-12" id="paket-tablosu" style="scroll-margin-top:130px">
            <div class="container-xxl">
                <h2 class="bolum-baslik" style="font-size:1.8rem">Hangi pakette neler var?</h2>
                <p class="bolum-giris mb-8">Aşağıdaki tablo, paketlerde açılan modülleri ve limitleri gösterir.
                    "Kısmen", o bölümün bir kısmının pakette bulunduğu anlamına gelir.</p>
                <?php require __DIR__ . '/partials/paket_matris.php'; ?>
            </div>
        </div>
        <!--end::Paket tablosu-->

        <!--begin::Modül rehberi-->
        <div class="pb-15">
            <div class="container-xxl">
                <div class="rehber">
                    <?php foreach ($moduller as $m): ?>
                    <article class="rehber-kart" id="<?= $m['slug'] ?>" style="--m:var(--m-<?= $m['renk'] ?>)">
                        <div class="rehber-ust">
                            <figure class="ill m-0"><?php require __DIR__ . '/partials/ill/' . $m['slug'] . '.php'; ?>
                            </figure>
                            <div>
                                <h2><?= htmlspecialchars($m['ad']) ?><?php if ($m['durum'] === 'kismen'): ?> <span
                                        class="mod-rozet">Bir kısmı yakında</span><?php endif; ?></h2>
                                <p><?= htmlspecialchars($m['ozet']) ?></p>
                                <?php if ($m['durum'] === 'kismen'): ?><p>
                                    <b><?= htmlspecialchars($m['durum_not']) ?></b>
                                </p><?php endif; ?>
                            </div>
                        </div>
                        <div class="rehber-listeler">
                            <div>
                                <h3>Ne yapar?</h3>
                                <ul>
                                    <?php foreach ($m['maddeler'] as $md): ?><li><b><?= htmlspecialchars($md[0]) ?>.</b>
                                        <?= htmlspecialchars($md[1]) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                            <div>
                                <h3>Ayrıca</h3>
                                <ul>
                                    <?php foreach ($m['ayrinti'] as $ay): ?><li><?= htmlspecialchars($ay) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="rehber-alt">
                            <span><b>Kimler kullanır:</b> <?= htmlspecialchars($m['kimler']) ?></span>
                            <span class="mod-paket" style="gap:.4rem 1rem"><b class="mod-paket-etiket">Hangi
                                    pakette?</b>
                                <?php foreach ($packages as $pk): $d = prolynweb_pakette_var($pk, $m['anahtarlar']); ?>
                                <span
                                    class="pk-d <?= $d ?>"><?= $d === 'yok' ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 12h12"/></svg>' : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>' ?><?= htmlspecialchars($pk['name']) ?><?= $d === 'kismen' ? ' (kısmen)' : ($d === 'yok' ? ' (yok)' : '') ?></span>
                                <?php endforeach; ?>
                            </span>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-15">
                    <h2 class="bolum-baslik" style="font-size:1.8rem">Kendi işinizde deneyin</h2>
                    <p class="bolum-giris mx-auto mb-8">30 gün boyunca seçtiğiniz paketi ücretsiz kullanın; kredi kartı
                        gerekmez.</p>
                    <a href="register.php" class="btn btn-primary-glow btn-lg px-10">30 Gün Ücretsiz Dene</a>
                </div>
            </div>
        </div>
        <!--end::Modül rehberi-->

        <?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>
    <!--end::Root-->

    <?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

    <script src="assets/js/site.js?v=7"></script>
    <?php require __DIR__ . '/partials/cookie_banner.php'; ?>
    <?php require __DIR__ . '/partials/fab.php'; ?>
</body>

</html>