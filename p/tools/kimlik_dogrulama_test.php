<?php
/**
 * Prolyn - TCKN / VKN doğrulama birim testi (SADECE CLI).
 * Çalıştırma:  php p/tools/kimlik_dogrulama_test.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../../partials/kimlik_dogrulama.php';

$basarisiz = 0;
function kontrol(string $ad, $beklenen, $gercek): void {
    global $basarisiz;
    if ($beklenen !== $gercek) { $basarisiz++; echo "[HATA] $ad: beklenen=" . var_export($beklenen, true) . " gelen=" . var_export($gercek, true) . "\n"; }
    else { echo "[ok]   $ad\n"; }
}

/* ---- T.C. Kimlik No ---- */
// 10000000146 yaygın kullanılan, algoritmaya uyan test numarasıdır (gerçek kişiye ait değildir).
kontrol('TCKN geçerli (10000000146)', true, prolyn_tckn_gecerli('10000000146'));
kontrol('TCKN 10. hane bozuk', false, prolyn_tckn_gecerli('10000000156'));
kontrol('TCKN 11. hane bozuk', false, prolyn_tckn_gecerli('10000000147'));
kontrol('TCKN ilk hane 0', false, prolyn_tckn_gecerli('01234567890'));
kontrol('TCKN hepsi aynı', false, prolyn_tckn_gecerli('11111111111'));
kontrol('TCKN kısa', false, prolyn_tckn_gecerli('1000000014'));
kontrol('TCKN harfli', false, prolyn_tckn_gecerli('1000000014a'));
// Eski kod yalnızca "ilk 10 hane toplamı = 11. hane" bakıyordu; 10. hane hatası geçiyordu:
kontrol('TCKN eski kontrolü geçen sahte (10000000157: 11. hane toplama uyar, 10. hane yanlış)', false, prolyn_tckn_gecerli('10000000157'));

/* ---- Vergi Kimlik No ---- */
// Geçerli bir VKN üret (algoritmanın tersi) ve doğrula.
function vkn_uret(string $ilk9): string {
    $d = array_map('intval', str_split($ilk9)); $t = 0;
    for ($i = 0; $i < 9; $i++) { $v = ($d[$i] + (9 - $i)) % 10; $vv = ($v * (2 ** (9 - $i))) % 9; if ($v !== 0 && $vv === 0) { $vv = 9; } $t += $vv; }
    return $ilk9 . ((10 - ($t % 10)) % 10);
}
$gecerliVkn = vkn_uret('123456789');
kontrol("VKN geçerli ($gecerliVkn)", true, prolyn_vkn_gecerli($gecerliVkn));
$bozuk = substr($gecerliVkn, 0, 9) . (((int)$gecerliVkn[9] + 1) % 10);
kontrol("VKN son hane bozuk ($bozuk)", false, prolyn_vkn_gecerli($bozuk));
kontrol('VKN 0000000000', false, prolyn_vkn_gecerli('0000000000'));
kontrol('VKN kısa', false, prolyn_vkn_gecerli('123456789'));
for ($n = 0, $hepsi = true; $n < 200; $n++) { $x = vkn_uret(str_pad((string)random_int(0, 999999999), 9, '0', STR_PAD_LEFT)); if (!prolyn_vkn_gecerli($x) && !preg_match('/^(\d)\1{9}$/', $x)) { $hepsi = false; } }
kontrol('VKN: üretilen 200 numara doğrulanıyor', true, $hepsi);

/* ---- Birleşik çözümleyici ve normalizasyon ---- */
$r = prolyn_kimlik_no_incele('100 000 001-46');
kontrol('Boşluk/tire temizlenir ve TCKN tanınır', ['gecerli' => true, 'tip' => 'tckn', 'no' => '10000000146'], ['gecerli' => $r['gecerli'], 'tip' => $r['tip'], 'no' => $r['no']]);
kontrol('Aynı numara farklı yazımlarda aynı normalize değer', prolyn_kimlik_normalize('1000 0000.146'), prolyn_kimlik_normalize('10000000146'));
kontrol('12 hane reddedilir', false, prolyn_kimlik_no_incele('123456789012')['gecerli']);
kontrol('Boş reddedilir', false, prolyn_kimlik_no_incele('')['gecerli']);

echo $basarisiz === 0 ? "\nTÜM TESTLER GEÇTİ\n" : "\n$basarisiz TEST BAŞARISIZ\n";
exit($basarisiz === 0 ? 0 : 1);
