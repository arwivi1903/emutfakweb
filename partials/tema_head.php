<?php
/**
 * Tema + yazı tipi — her sayfanın <head> bölümünde, CSS bağlantılarından ÖNCE include edilir.
 * Varsayılan tema AÇIK'tır. Ziyaretçi koyu temayı seçtiyse (localStorage) sayfa çizilmeden
 * önce uygulanır; böylece açık→koyu "yanıp sönme" olmaz. localStorage kapalıysa açık tema kalır.
 */
?>
    <meta name="color-scheme" content="light dark">
    <script>
    (function () {
        var t = 'light';
        try { var s = localStorage.getItem('prolyn_tema'); if (s === 'dark' || s === 'light') { t = s; } } catch (e) {}
        var h = document.documentElement;
        h.setAttribute('data-tema', t);
        h.setAttribute('data-bs-theme', t);
        h.classList.add('js');
    })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&amp;display=swap">
