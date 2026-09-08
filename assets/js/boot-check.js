/* Prolyn - açılışta bağlantı/güvenlik doğrulama ekranı.
   Adımlar gerçek durumu yansıtır (sahte tarama yapılmaz):
   1) script çalıştığına göre sunucu bağlantısı zaten kurulmuştur
   2) location.protocol üzerinden gerçek HTTPS durumu kontrol edilir
   3) window 'load' olayı beklenir (sayfa gerçekten hazır olduğunda) */
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

    var step1 = document.getElementById('boot-step-1');
    var step2 = document.getElementById('boot-step-2');
    var step3 = document.getElementById('boot-step-3');

    function activate(el) { if (el) { el.classList.add('boot-active'); } }
    function markOk(el) { if (el) { el.classList.add('boot-active', 'boot-ok'); } }
    function markWarn(el) { if (el) { el.classList.add('boot-active', 'boot-warn'); } }

    function finish() {
        overlay.classList.add('boot-check-done');
        try { sessionStorage.setItem('prolyn_boot_seen', '1'); } catch (e) {}
        window.setTimeout(function () {
            if (overlay.parentNode) { overlay.parentNode.removeChild(overlay); }
        }, 450);
    }

    activate(step1);

    window.setTimeout(function () {
        var isSecure = window.location.protocol === 'https:';
        if (isSecure) {
            markOk(step2);
        } else {
            markWarn(step2);
        }
    }, 350);

    window.setTimeout(function () {
        activate(step3);
        var doneAt = Date.now();
        var minVisible = 900; // ekranın göz kırpıp geçmemesi için asgari süre

        function finishAfterMinTime() {
            var wait = Math.max(0, minVisible - (Date.now() - doneAt));
            window.setTimeout(finish, wait);
        }

        if (document.readyState === 'complete') {
            finishAfterMinTime();
        } else {
            window.addEventListener('load', finishAfterMinTime, { once: true });
            // Ağ çok yavaşsa sonsuza kadar bekletme.
            window.setTimeout(finish, 4000);
        }
    }, 700);
})();
