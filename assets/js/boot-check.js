/* Prolyn - açılışta bağlantı/güvenlik doğrulama ekranı.
   Metinler kurspro.net tarzı bir "tarayıcı doğrulanıyor" ekranını
   yansıtır, ama sahte bir tarama yapılmaz: gerçek durum kontrol edilir
   (location.protocol ile HTTPS durumu) ve sayfa gerçekten hazır
   olduğunda (window 'load') "Doğrulama tamamlandı" gösterilip ekran
   kapanır. */
(function () {
    var overlay = document.getElementById('prolyn-boot-check');
    if (!overlay) {
        return;
    }

    // Aynı oturumda tekrar tekrar gösterme.
    try {
        if (sessionStorage.getItem('prolyn_boot_seen') === '1') {
            overlay.remove();
            return;
        }
    } catch (e) { /* private mod / erişim yok: yine göster */ }

    var message = document.getElementById('boot-check-message');

    function setMessage(text) {
        if (message) { message.textContent = text; }
    }

    function finish() {
        var isSecure = window.location.protocol === 'https:';
        setMessage(isSecure ? 'Doğrulama tamamlandı ✓ Güvenli bağlantı' : 'Doğrulama tamamlandı');
        overlay.classList.add('boot-check-verified');

        window.setTimeout(function () {
            overlay.classList.add('boot-check-done');
            try { sessionStorage.setItem('prolyn_boot_seen', '1'); } catch (e) {}
            window.setTimeout(function () {
                if (overlay.parentNode) { overlay.parentNode.removeChild(overlay); }
            }, 450);
        }, 500);
    }

    var startedAt = Date.now();
    var minVisible = 1100; // ekranın göz kırpıp geçmemesi için asgari süre

    function finishAfterMinTime() {
        var wait = Math.max(0, minVisible - (Date.now() - startedAt));
        window.setTimeout(finish, wait);
    }

    if (document.readyState === 'complete') {
        finishAfterMinTime();
    } else {
        window.addEventListener('load', finishAfterMinTime, { once: true });
        // Ağ çok yavaşsa sonsuza kadar bekletme.
        window.setTimeout(finish, 4500);
    }
})();
