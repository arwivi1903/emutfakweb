/* Prolyn - Scroll reveal for landing pages */
document.addEventListener('DOMContentLoaded', function () {
    var items = document.querySelectorAll('.scroll-reveal');
    if (!items.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05, rootMargin: '0px 0px 0px 0px' });

    items.forEach(function (el) { observer.observe(el); });
    // Güvenlik ağı: hızlı kaydırmada atlanan bölümler boş kalmasın
    window.addEventListener('scroll', function () {
        var h = window.innerHeight;
        items.forEach(function (el) {
            if (el.getBoundingClientRect().top < h) { el.classList.add('is-visible'); }
        });
    }, { passive: true });
});
