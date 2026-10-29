<?php
/**
 * Paket verisi — TEK KAYNAK: prolyn_master.paket_ozellikleri
 * Fiyat sayfası, ana sayfadaki "Ne satın alıyorsunuz?" bölümü ve modül karşılaştırma
 * tablosu aynı fonksiyonu kullanır; panelde (Paketler) yapılan değişiklik hepsine yansır.
 *
 * Veritabanına ulaşılamazsa sayfa boş kalmasın diye son çare yedek liste döner.
 * Yedek listedeki modüller, "Önerilen paket içerikleri" tablosuyla aynıdır.
 */
require_once __DIR__ . '/onbellek.php';
require_once __DIR__ . '/performans.php';

if (!defined('VARSAYILAN_YILLIK_INDIRIM')) {
    define('VARSAYILAN_YILLIK_INDIRIM', 10); // paket_ozellikleri.yillik_indirim kolonu yoksa
}

/** Kullanıcıya gösterilen modül adları (anahtar => ad). 'core' her pakette vardır, listelenmez. */
function prolynweb_modul_adlari(): array
{
    return [
        'customer' => 'Müşteri', 'commercial' => 'Ticari Müşteri', 'appointment' => 'Randevu',
        'current_account' => 'Cari Takip', 'inventory' => 'Ürün Yönetimi', 'accounting' => 'Ön Muhasebe (kasa, çek, personel gideri)',
        'invoice' => 'Fatura', 'contract' => 'Sözleşme', 'reports' => 'Raporlar', 'planning' => 'Planlama ve İmalat',
        'bank' => 'Banka', 'statistics' => 'İstatistik', 'core' => 'Kullanıcı Yönetimi',
    ];
}

function prolynweb_paketler(?PDO $pdo = null): array
{
    // 1) Önbellek (5 dk). Doluysa veritabanına hiç gidilmez.
    $paketler = prolynweb_onbellek_oku('paketler') ?? [];
    if ($paketler) {
        prolynweb_perf_isaretle('paket', 'onbellek');
    }
    // 2) Yoksa DB'den oku ($pdo null ise — vitrin sayfaları lazy modda — burada bağlanır).
    if (!$paketler) {
    try {
        $pdo = $pdo ?? prolynweb_baglan();
        prolynweb_perf_isaretle('paket', 'db');
        $satirlar = $pdo->query("SELECT * FROM paket_ozellikleri ORDER BY price ASC")->fetchAll();
        foreach ($satirlar as $r) {
            $paketler[] = [
                'code'            => $r['plan_name'],
                'name'            => trim((string)($r['plan_display_name'] ?? '')) !== '' ? $r['plan_display_name'] : $r['plan_name'],
                'description'     => (string)($r['plan_description'] ?? ''),
                'modules'         => json_decode((string)($r['allowed_modules'] ?? '[]'), true) ?: [],
                'storage_gb'      => (int)($r['max_storage_gb'] ?? 0),
                'support'         => (string)($r['support_level'] ?? 'email'),
                'support_hours'   => (int)($r['support_response_hours'] ?? 0),
                'price_monthly'   => (float)($r['price'] ?? 0),
                'max_users'       => (int)($r['max_total_users'] ?? 0),
                'max_branches'    => (int)($r['max_branches'] ?? 0),
                'yearly_discount' => array_key_exists('yillik_indirim', $r) ? (int)$r['yillik_indirim'] : VARSAYILAN_YILLIK_INDIRIM,
            ];
        }
    } catch (Throwable $e) {
        error_log('[prolynweb] paket_ozellikleri okunamadi: ' . $e->getMessage());
    }
    if ($paketler) {
        prolynweb_onbellek_yaz('paketler', $paketler);
    } else {
        // DB okunamadıysa sabit yedek liste yerine son bilinen gerçek fiyatları göster.
        $paketler = prolynweb_onbellek_oku('paketler', 0, true) ?? [];
        if ($paketler) { prolynweb_perf_isaretle('paket', 'bayat'); }
    }
    }

    if (empty($paketler)) {
        $b = ['core', 'customer', 'appointment', 'reports', 'current_account', 'contract'];
        $p = array_merge($b, ['commercial', 'inventory', 'accounting', 'bank', 'statistics', 'invoice']);
        $k = array_merge($p, ['planning']);
        $paketler = [
            ['code' => 'basic', 'name' => 'Başlangıç', 'description' => '', 'modules' => $b, 'storage_gb' => 0, 'support' => 'email', 'support_hours' => 0, 'price_monthly' => 1190.00, 'max_users' => 5, 'max_branches' => 1, 'yearly_discount' => VARSAYILAN_YILLIK_INDIRIM],
            ['code' => 'pro', 'name' => 'Pro', 'description' => '', 'modules' => $p, 'storage_gb' => 0, 'support' => 'email', 'support_hours' => 0, 'price_monthly' => 2290.00, 'max_users' => 15, 'max_branches' => 3, 'yearly_discount' => VARSAYILAN_YILLIK_INDIRIM],
            ['code' => 'enterprise', 'name' => 'Kurumsal', 'description' => '', 'modules' => $k, 'storage_gb' => 0, 'support' => 'email', 'support_hours' => 0, 'price_monthly' => 4490.00, 'max_users' => 0, 'max_branches' => 0, 'yearly_discount' => VARSAYILAN_YILLIK_INDIRIM],
        ];
    }

    $varsayilanAciklama = [
        'basic'      => 'Küçük işletmeler için müşteri, randevu, cari takip ve sözleşme yönetimi.',
        'pro'        => 'Büyüyen işletmeler için gelişmiş özellikler ve çoklu şube desteği.',
        'enterprise' => 'Büyük ölçekli firmalar için tam donanımlı, sınırsız çözüm.',
    ];
    foreach ($paketler as $i => $pk) {
        if ($pk['description'] === '') {
            $paketler[$i]['description'] = $varsayilanAciklama[$pk['code']] ?? '';
        }
        // Yıllık peşin: aylığın 12 katından indirim düşülür, en yakın 50 ₺'ye yuvarlanır (fiyat sayfasıyla aynı kural)
        $ind   = (int)$pk['yearly_discount'];
        $aylik = (float)$pk['price_monthly'];
        $yillikTam = round(($aylik * 12 * (1 - $ind / 100)) / 50) * 50;
        $paketler[$i]['yearly_total']   = $yillikTam;
        $paketler[$i]['monthly_approx'] = round($yillikTam / 12 / 10) * 10;
        $paketler[$i]['list_yearly']    = $aylik * 12;
    }
    return $paketler;
}

/**
 * Bir bölümün (bir veya birkaç modül anahtarı) pakette ne kadarının bulunduğu:
 * 'tam' | 'kismen' | 'yok'.
 */
function prolynweb_pakette_var(array $paket, array $anahtarlar): string
{
    $modller = (array)($paket['modules'] ?? []);
    $var = 0;
    foreach ($anahtarlar as $a) {
        if ($a === 'core' || in_array($a, $modller, true)) { $var++; }
    }
    if ($var === count($anahtarlar)) { return 'tam'; }
    return $var > 0 ? 'kismen' : 'yok';
}

/** Sayı biçimi: 12850 => 12.850 */
function prolynweb_tl(float $v): string
{
    return number_format($v, 0, ',', '.');
}
