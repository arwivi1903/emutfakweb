<?php
/**
 * Basit dosya önbelleği (vitrin sitesi: ayarlar, paket listesi).
 *
 * Neden: paylaşımlı hostingte MySQL bağlantısı/sorgusu zaman zaman saniyelerce takılıyor.
 * Sık değişmeyen veriyi 5 dk dosyada tutarsak sayfalar DB'ye gitmeden açılır.
 *
 * Güvenlik: dosyalar cache/ klasöründe, adı *.cache.php ve ilk satırı exit'li; yanlışlıkla web'den
 * istenirse bile içerik gösterilmez (klasörde ayrıca .htaccess ile erişim kapalı).
 * Yalnızca JSON saklanır (unserialize yok). Yerelde (localhost) önbellek kapalıdır.
 * Elle temizlemek için: cache/*.cache.php dosyalarını silmek yeterli.
 */

/** Geçerlilik süresi (sn). .env: SITE_CACHE_TTL (0 = kapalı). Yerelde her zaman 0. */
function prolynweb_onbellek_ttl(): int
{
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '' || strpos($host, 'localhost') === 0 || strpos($host, '127.0.0.1') === 0) {
        return 0;
    }
    $v = prolynweb_env('SITE_CACHE_TTL', '300');
    return max(0, min(3600, (int)$v));
}

function prolynweb_onbellek_yol(string $anahtar): string
{
    // Anahtar yalnızca harf/rakam/_ olabilir (yol manipülasyonu yok).
    return __DIR__ . '/../cache/' . preg_replace('/[^a-z0-9_]/i', '', $anahtar) . '.cache.php';
}

/** Süresi dolmamışsa veriyi döndürür; yoksa null. $bayatOk=true ise yaşa bakmaz (DB çökerse son çare). */
function prolynweb_onbellek_oku(string $anahtar, ?int $ttl = null, bool $bayatOk = false): ?array
{
    $ttl = $ttl ?? prolynweb_onbellek_ttl();
    if ($ttl <= 0 && !$bayatOk) {
        return null;
    }
    $yol = prolynweb_onbellek_yol($anahtar);
    $mt  = @filemtime($yol);
    if ($mt === false) {
        return null;
    }
    if (!$bayatOk && (time() - $mt) > $ttl) {
        return null;
    }
    $ham = @file_get_contents($yol);
    if ($ham === false) {
        return null;
    }
    $nl = strpos($ham, "\n");
    $veri = $nl === false ? null : json_decode(substr($ham, $nl + 1), true);
    return is_array($veri) ? $veri : null;
}

/** Atomik yazar (geçici dosya + rename): okuyan yarım dosya görmez. Hata olursa sessizce geçer. */
function prolynweb_onbellek_yaz(string $anahtar, array $veri): void
{
    if (prolynweb_onbellek_ttl() <= 0 || $veri === []) {
        return;
    }
    try {
        $yol = prolynweb_onbellek_yol($anahtar);
        $dizin = dirname($yol);
        if (!is_dir($dizin)) {
            @mkdir($dizin, 0750, true);
        }
        $tmp = $yol . '.' . getmypid() . '.tmp';
        $icerik = "<?php http_response_code(404); exit; ?>\n" . json_encode($veri, JSON_UNESCAPED_UNICODE);
        if (@file_put_contents($tmp, $icerik, LOCK_EX) !== false) {
            @rename($tmp, $yol);
        }
        @unlink($tmp);
    } catch (\Throwable $e) {
        // Önbellek yazılamıyorsa site yine çalışır.
    }
}
