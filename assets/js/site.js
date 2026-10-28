/* Prolyn site betiği: kaydırınca görünme, illüstrasyon animasyonları, modül çubuğu */
document.addEventListener('DOMContentLoaded', function () {
    var azHareket = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* 1) Eski kaydırmada beliren öğeler (.scroll-reveal) */
    var items = document.querySelectorAll('.scroll-reveal');
    if (items.length) {
        if (!('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
        } else {
            var observer = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) { entry.target.classList.add('is-visible'); obs.unobserve(entry.target); }
                });
            }, { threshold: 0.05 });
            items.forEach(function (el) { observer.observe(el); });
            window.addEventListener('scroll', function () {
                var h = window.innerHeight;
                items.forEach(function (el) { if (el.getBoundingClientRect().top < h) { el.classList.add('is-visible'); } });
            }, { passive: true });
        }
    }

    /* 2) İllüstrasyonlar yalnızca ekrandayken oynar (pil ve işlemci tasarrufu) */
    var cizimler = document.querySelectorAll('.ill');
    if (cizimler.length) {
        if (azHareket || !('IntersectionObserver' in window)) {
            document.documentElement.classList.remove('js');   // animasyonsuz, tamamlanmış hâl
        } else {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { e.target.classList.toggle('ill-live', e.isIntersecting); });
            }, { threshold: 0.2 });
            cizimler.forEach(function (el) { io.observe(el); });
        }
    }

    /* 3) Modül çubuğunda görünür bölümü işaretle */
    var baglantilar = document.querySelectorAll('.mod-nav a[data-mod]');
    var bolumler = document.querySelectorAll('section.mod[id]');
    if (baglantilar.length && bolumler.length && 'IntersectionObserver' in window) {
        var nav = document.querySelector('.mod-nav');
        var io2 = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) { return; }
                baglantilar.forEach(function (a) {
                    var aktif = a.getAttribute('data-mod') === e.target.id;
                    if (aktif) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); }
                    if (aktif && nav && nav.scrollWidth > nav.clientWidth) {
                        nav.scrollTo({ left: a.offsetLeft - nav.clientWidth / 2 + a.clientWidth / 2, behavior: azHareket ? 'auto' : 'smooth' });
                    }
                });
            });
        }, { rootMargin: '-35% 0px -55% 0px' });
        bolumler.forEach(function (s) { io2.observe(s); });
    }
});
