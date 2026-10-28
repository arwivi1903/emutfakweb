<?php
/**
 * Ortak mobil menü (offcanvas). Tüm sayfalarda aynı; </body> öncesinde include edilir.
 * İsteğe bağlı: $kayitUrl (varsayılan register.php), $navActive.
 */
$__kayit  = $kayitUrl ?? 'register.php';
$__active = $navActive ?? '';
$__mm = [
    ['ozellikler', 'index.php#ozellikler', 'bi-star', 'Özellikler'],
    ['nasil',      'index.php#nasil-calisir', 'bi-info-circle', 'Nasıl Çalışır?'],
    ['moduller',   'moduller.php', 'bi-grid', 'Modül Detayları'],
    ['blog',       'blog.php', 'bi-file-text', 'Blog'],
    ['video',      'video.php', 'bi-play-circle', 'Videolar'],
    ['pricing',    'pricing.php', 'bi-tag', 'Fiyatlandırma'],
    ['contact',    'contact.php', 'bi-envelope', 'İletişim'],
];
?>
    <!-- Mobil menü -->
    <div class="offcanvas offcanvas-end offcanvas-glass border-0" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header border-bottom border-gray-800 p-6">
            <h5 class="offcanvas-title m-0" id="mobileMenuLabel"><img src="p/dist/logo/prolyn-logo-horizontal-sm.png" alt="Prolyn" class="site-logo site-logo-sm logo-acik" /><img src="p/dist/logo/prolyn-logo-horizontal-white-sm.png" alt="" class="site-logo site-logo-sm logo-koyu" aria-hidden="true" /></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
        </div>
        <div class="offcanvas-body p-6">
            <ul class="nav flex-column fs-4 fw-semibold mb-8">
                <?php foreach ($__mm as $__m): ?>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0<?= $__active === $__m[0] ? ' is-active' : '' ?>" href="<?= $__m[1] ?>"><i class="bi <?= $__m[2] ?> me-2 text-primary"></i><?= $__m[3] ?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="d-grid gap-3 mt-8">
                <a href="<?= htmlspecialchars($__kayit) ?>" class="btn btn-primary-glow py-3 fw-bold fs-5">30 Gün Ücretsiz Dene</a>
                <a href="<?= htmlspecialchars($appPanelUrl ?? 'login.php') ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold py-3 fs-5">Giriş Yap</a>
            </div>
        </div>
    </div>
