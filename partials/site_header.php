<?php
/**
 * Ortak üst menü (tüm sayfalarda aynı).
 *
 * Sayfadan verilebilecek isteğe bağlı değişkenler:
 *   $navActive : 'ozellikler' | 'moduller' | 'blog' | 'video' | 'pricing' | 'contact'
 *   $kayitUrl  : "30 Gün Ücretsiz Dene" bağlantısı (varsayılan: register.php)
 * $appPanelUrl db.php'den gelir.
 */
$__kayit  = $kayitUrl ?? 'register.php';
$__active = $navActive ?? '';
$__menu = [
    ['ozellikler', 'index.php#ozellikler', 'Özellikler'],
    ['nasil',      'index.php#nasil-calisir', 'Nasıl Çalışır?'],
    ['moduller',   'moduller.php', 'Modül Detayları'],
    ['blog',       'blog.php', 'Blog'],
    ['video',      'video.php', 'Videolar'],
    ['pricing',    'pricing.php', 'Fiyatlandırma'],
    ['contact',    'contact.php', 'İletişim'],
];
?>
        <!--begin::Header-->
        <div class="landing-header glass-header" data-kt-sticky="true" data-kt-sticky-name="landing-header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
            <div class="container-xxl">
                <nav class="navbar navbar-dark w-100 py-4 py-lg-6" aria-label="Ana menü">
                    <a class="navbar-brand d-flex align-items-center" href="index.php" aria-label="Prolyn ana sayfa">
                        <img src="p/dist/logo/prolyn-logo-horizontal-sm.png" alt="Prolyn" class="site-logo logo-acik" /><img src="p/dist/logo/prolyn-logo-horizontal-white-sm.png" alt="" class="site-logo logo-koyu" aria-hidden="true" />
                    </a>

                    <div class="d-flex align-items-center gap-3 d-lg-none ms-auto">
                    <button type="button" class="tema-btn " data-tema-toggle aria-pressed="false" aria-label="Koyu temaya geç" title="Koyu / açık tema">
                                <svg class="tema-ay" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                                <svg class="tema-gunes" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                            </button>
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Menüyü aç">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    </div>

                    <div class="d-none d-lg-flex align-items-center ms-auto">
                        <ul class="nav text-white fw-semibold fs-6 me-10">
                            <?php foreach ($__menu as $__m): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4<?= $__active === $__m[0] ? ' is-active' : '' ?>" href="<?= $__m[1] ?>"<?= $__active === $__m[0] ? ' aria-current="page"' : '' ?>><?= $__m[2] ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="d-flex align-items-center flex-shrink-0">
                            <button type="button" class="tema-btn me-4" data-tema-toggle aria-pressed="false" aria-label="Koyu temaya geç" title="Koyu / açık tema">
                                <svg class="tema-ay" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                                <svg class="tema-gunes" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                            </button>
                            <a href="<?= htmlspecialchars($appPanelUrl ?? 'login.php') ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold px-6 py-3 me-4">Giriş Yap</a>
                            <a href="<?= htmlspecialchars($__kayit) ?>" class="btn btn-primary-glow px-6 py-3 fw-bold">30 Gün Ücretsiz Dene</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!--end::Header-->
<script>
(function () {
    function esitle() {
        var koyu = document.documentElement.getAttribute('data-tema') === 'dark';
        document.querySelectorAll('[data-tema-toggle]').forEach(function (b) {
            b.setAttribute('aria-pressed', koyu ? 'true' : 'false');
            b.setAttribute('aria-label', koyu ? 'Açık temaya geç' : 'Koyu temaya geç');
        });
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-tema-toggle]');
        if (!b) { return; }
        var h = document.documentElement;
        var t = h.getAttribute('data-tema') === 'dark' ? 'light' : 'dark';
        h.setAttribute('data-tema', t);
        h.setAttribute('data-bs-theme', t);
        try { localStorage.setItem('prolyn_tema', t); } catch (x) {}
        esitle();
    });
    document.addEventListener('DOMContentLoaded', esitle);
})();
</script>
