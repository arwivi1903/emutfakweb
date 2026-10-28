/**
 * Prolyn master panel: tema düğmesi + sidebar menü filtresi.
 * Bağımlılık yok. Tema, Metronic'in KTThemeMode'una (varsa) devreder; yoksa aynı anahtarı kendisi yazar.
 */
(function () {
    'use strict';

    // ---- Tema düğmesi: açık <-> koyu ----------------------------------------------------------
    function mevcutTema() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    }
    function temaAyarla(mod) {
        try {
            if (window.KTThemeMode && typeof window.KTThemeMode.setMode === 'function') {
                window.KTThemeMode.setMode(mod);
                return;
            }
        } catch (e) { /* aşağıdaki yedek yol */ }
        document.documentElement.setAttribute('data-bs-theme', mod);
        try { localStorage.setItem('data-bs-theme', mod); } catch (e) { /* özel pencere vb. */ }
    }
    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest && ev.target.closest('[data-pl-tema]');
        if (!btn) { return; }
        ev.preventDefault();
        temaAyarla(mevcutTema() === 'dark' ? 'light' : 'dark');
    });

    // ---- Sidebar'da ara ------------------------------------------------------------------------
    function norm(t) {
        return String(t || '').replace(/İ/g, 'i').replace(/I/g, 'ı').toLowerCase()
            .replace(/ı/g, 'i').replace(/ş/g, 's').replace(/ğ/g, 'g')
            .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c')
            .replace(/\s+/g, ' ').trim();
    }

    function kur() {
        var girdi = document.getElementById('plSbAra');
        var menu = document.getElementById('kt_app_sidebar_menu');
        var bosEl = document.getElementById('plSbBos');
        if (!girdi || !menu) { return; }

        var ogeler = [].slice.call(menu.children);
        var acikDurum = new Map(); // arama başlamadan önceki açık/kapalı durum

        function baslik(oge) {
            var t = oge.querySelector(':scope > .menu-link .menu-title');
            return t ? norm(t.textContent) : '';
        }

        function uygula() {
            var q = norm(girdi.value);
            var aramada = q.length > 0;

            if (aramada && acikDurum.size === 0) {
                ogeler.forEach(function (o) { acikDurum.set(o, o.classList.contains('show')); });
            }

            var bolumOgesi = null, bolumGorunen = 0, toplamGorunen = 0;
            function bolumuKapat() {
                if (bolumOgesi) { bolumOgesi.classList.toggle('d-none', aramada && bolumGorunen === 0); }
            }

            ogeler.forEach(function (o) {
                if (o.querySelector(':scope > .menu-content .menu-heading')) { // bölüm başlığı
                    bolumuKapat();
                    bolumOgesi = o; bolumGorunen = 0;
                    o.classList.remove('d-none');
                    return;
                }
                var gorunur = true;
                if (aramada) {
                    var kendi = baslik(o).indexOf(q) !== -1;
                    var altlar = [].slice.call(o.querySelectorAll('.menu-sub .menu-item'));
                    if (altlar.length) {
                        var eslesen = 0;
                        altlar.forEach(function (a) {
                            var ok = kendi || baslik(a).indexOf(q) !== -1;
                            a.classList.toggle('d-none', !ok);
                            if (ok) { eslesen++; }
                        });
                        gorunur = eslesen > 0;
                        o.classList.toggle('show', gorunur);
                    } else {
                        gorunur = kendi;
                    }
                } else {
                    [].forEach.call(o.querySelectorAll('.menu-sub .menu-item.d-none'), function (a) { a.classList.remove('d-none'); });
                    if (acikDurum.has(o)) { o.classList.toggle('show', acikDurum.get(o)); }
                }
                o.classList.toggle('d-none', !gorunur);
                if (gorunur) { bolumGorunen++; toplamGorunen++; }
            });
            bolumuKapat();

            if (!aramada) { acikDurum.clear(); }
            if (bosEl) { bosEl.style.display = aramada && toplamGorunen === 0 ? 'block' : 'none'; }
        }

        girdi.addEventListener('input', uygula);
        girdi.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') { girdi.value = ''; uygula(); girdi.blur(); }
            if (ev.key === 'Enter') { // ilk görünen bağlantıya git
                var a = menu.querySelector('.menu-item:not(.d-none) .menu-sub .menu-item:not(.d-none) a.menu-link, .menu-item:not(.d-none) > a.menu-link');
                if (a && a.getAttribute('href')) { window.location.href = a.getAttribute('href'); }
            }
        });
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', kur); } else { kur(); }
})();
