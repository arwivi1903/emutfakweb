<?php
/**
 * Hafif performans günlüğü: yavaşlığın PHP/DB'den mi yoksa sunucu öncesinden mi geldiğini ayırt etmek için.
 *
 * logs/performans.log.txt dosyasına YALNIZCA şunlar yazılır:
 *   - yavaş istekler (toplam >= 500 ms veya DB bağlantısı >= 300 ms)  -> "yavas=1"
 *   - her ~50 istekten 1'i (karşılaştırma için)                       -> "yavas=0"
 * Kişisel veri yok: IP, çerez, sorgu dizesi yazılmaz; yalnızca yol ve süreler.
 * Okuma ipucu: tarayıcıda yavaşlık görüyorsunuz ama burada yavaş kayıt YOKSA gecikme PHP'ye
 * ulaşmadan önce (Apache kuyruğu / hosting kaynak sınırı / ağ) oluşuyor demektir.
 */

function prolynweb_perf_isaretle(string $anahtar, $deger): void
{
    $GLOBALS['prolynweb_perf'][$anahtar] = $deger;
}

function prolynweb_perf_baslat(): void
{
    static $basladi = false;
    if ($basladi || PHP_SAPI === 'cli') {
        return;
    }
    $basladi = true;
    register_shutdown_function(static function () {
        try {
            $simdi  = microtime(true);
            $toplam = (int)round(($simdi - (float)($_SERVER['REQUEST_TIME_FLOAT'] ?? $simdi)) * 1000);
            $p      = $GLOBALS['prolynweb_perf'] ?? [];
            $db     = (int)($p['db_baglanti_ms'] ?? 0);
            $yavas  = $toplam >= 500 || $db >= 300;
            if (!$yavas && mt_rand(1, 50) !== 1) {
                return;
            }
            $yol = (string)strtok((string)($_SERVER['REQUEST_URI'] ?? '/'), '?');
            $yol = substr(preg_replace('/[^\x20-\x7E]/', '?', $yol), 0, 120);
            $satir = sprintf(
                "%s yavas=%d yontem=%s yol=%s kod=%d toplam_ms=%d db_baglanti_ms=%s ayar=%s paket=%s bellek_kb=%d\n",
                date('c'), $yavas ? 1 : 0, $_SERVER['REQUEST_METHOD'] ?? '-', $yol, http_response_code() ?: 200, $toplam,
                isset($p['db_baglanti_ms']) ? (string)$p['db_baglanti_ms'] : '-',
                (string)($p['ayar'] ?? '-'), (string)($p['paket'] ?? '-'),
                (int)(memory_get_peak_usage(true) / 1024)
            );
            $dosya = __DIR__ . '/../logs/performans.log.txt';
            if (!is_dir(dirname($dosya))) {
                @mkdir(dirname($dosya), 0750, true);
            }
            // 512 KB'ı geçince eskisini yedeğe al (disk dolmasın).
            if (@filesize($dosya) > 524288) {
                @rename($dosya, $dosya . '.1');
            }
            @file_put_contents($dosya, $satir, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Günlük yazılamıyorsa sayfa etkilenmesin.
        }
    });
}
