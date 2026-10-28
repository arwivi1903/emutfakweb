<?php require_once 'db.php'; ?>
<?php
require_once __DIR__ . '/partials/paket_veri.php';
require_once __DIR__ . '/partials/modul_icerik.php';
$packages = prolynweb_paketler($pdo);
$faqItems = [
    ['Prolyn nedir?', 'Prolyn, işletmeler için geliştirilmiş bulut tabanlı bir ERP ve ön muhasebe yazılımıdır. Müşteri (CRM), randevu, cari hesap, kasa ve banka, sözleşme, personel ve destek süreçlerini tek panelden yönetmenizi sağlar. Her firmanın verisi kendi ayrı veritabanında tutulur ve ilk 30 gün ücretsiz denenebilir.'],
    ['Prolyn kimler için uygun?', 'Müşteri, randevu, kasa, cari ve çek takibini bir arada yapmak isteyen küçük ve orta ölçekli işletmeler için. Şu an en çok mutfak, dolap ve banyo firmalarının ölçü, montaj, kapora, taksit ve çek süreçlerine uygun şekilde kullanılıyor; farklı bir sektördeyseniz 30 günlük denemeyle kendi işinize uyup uymadığını görebilirsiniz.'],
    ['Excel veya defter tutmaktan ne farkı var?', 'Müşteri, randevu, sözleşme, ödeme ve cari kayıtları ayrı dosyalarda değil tek panelde ve birbirine bağlı tutulur. Bir müşterinin borcunu, taksitini ve randevusunu aynı yerden görürsünüz; aramak, toplamak ve el ile eşleştirmek zorunda kalmazsınız.'],
    ['Prolyn hangi modülleri içerir?', 'Müşteri ve cari yönetimi, randevu ve servis takibi, kasa ve banka hareketleri, çek ve sözleşme yönetimi, personel giderleri, raporlama ve işlem kayıtları ile destek talebi modülü Prolyn ile birlikte gelir. Kullanabileceğiniz modüller seçtiğiniz pakete göre belirlenir; güncel liste Modüller sayfasında yer alır.'],
    ['30 günlük deneme sürümünde kısıtlama var mı?', 'Hayır. Deneme sürecinde de kendi ayrı veri alanınızla, seçtiğiniz pakete uygun modüllerle çalışırsınız. Kredi kartı istemiyoruz; süre sonunda dilerseniz ücretli pakete geçersiniz, dilerseniz vazgeçersiniz. Aynı vergi numarası veya T.C. kimlik numarasıyla bir kez deneme hesabı açılabilir.'],
    ['Verilerimiz başka firmalarla aynı yerde mi tutuluyor?', 'Hayır. Her firma için ayrı bir veritabanı oluşturulur; verileriniz başka bir firmayla aynı tabloyu paylaşmaz ve başka firmaların kullanıcıları tarafından görülemez.'],
    ['Kişisel verilerim ve KVKK nasıl ele alınıyor?', 'Kayıt sırasında verdiğiniz bilgiler yalnızca hizmeti sunmak, faturalandırma ve güvenlik amacıyla işlenir. Hangi verinin neden işlendiğini, saklama sürelerini ve haklarınızı sayfa altındaki KVKK Aydınlatma Metni, Gizlilik Politikası ve Çerez Politikası sayfalarında bulabilirsiniz.'],
    ['Paketler arasındaki fark nedir?', 'Paketler kullanıcı sayısı, şube sayısı ve açılan modüllerde ayrışır. Hangi pakette hangi modülün bulunduğunu bu sayfadaki karşılaştırma tablosunda ve her modül bölümünün altındaki "Hangi pakette?" satırında görebilirsiniz. Fiyatlandırma sayfasındaki tutarlara %20 KDV dahildir.'],
    ['Telefondan ve tabletten kullanabilir miyiz?', 'Prolyn web tabanlıdır; bilgisayar, tablet ve telefon tarayıcısından kurulum gerektirmeden açılır. Her personel kendi kullanıcısıyla yalnızca yetkili olduğu ekranları görür.'],
    ['Stok takibi var mı?', 'Ürün kartları ve montajda eksik çıkan malzemenin takibi bugün kullanılabilir. Depodaki miktarı izleyen ayrıntılı stok modülü geliştirme aşamasındadır.'],
    ['Paketler arasında sonradan geçiş yapabilir miyim?', 'Evet. Paket yükseltme talebinizi bize iletmeniz yeterli; ek bir kurulum sürecine gerek kalmadan paketiniz güncellenir.'],
    ['Mevcut sistemimizdeki verileri Prolyn\'e aktarabilir miyiz?', 'Kayıt sonrası kurulum ekibimiz sizinle iletişime geçerek mevcut müşteri ve cari verilerinizin aktarım sürecini birlikte planlar.'],
    ['Destek talebi açtığımda ne kadar sürede dönüş alırım?', 'Paketinize göre değişen öncelik seviyelerimiz var; panel üzerinden açtığınız destek talepleri ekibimiz tarafından takip edilip yanıtlanır.'],
];
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light" data-tema="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
    <title>
        <?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn | Bulut ERP ve Ön Muhasebe Yazılımı - 30 Gün Ücretsiz'); ?>
    </title>
    <meta name="description"
        content="<?php echo htmlspecialchars($appSettings['seo_description'] ?? 'Prolyn; müşteri, randevu, cari hesap, kasa, çek ve sözleşme takibini tek panelde toplayan bulut tabanlı ERP ve ön muhasebe yazılımıdır. İlk 30 gün ücretsiz.'); ?>">
    <meta name="keywords"
        content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? 'erp, bulut erp, crm, ön muhasebe, ik'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title"
        content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn | Bulut ERP ve Ön Muhasebe Yazılımı - 30 Gün Ücretsiz'); ?>">
    <meta property="og:description"
        content="<?php echo htmlspecialchars($appSettings['seo_description'] ?? 'Prolyn; müşteri, randevu, cari hesap, kasa, çek ve sözleşme takibini tek panelde toplayan bulut tabanlı ERP ve ön muhasebe yazılımıdır. İlk 30 gün ücretsiz.'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php
    $ldBase  = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');
    $ldName  = $appSettings['site_name'] ?? 'Prolyn ERP';
    $ldMail  = trim((string)($appSettings['contact_email'] ?? '')) ?: 'info@prolyn.net';
    $ldPhone = trim((string)($appSettings['contact_phone'] ?? ''));
    $ldOrg = [
        '@type' => 'Organization', '@id' => $ldBase . '/#org', 'name' => $ldName, 'url' => $ldBase,
        'logo' => $ldBase . '/p/dist/logo/icon-512x512.png',
        'email' => $ldMail,
        'sameAs' => array_values(array_filter([$appSettings['social_twitter'] ?? null, $appSettings['social_linkedin'] ?? null, $appSettings['social_instagram'] ?? 'https://www.instagram.com/echolab2/', $appSettings['social_youtube'] ?? 'https://www.youtube.com/@echoLabs2'])),
    ];
    if ($ldPhone !== '') {
        $ldOrg['contactPoint'] = [['@type' => 'ContactPoint', 'contactType' => 'customer support', 'telephone' => $ldPhone, 'email' => $ldMail, 'availableLanguage' => 'Turkish']];
    }
    $ldFaq = [];
    foreach ($faqItems as $fq) {
        $ldFaq[] = ['@type' => 'Question', 'name' => $fq[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $fq[1]]];
    }
    $ldGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $ldOrg,
            ['@type' => 'WebSite', '@id' => $ldBase . '/#website', 'url' => $ldBase, 'name' => $ldName, 'inLanguage' => 'tr-TR', 'publisher' => ['@id' => $ldBase . '/#org']],
            ['@type' => 'SoftwareApplication', 'name' => $ldName, 'applicationCategory' => 'BusinessApplication',
             'operatingSystem' => 'Web', 'inLanguage' => 'tr-TR', 'url' => $ldBase,
             'description' => $appSettings['seo_description'] ?? 'Müşteri, randevu, cari, kasa ve çek takibini tek panelde toplayan bulut tabanlı ERP ve ön muhasebe yazılımı.',
             'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'TRY', 'description' => '30 gün ücretsiz deneme'],
             'publisher' => ['@id' => $ldBase . '/#org']],
            ['@type' => 'FAQPage', 'mainEntity' => $ldFaq],
        ],
    ];
    ?>
    <script type="application/ld+json">
    <?php echo json_encode($ldGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?>
    </script>
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
        <?php $navActive = ''; require __DIR__ . '/partials/site_header.php'; ?>

        <!--begin::Hero-->
        <div class="hero-section hero-home">
            <div class="hero-glow-1"></div>
            <div class="hero-glow-2"></div>

            <div class="container-xxl position-relative z-index-2">
                <div class="row align-items-center g-10">
                    <div class="col-lg-6 text-center text-lg-start">
                        <div class="animate-fade-in-up">
                            <span class="hero-eyebrow">Bulut tabanlı ERP ve ön muhasebe yazılımı</span>

                            <h1 class="fs-3x fs-lg-4x mb-6">
                                Müşteri, randevu, kasa ve cari takibini tek panelden yönetin
                            </h1>

                            <p class="hero-lead mb-8 mx-auto mx-lg-0">
                                Müşteriden ölçü randevusuna, sözleşmeden taksit ve çeke, ana kasadan personel giderine kadar
                                işin her adımı birbirine bağlı kayıtlarla ilerler. Her firmanın verisi kendi ayrı
                                veritabanında tutulur; ilk 30 gün ücretsiz denenebilir.
                            </p>

                            <ul class="hero-audience justify-content-center justify-content-lg-start"
                                aria-label="Kimler için">
                                <li>Mutfak, dolap ve banyo firmaları</li>
                                <li>Ölçü, montaj ve servis randevusu olan işletmeler</li>
                                <li>Kapora, taksit ve çek takibi yapan firmalar</li>
                            </ul>

                            <div class="d-flex justify-content-center justify-content-lg-start gap-4 flex-wrap mb-8">
                                <a href="register.php" class="btn btn-primary-glow btn-lg px-8">30 Gün Ücretsiz Dene</a>
                                <a href="#ozellikler" class="btn btn-outline btn-outline-dashed btn-outline-default btn-lg">Modülleri İncele</a>
                            </div>

                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-5 text-gray-400 fs-6 fw-semibold flex-wrap">
                                <div class="d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-2"></i>Kredi kartı gerekmez</div>
                                <div class="d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-2"></i>Program kurulumu yok</div>
                                <div class="d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-2"></i>Her firmaya ayrı veritabanı</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="yol" role="group" aria-label="Örnek: bir işin Prolyn'deki yolu">
                            <div class="yol-ust">
                                <div>
                                    <h2 class="yol-baslik">Mutfak ve dolap işi · Kartepe</h2>
                                    <p class="yol-alt-baslik">Bir işin Prolyn'deki yolu</p>
                                </div>
                                <span class="yol-durum">Devam ediyor</span>
                            </div>
                            <ol class="yol-liste">
                                <li style="--i:0;--m:var(--m-musteri)"><span class="yol-ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 15-5 16 0"/></svg></span><span class="yol-metin"><b>Müşteri kartı açıldı</b><small>A. Yılmaz · Kartepe</small></span><span class="yol-tik"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span></li>
                                <li style="--i:1;--m:var(--m-randevu)"><span class="yol-ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M3 10h18M8 3v4M16 3v4"/></svg></span><span class="yol-metin"><b>Randevu planlandı</b><small>Salı 09:00 · Ekip 1</small></span><span class="yol-tik"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span></li>
                                <li style="--i:2;--m:var(--m-sozlesme)"><span class="yol-ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h8l4 4v14H6z"/><path d="M9 12h6M9 16h6"/></svg></span><span class="yol-metin"><b>Sözleşme ve taksit planı</b><small>₺120.000 · kapora + 6 taksit</small></span><span class="yol-tik"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span></li>
                                <li style="--i:3;--m:var(--m-cek)"><span class="yol-ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h6M6 14h4M15 14c1-2 2-2 3 0"/></svg></span><span class="yol-metin"><b>Kapora çekle alındı</b><small>₺30.000 · çek kaydı</small></span><span class="yol-tik"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span></li>
                                <li style="--i:4;--m:var(--m-kasa)"><span class="yol-ikon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M3 10h18M8 15h3"/></svg></span><span class="yol-metin"><b>Ana kasaya işlendi</b><small>Çek bakiyesi güncellendi</small></span><span class="yol-tik"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span></li>
                            </ol>
                            <div class="yol-dip">
                                <div class="yol-dip-satir"><span>Tahsil edilen</span><b>₺30.000 / ₺120.000</b></div>
                                <div class="yol-iz" aria-hidden="true"><i></i></div>
                            </div>
                            <p class="yol-not">Örnek ekran · müşteri ve tutarlar temsilidir</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Quick facts-->
        <div class="gercek-bant mt-10">
            <div class="container-xxl">
                <div class="row">
                    <div class="col-6 col-lg-3 gercek"><b>30 gün</b><span>ücretsiz deneme, kredi kartı istenmez</span></div>
                    <div class="col-6 col-lg-3 gercek"><b>8 rol</b><span>her çalışan yalnızca kendi ekranlarını görür</span></div>
                    <div class="col-6 col-lg-3 gercek"><b>Ayrı veritabanı</b><span>her firmanın verisi kendi alanında</span></div>
                    <div class="col-6 col-lg-3 gercek"><b>Kurulum yok</b><span>tarayıcıdan bilgisayar, tablet ve telefonda</span></div>
                </div>
            </div>
        </div>
        <!--end::Quick facts-->

        <!--begin::Problem-->
        <div id="sorun" class="py-15 py-lg-20">
            <div class="container-xxl">
                <div class="bolum-bas orta">
                    <h2 class="bolum-baslik">Prolyn hangi sorunu çözer?</h2>
                    <p class="bolum-giris">Müşteri Excel'de, randevu WhatsApp'ta, borçlar defterde... Kayıtlar dağınık olunca kimin ne kadar borcu olduğunu, hangi çekin ne zaman vadesinin geldiğini bulmak zaman alır. Prolyn bunları tek yerde, birbirine bağlı tutar.</p>
                </div>
                <div class="sorun-sar">
                    <div class="sorun"><s>Müşteri bir dosyada, ödeme başka dosyada</s><h3>Müşteri, sözleşme ve ödeme bir arada</h3><p>Bir müşterinin kartında sözleşmesini, randevularını, ödemelerini ve kalan borcunu birlikte görürsünüz.</p></div>
                    <div class="sorun"><s>Kim ne kadar borçlu, elle hesaplanıyor</s><h3>Cari hesap ve tahsilat takibi</h3><p>Müşteri bakiyelerini, kapora ve taksit ödemelerini anlık takip edersiniz.</p></div>
                    <div class="sorun"><s>Çek ve taksit vadeleri not kâğıdında</s><h3>Çek, kasa ve banka takibi</h3><p>Vadesi gelen taksitleri izler, kasa ve banka hareketlerini tek ekranda görürsünüz.</p></div>
                    <div class="sorun"><s>Ölçü ve montaj günleri karışıyor</s><h3>Randevu ve montaj planı</h3><p>Ölçü, montaj ve servis randevularını takvimde planlar, eksik malzemeleri takip edersiniz.</p></div>
                </div>
            </div>
        </div>
        <!--end::Problem-->

        <!--begin::Modüller-->
        <div id="ozellikler" class="pb-5" style="scroll-margin-top:90px">
            <div class="container-xxl pt-5">
                <div class="bolum-bas orta mb-10">
                    <h2 class="bolum-baslik">Prolyn'in içinde neler var?</h2>
                    <p class="bolum-giris">İşinizin her adımı için ayrı bir modül var. Aşağıda her birinin ne yaptığını, kimin kullandığını ve hangi pakette bulunduğunu görebilirsiniz.</p>
                </div>
            </div>
            <?php require __DIR__ . '/partials/modul_bolumleri.php'; ?>
        </div>
        <!--end::Modüller-->

        <!--begin::Paketler-->
        <div id="paketler" class="py-15 py-lg-20" style="background:var(--pw-bg-alt);scroll-margin-top:90px">
            <div class="container-xxl">
                <div class="bolum-bas orta">
                    <h2 class="bolum-baslik">Ne satın alıyorsunuz?</h2>
                    <p class="bolum-giris">Üç paket var. Hepsinde aynı uygulama, ayrı veritabanı ve kullanıcı rolleri bulunur; paketler kullanıcı sayısı, şube sayısı ve açılan modüllerde ayrışır. 30 gün boyunca seçtiğiniz paketi ücretsiz kullanırsınız.</p>
                </div>
                <div class="pk-sar">
                    <?php foreach ($packages as $pk):
                        $one = ($pk['code'] === 'pro');
                        $modSayi = count(array_diff($pk['modules'], ['core']));
                        $destekAd = ['email' => 'E-posta desteği', 'priority' => 'Öncelikli destek', 'dedicated' => 'Özel destek'];
                    ?>
                    <div class="pk-kart<?= $one ? ' one' : '' ?>">
                        <?php if ($one): ?><span class="pk-onerilen">En çok tercih edilen</span><?php endif; ?>
                        <h3 class="pk-ad"><?= htmlspecialchars($pk['name']) ?></h3>
                        <p class="pk-acik"><?= htmlspecialchars($pk['description']) ?></p>
                        <div class="pk-fiyat"><span>₺</span><b><?= prolynweb_tl($pk['yearly_total']) ?></b><span>/ yıl</span></div>
                        <p class="pk-not">Aylık ≈ ₺<?= prolynweb_tl($pk['monthly_approx']) ?><?php if ($pk['yearly_discount'] > 0): ?> · <s>₺<?= prolynweb_tl($pk['list_yearly']) ?></s> yerine %<?= (int)$pk['yearly_discount'] ?> yıllık indirimle<?php endif; ?></p>
                        <ul class="pk-ozet">
                            <li><b><?= $pk['max_users'] > 0 ? (int)$pk['max_users'] : 'Sınırsız' ?></b>&nbsp;kullanıcı</li>
                            <li><b><?= $pk['max_branches'] > 0 ? (int)$pk['max_branches'] : 'Sınırsız' ?></b>&nbsp;şube</li>
                            <li><b><?= $modSayi ?></b>&nbsp;modül</li>
                            <li><?= htmlspecialchars($destekAd[$pk['support'] ?? 'email'] ?? 'Teknik destek') ?></li>
                        </ul>
                        <a href="register.php?plan=<?= urlencode($pk['code']) ?>&amp;periyot=yillik" class="btn <?= $one ? 'btn-primary-glow' : 'btn-light-primary' ?> w-100 py-3 fw-bold">30 gün ücretsiz dene</a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h3 class="bolum-baslik mt-15 mb-6" style="font-size:1.6rem">Hangi pakette neler var?</h3>
                <?php require __DIR__ . '/partials/paket_matris.php'; ?>
                <p class="pk-alt-not">Fiyatlara %20 KDV dahildir. Tablodaki "Kısmen", o bölümün bir kısmının pakette bulunduğunu gösterir; ayrıntısı <a href="moduller.php" style="color:var(--pw-link);text-decoration:underline">Modül Detayları</a> sayfasında. Fiyat ve ödeme seçenekleri için <a href="pricing.php" style="color:var(--pw-link);text-decoration:underline">Fiyatlandırma</a> sayfasına bakabilirsiniz.</p>
            </div>
        </div>
        <!--end::Paketler-->

        <!--begin::How It Works-->
        <div id="nasil-calisir" class="py-15 py-lg-20">
            <div class="container-xxl">
                <div class="bolum-bas orta">
                    <h2 class="bolum-baslik">3 adımda başlayın</h2>
                    <p class="bolum-giris">Program kurmanız gerekmez. Kayıt olduktan sonra firmanıza özel çalışma alanınız kısa sürede hazırlanır ve tarayıcıdan giriş yaparak kullanmaya başlarsınız.</p>
                </div>
                <ol class="adimlar">
                    <li><b>Hesabınızı oluşturun</b><span>Firma ve yetkili bilgilerinizi girerek 30 günlük ücretsiz denemeyi başlatın.</span></li>
                    <li><b>Çalışma alanınız hazırlanır</b><span>Firmanıza ait ayrı bir veritabanı oluşturulur. İlk girişte kurulum sihirbazı firma bilgilerinizi, ilk şubenizi ve ekibinizi adım adım ister; dilerseniz atlarsınız.</span></li>
                    <li><b>Yönetmeye başlayın</b><span>Kurum kodunuz ve şifrenizle giriş yapın; müşterilerinizi, randevularınızı ve kasanızı takip edin.</span></li>
                </ol>
            </div>
        </div>
        <!--end::How It Works-->

        <!--begin::Audience-->
        <div id="kimler-icin" class="py-15 py-lg-20" style="background:var(--pw-bg-alt)">
            <div class="container-xxl">
                <div class="bolum-bas orta">
                    <h2 class="bolum-baslik">Kimler için?</h2>
                    <p class="bolum-giris">Küçük, orta ve büyük ölçekli, müşteri ile birebir çalışan işletmeler için tasarlandı.</p>
                </div>
                <div class="row g-6">
                    <div class="col-md-4">
                        <div class="glass-card audience-card">
                            <div class="audience-icon"><i class="bi bi-house-gear"></i></div>
                            <h4>Mutfak, dolap ve banyo firmaları</h4>
                            <p>Ölçü, üretim ve montaj sürecinde kapora, taksit, çek ve randevu takibini tek yerde yapın. <a href="mutfak-dolap-yazilimi.php" class="text-primary">Sektöre özel sayfayı inceleyin</a></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card audience-card">
                            <div class="audience-icon"><i class="bi bi-calendar-check"></i></div>
                            <h4>Randevu ve servisle çalışan işletmeler</h4>
                            <p>Müşteri kartı, randevu geçmişi ve servis kayıtlarını bir arada tutun; yaklaşan işleri takvimden görün.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card audience-card">
                            <div class="audience-icon"><i class="bi bi-journal-text"></i></div>
                            <h4>Sözleşmeli ve vadeli satış yapan firmalar</h4>
                            <p>Sözleşme, taksit ve çek vadelerini takip edin; ön muhasebe kayıtlarınızı düzenli tutun.</p>
                        </div>
                    </div>
                </div>
                <p class="text-center text-gray-400 mt-10 mb-0">Farklı bir sektördeyseniz 30 günlük deneme ile kendi işinize uyup uymadığını görebilir ya da <a href="contact.php" class="text-primary">bize yazabilirsiniz</a>.</p>
            </div>
        </div>
        <!--end::Audience-->

        <!--begin::FAQ-->
        <div id="sss" class="py-15 py-lg-20">
            <div class="container-xxl">
                <div class="bolum-bas orta">
                    <h2 class="bolum-baslik">Sıkça sorulan sorular</h2>
                    <p class="bolum-giris">Aklınıza takılan bir şey mi var? İşte en çok sorulanlar.</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="accordion" id="prolynFaqAccordion">
                            <?php foreach ($faqItems as $fi => $faq): $n = $fi + 1; ?>
                            <div class="glass-card <?= $fi < count($faqItems) - 1 ? 'mb-4 ' : '' ?>p-2">
                                <h3 class="accordion-header fs-5" id="faqHead<?= $n ?>">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#faqBody<?= $n ?>" aria-expanded="false" aria-controls="faqBody<?= $n ?>">
                                        <?= htmlspecialchars($faq[0]) ?>
                                    </button>
                                </h3>
                                <div id="faqBody<?= $n ?>" class="accordion-collapse collapse" data-bs-parent="#prolynFaqAccordion">
                                    <div class="accordion-body text-gray-400 fs-6 lh-lg">
                                        <?= htmlspecialchars($faq[1]) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::FAQ-->

        <!--begin::CTA-->
        <div class="cta-section py-20">
            <div class="cta-pattern"></div>
            <div class="container-xxl position-relative z-index-2 text-center">
                <h2 class="bolum-baslik">İşletmenizde 30 gün ücretsiz deneyin</h2>
                <p class="bolum-giris mx-auto mb-10">Kredi kartı gerekmez. Deneme sonunda devam edip etmemek size kalmış.</p>
                <a href="register.php" class="btn btn-primary-glow btn-lg px-10 py-4 fs-4">Ücretsiz Denemeye Başla</a>
            </div>
        </div>
        <!--end::CTA-->

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