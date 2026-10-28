<?php
/**
 * Çerez bilgilendirme bandı.
 * Site yalnızca oturum/güvenlik için zorunlu çerezler kullanır; analitik veya
 * reklam çerezi yoktur. Bu nedenle bant bilgilendirme amaçlıdır. İleride
 * analitik/reklam aracı eklenirse burası açık rıza (kabul/reddet) akışına
 * çevrilmeli ve ilgili betikler onaydan önce yüklenmemelidir.
 */
?>
<div id="cookie-banner" class="cookie-banner" role="region" aria-label="Çerez bilgilendirmesi" hidden>
    <p class="cookie-banner-text">
        Bu sitede yalnızca oturum güvenliği ve form koruması için gerekli çerezler kullanılır; reklam veya takip çerezi kullanmıyoruz.
        Ayrıntılar için <a href="cerez-politikasi.php">Çerez Politikası</a> ve <a href="kvkk-aydinlatma-metni.php">KVKK Aydınlatma Metni</a>'ni inceleyebilirsiniz.
    </p>
    <button type="button" class="btn btn-primary-glow cookie-banner-btn" id="cookie-banner-ok">Anladım</button>
</div>
<script>
(function () {
    var el = document.getElementById('cookie-banner');
    if (!el) { return; }
    var seen = false;
    try { seen = localStorage.getItem('prolyn_cookie_notice') === '1'; } catch (e) {}
    if (!seen) { el.hidden = false; }
    document.getElementById('cookie-banner-ok').addEventListener('click', function () {
        el.hidden = true;
        try { localStorage.setItem('prolyn_cookie_notice', '1'); } catch (e) {}
    });
})();
</script>
