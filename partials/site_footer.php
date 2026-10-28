<?php
/**
 * Ortak alt bilgi (tüm sayfalarda aynı).
 * Ayarlar sistem_ayarlari'ndan ($appSettings) gelir; boş olan bilgi gösterilmez.
 */
$__fMail = trim((string)($appSettings['contact_email'] ?? ''));
$__fTel  = trim((string)($appSettings['contact_phone'] ?? ''));
$__fText = trim((string)($appSettings['website_footer_text'] ?? ''));
// Eski, genel geçer slogan veritabanında kalmışsa yeni açıklamayı kullan.
if ($__fText === '' || stripos($__fText, 'yeni nesil çözüm ortağınız') !== false) {
    $__fText = 'Prolyn; müşteri, randevu, cari hesap, kasa, çek ve sözleşme takibini tek panelde toplayan bulut tabanlı ön muhasebe ve ERP yazılımıdır.';
}
// Anahtar veritabanında hiç yoksa varsayılan; varsa (boş olsa bile) panelde girilen değer geçerli.
$__fYt = trim((string)($appSettings['social_youtube'] ?? 'https://www.youtube.com/@echoLabs2'));
$__fIg = trim((string)($appSettings['social_instagram'] ?? 'https://www.instagram.com/echolab2/'));
?>
        <!--begin::Footer-->
        <footer class="site-footer mt-auto">
            <div class="container-xxl">
                <div class="row pt-9 pb-5">
                    <div class="col-lg-4 mb-10 mb-lg-0">
                        <img src="p/dist/logo/prolyn-logo-horizontal-sm.png" alt="Prolyn" class="site-logo mb-5 logo-acik" /><img src="p/dist/logo/prolyn-logo-horizontal-white-sm.png" alt="" class="site-logo mb-5 logo-koyu" aria-hidden="true" />
                        <p class="text-gray-500 fs-6 fw-semibold lh-lg mb-5"><?= htmlspecialchars($__fText) ?></p>
                        <?php if ($__fMail !== '' || $__fTel !== ''): ?>
                        <div class="d-flex flex-column gap-2 fs-6 fw-semibold">
                            <?php if ($__fMail !== ''): ?><a href="mailto:<?= htmlspecialchars($__fMail) ?>" class="footer-link"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($__fMail) ?></a><?php endif; ?>
                            <?php if ($__fTel !== ''): ?><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $__fTel)) ?>" class="footer-link"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($__fTel) ?></a><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold fs-5 mb-5">Ürün</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="index.php#ozellikler" class="footer-link fw-semibold">Özellikler</a>
                            <a href="index.php#nasil-calisir" class="footer-link fw-semibold">Nasıl Çalışır?</a>
                            <a href="moduller.php" class="footer-link fw-semibold">Modül Detayları</a>
                            <a href="mutfak-dolap-yazilimi.php" class="footer-link fw-semibold">Mutfak &amp; Dolap Yazılımı</a>
                            <a href="pricing.php" class="footer-link fw-semibold">Fiyatlandırma</a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold fs-5 mb-5">Kaynaklar</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="blog.php" class="footer-link fw-semibold">Blog</a>
                            <a href="video.php" class="footer-link fw-semibold">Videolar</a>
                            <a href="index.php#sss" class="footer-link fw-semibold">Sık Sorulan Sorular</a>
                            <a href="contact.php" class="footer-link fw-semibold">İletişim</a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold fs-5 mb-5">Yasal</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="kvkk-aydinlatma-metni.php" class="footer-link fw-semibold">KVKK Aydınlatma Metni</a>
                            <a href="gizlilik-politikasi.php" class="footer-link fw-semibold">Gizlilik Politikası</a>
                            <a href="cerez-politikasi.php" class="footer-link fw-semibold">Çerez Politikası</a>
                            <a href="kullanim-kosullari.php" class="footer-link fw-semibold">Kullanım Koşulları</a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <h4 class="text-white fw-bold fs-5 mb-5">Bizi Takip Edin</h4>
                        <div class="d-flex gap-3">
                            <?php if (!empty($appSettings['social_twitter'])): ?>
                            <a href="<?= htmlspecialchars($appSettings['social_twitter']) ?>" target="_blank" rel="noopener" class="btn btn-icon btn-light-primary btn-sm rounded-circle" aria-label="X (Twitter)"><i class="bi bi-twitter-x fs-4"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($appSettings['social_linkedin'])): ?>
                            <a href="<?= htmlspecialchars($appSettings['social_linkedin']) ?>" target="_blank" rel="noopener" class="btn btn-icon btn-light-primary btn-sm rounded-circle" aria-label="LinkedIn"><i class="bi bi-linkedin fs-4"></i></a>
                            <?php endif; ?>
                            <?php if ($__fIg !== ''): ?>
                            <a href="<?= htmlspecialchars($__fIg) ?>" target="_blank" rel="noopener" class="btn btn-icon btn-light-primary btn-sm rounded-circle" aria-label="Instagram"><i class="bi bi-instagram fs-4"></i></a>
                            <?php endif; ?>
                            <?php if ($__fYt !== ''): ?>
                            <a href="<?= htmlspecialchars($__fYt) ?>" target="_blank" rel="noopener" class="btn btn-icon btn-light-primary btn-sm rounded-circle" aria-label="YouTube"><i class="bi bi-youtube fs-4"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom d-flex flex-wrap justify-content-center text-center border-top border-gray-800 pt-8">
                    <span class="text-gray-500 fw-semibold">&copy; <?= date('Y') ?> <?= htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP') ?>. Tüm hakları saklıdır. · <a href="https://echoshift.net" target="_blank" rel="noopener" class="text-gray-400 text-hover-primary">Created by EchoShift</a></span>
                </div>
            </div>
        </footer>
        <!--end::Footer-->
