<?php
/**
 * Prolyn - Potansiyel Müşteri (Lead) yardımcıları
 * -----------------------------------------------
 * Şema kurulumu, şablonlar, iletişim kuralları, takip/ret linkleri.
 * Tablolar ilk kullanımda otomatik oluşturulur (prolyn_master).
 */

require_once __DIR__ . '/../../env.php';

const LEAD_DURUMLAR = [
    'yeni'              => ['Yeni', 'secondary'],
    'iletisime_gecildi' => ['İletişime geçildi', 'info'],
    'ilgileniyor'       => ['İlgileniyor', 'primary'],
    'demo'              => ['Demo planlandı', 'warning'],
    'deneme'            => ['Denemede', 'success'],
    'musteri'           => ['Müşteri', 'success'],
    'olumsuz'           => ['Olumsuz', 'dark'],
    'ret'               => ['İletişim istemiyor', 'danger'],
];

/** Bu durumlardaki leadlere kampanya mesajı gönderilmez. */
const LEAD_DURDUR = ['deneme', 'musteri', 'olumsuz', 'ret'];

function lead_mail_kurallari(): array
{
    return [
        'max_mail'      => 3,                                            // lead başına en fazla kampanya e-postası
        'min_gun_arasi' => 4,                                            // iki e-posta arası en az gün
        'gunluk_limit'  => (int)prolynweb_env('LEAD_MAIL_DAILY_LIMIT', 40), // alan adı itibarı için günlük üst sınır
    ];
}

function lead_site_url(string $path = ''): string
{
    $base = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');
    return $base . '/' . ltrim($path, '/');
}

/** Tanıtım sayfası linki (lead kimliği + UTM ile; kayıt olunca lead ile eşleşir). */
function lead_takip_linki(int $leadId, string $kanal): string
{
    return lead_site_url('mutfak-dolap-yazilimi') . '?' . http_build_query([
        'ref' => 'L' . $leadId,
        'utm_source' => $kanal,
        'utm_medium' => 'outreach',
        'utm_campaign' => 'mutfak-dolap-2026',
    ]);
}

function lead_kayit_linki(int $leadId, string $kanal): string
{
    return lead_site_url('register') . '?' . http_build_query([
        'ref' => 'L' . $leadId, 'utm_source' => $kanal, 'utm_medium' => 'outreach', 'utm_campaign' => 'mutfak-dolap-2026',
    ]);
}

function lead_ret_linki(string $token): string
{
    return lead_site_url('ret') . '?t=' . rawurlencode($token);
}

/** +905xx... → 905xx... (wa.me biçimi) */
function lead_wa_numara(string $telefon): string
{
    return preg_replace('/\D/', '', $telefon);
}

function lead_sema_kur($db): void
{
    static $kuruldu = false;
    if ($kuruldu) { return; }
    $kuruldu = true;

    if (!$db->tableExists('potansiyel_musteriler')) {
        $db->TableOperations("CREATE TABLE IF NOT EXISTS potansiyel_musteriler (
            lead_id INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar',
            firma_adi VARCHAR(255) NOT NULL COMMENT 'İşletme adı',
            yetkili VARCHAR(150) DEFAULT NULL COMMENT 'Görüşülen kişi',
            telefon VARCHAR(20) DEFAULT NULL COMMENT 'E.164 biçiminde telefon (+90...)',
            whatsapp TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: cep telefonu (WhatsApp ile ulaşılabilir)',
            eposta VARCHAR(191) DEFAULT NULL COMMENT 'E-posta',
            web VARCHAR(255) DEFAULT NULL COMMENT 'Web sitesi / sosyal hesap',
            il VARCHAR(60) DEFAULT NULL, ilce VARCHAR(80) DEFAULT NULL,
            adres VARCHAR(500) DEFAULT NULL,
            segment VARCHAR(40) DEFAULT NULL COMMENT 'mutfak, banyo, dolap...',
            imalat_montaj TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: imalat/montaj yapan firma',
            buyuk_marka TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: büyük marka/fabrika (hedef dışı olabilir)',
            google_puan DECIMAL(2,1) DEFAULT NULL,
            puan INT(11) NOT NULL DEFAULT 0 COMMENT 'Lead puanı 0-100',
            oncelik CHAR(1) NOT NULL DEFAULT 'C' COMMENT 'A/B/C',
            kaynak VARCHAR(40) NOT NULL DEFAULT 'manuel',
            place_id VARCHAR(120) DEFAULT NULL COMMENT 'Google place_id (tekrar içe aktarmayı engeller)',
            maps_url VARCHAR(255) DEFAULT NULL,
            durum VARCHAR(30) NOT NULL DEFAULT 'yeni' COMMENT 'Satış hattı durumu',
            sonraki_takip DATE DEFAULT NULL COMMENT 'Bir sonraki takip tarihi',
            son_iletisim DATETIME DEFAULT NULL,
            wa_sayisi INT(11) NOT NULL DEFAULT 0,
            mail_sayisi INT(11) NOT NULL DEFAULT 0,
            son_mail DATETIME DEFAULT NULL,
            ret_token CHAR(32) NOT NULL COMMENT 'Abonelikten çıkma anahtarı',
            ret_tarihi DATETIME DEFAULT NULL COMMENT 'İletişim reddi zamanı',
            customer_id INT(11) DEFAULT NULL COMMENT 'Kayıt olduysa kurumlar.customer_id',
            notlar TEXT DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (lead_id),
            UNIQUE KEY uq_lead_place (place_id),
            UNIQUE KEY uq_lead_ret (ret_token),
            KEY idx_lead_durum (durum), KEY idx_lead_oncelik (oncelik), KEY idx_lead_takip (sonraki_takip),
            KEY idx_lead_eposta (eposta), KEY idx_lead_tel (telefon)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Potansiyel müşteriler (satış hattı)'");
    }
    if (!$db->tableExists('lead_aktiviteler')) {
        $db->TableOperations("CREATE TABLE IF NOT EXISTS lead_aktiviteler (
            aktivite_id INT(11) NOT NULL AUTO_INCREMENT,
            lead_id INT(11) NOT NULL,
            tur VARCHAR(20) NOT NULL COMMENT 'whatsapp, eposta, arama, not, durum, ziyaret, sistem',
            aciklama TEXT DEFAULT NULL,
            admin_id INT(11) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (aktivite_id), KEY idx_la_lead (lead_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lead iletişim geçmişi'");
    }
    if (!$db->tableExists('lead_sablonlari')) {
        $db->TableOperations("CREATE TABLE IF NOT EXISTS lead_sablonlari (
            sablon_id INT(11) NOT NULL AUTO_INCREMENT,
            kanal VARCHAR(20) NOT NULL COMMENT 'whatsapp veya eposta',
            kod VARCHAR(40) NOT NULL,
            ad VARCHAR(120) NOT NULL,
            konu VARCHAR(255) DEFAULT NULL COMMENT 'E-posta konusu',
            govde TEXT NOT NULL,
            sira INT(11) NOT NULL DEFAULT 0,
            aktif TINYINT(1) NOT NULL DEFAULT 1,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (sablon_id), UNIQUE KEY uq_sablon_kod (kod)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='WhatsApp ve e-posta şablonları'");
        foreach (lead_varsayilan_sablonlar() as $i => $s) {
            $db->Insert("INSERT IGNORE INTO lead_sablonlari (kanal, kod, ad, konu, govde, sira) VALUES (?,?,?,?,?,?)",
                [$s['kanal'], $s['kod'], $s['ad'], $s['konu'] ?? null, $s['govde'], $i + 1]);
        }
    }
}

function lead_varsayilan_sablonlar(): array
{
    return [
        ['kanal' => 'whatsapp', 'kod' => 'wa_tanisma', 'ad' => '1. İlk tanışma',
         'govde' => "Merhaba {firma} ekibi, ben {gonderen}, Prolyn'den yazıyorum.\n\nMutfak, dolap ve banyo firmaları için ölçü ve montaj randevularını, sözleşme ve tahsilatları, eksik malzeme takibini tek ekrandan yöneten bir program geliştirdik.\n\n30 gün ücretsiz deneyebilirsiniz; kurulumu ve mevcut müşteri listenizin aktarımını da ücretsiz biz yapıyoruz. Kısa tanıtım: {link}\n\nUygunsanız 15 dakikalık bir görüntülü demo ayarlayabilirim. İlgilenmiyorsanız \"hayır\" yazmanız yeterli, tekrar yazmam."],
        ['kanal' => 'whatsapp', 'kod' => 'wa_takip', 'ad' => '2. Takip (3-5 gün sonra)',
         'govde' => "Merhaba {firma}, birkaç gün önce Prolyn için yazmıştım.\n\nFirmaların en çok zaman kaybettiği yer genelde montaj ekiplerinin günlük planı ve eksik parça takibi oluyor. Prolyn'de ikisini de tek listeden izliyorsunuz, müşteri ve tahsilat bilgisi aynı kayıtta duruyor.\n\nBu hafta 15 dakikalık bir demo için uygun olduğunuz bir gün var mı? {link}"],
        ['kanal' => 'whatsapp', 'kod' => 'wa_demo_sonrasi', 'ad' => '3. Demo sonrası deneme daveti',
         'govde' => "Merhaba {firma}, demo için teşekkürler.\n\nDeneme hesabınızı buradan 2 dakikada açabilirsiniz: {kayit_link}\n\nKurulum ve müşteri listenizin aktarımı bizden. Elinizdeki Excel listesini bu numaraya göndermeniz yeterli."],
        ['kanal' => 'eposta', 'kod' => 'mail_tanisma', 'ad' => '1. İlk e-posta', 'konu' => '{firma} için ölçü, montaj ve tahsilat takibi tek ekranda',
         'govde' => "Merhaba,\n\nBen {gonderen}, Prolyn'den yazıyorum. Prolyn, mutfak, dolap ve banyo firmalarının günlük işini tek panelde toplayan bulut tabanlı bir yazılım:\n\n- Ölçü ve montaj randevuları, ekiplerin günlük planı\n- Eksik malzeme ve sorunlu iş takibi\n- Müşteri, sözleşme, cari hesap, kasa, banka ve çek takibi\n- Şube ve personel bazında yetki\n\n{firma} için 30 günlük ücretsiz deneme hesabı açabiliriz. Kurulumu ve mevcut müşteri listenizin aktarımını ücretsiz biz yapıyoruz; kredi kartı istemiyoruz.\n\nKısa tanıtım: {link}\n\nUygun olursanız bu hafta 15 dakikalık bir görüntülü demo ayarlayalım. Bu e-postaya yanıt vermeniz yeterli.\n\nSaygılarımla,\n{gonderen}\nProlyn | {site}\n\n--\nBu e-postayı, Google Haritalar'da herkese açık yayımlanan işletme iletişim bilgileriniz üzerinden gönderdik. Bir daha almak istemezseniz: {ret_link}"],
        ['kanal' => 'eposta', 'kod' => 'mail_takip', 'ad' => '2. Takip e-postası', 'konu' => 'Montaj ekiplerinin günlük planı: kısa bir örnek',
         'govde' => "Merhaba,\n\nGeçen hafta Prolyn hakkında yazmıştım. Kısa bir örnekle anlatayım:\n\nSabah panelde günün ölçü ve montaj randevuları ekip ekip listelenir. Montajda eksik çıkan parça aynı randevuya not edilir ve eksik özetinden takip edilir. İş bitince tahsilat aynı müşteri kaydına işlenir; cari bakiye ve kasa otomatik güncellenir.\n\nİsterseniz bunu {firma}'nın kendi verileriyle 15 dakikada gösterebilirim. Tanıtım: {link}\n\nSaygılarımla,\n{gonderen}\nProlyn | {site}\n\n--\nBir daha almak istemezseniz: {ret_link}"],
        ['kanal' => 'eposta', 'kod' => 'mail_son', 'ad' => '3. Son hatırlatma', 'konu' => 'Son kez yazıyorum: ücretsiz kurulum teklifi',
         'govde' => "Merhaba,\n\nDaha önce iki kez yazdım; bu son e-postam.\n\n{firma} için ücretsiz kurulum ve müşteri listesi aktarımı teklifimiz geçerli. Deneme hesabını 2 dakikada açabilirsiniz: {kayit_link}\n\nŞu an uygun değilse sorun değil; bu e-postaya \"daha sonra\" yazarsanız birkaç ay sonra bir kez hatırlatırım.\n\nSaygılarımla,\n{gonderen}\nProlyn | {site}\n\n--\nBir daha almak istemezseniz: {ret_link}"],
    ];
}

/** Şablon değişkenlerini doldurur. */
function lead_sablon_doldur(string $metin, array $lead, string $kanal, string $gonderen): string
{
    $id = (int)$lead['lead_id'];
    $firma = trim((string)$lead['firma_adi']);
    $map = [
        '{firma}'      => $firma !== '' ? $firma : 'firmanız',
        '{yetkili}'    => trim((string)($lead['yetkili'] ?? '')) ?: 'Merhaba',
        '{il}'         => (string)($lead['il'] ?? ''),
        '{gonderen}'   => $gonderen !== '' ? $gonderen : 'Prolyn Ekibi',
        '{link}'       => lead_takip_linki($id, $kanal),
        '{kayit_link}' => lead_kayit_linki($id, $kanal),
        '{ret_link}'   => lead_ret_linki((string)$lead['ret_token']),
        '{site}'       => lead_site_url(''),
    ];
    return strtr($metin, $map);
}

/** Kampanya mesajı gönderilebilir mi? [bool, neden] */
function lead_gonderilebilir_mi(array $lead, string $kanal): array
{
    if (!empty($lead['ret_tarihi']) || $lead['durum'] === 'ret') { return [false, 'iletişim istemiyor']; }
    if (in_array($lead['durum'], LEAD_DURDUR, true)) { return [false, 'durum: ' . (LEAD_DURUMLAR[$lead['durum']][0] ?? $lead['durum'])]; }
    if ($kanal === 'eposta') {
        if (empty($lead['eposta'])) { return [false, 'e-posta yok']; }
        $k = lead_mail_kurallari();
        if ((int)$lead['mail_sayisi'] >= $k['max_mail']) { return [false, 'en fazla ' . $k['max_mail'] . ' e-posta']; }
        if (!empty($lead['son_mail']) && strtotime($lead['son_mail']) > strtotime('-' . $k['min_gun_arasi'] . ' days')) {
            return [false, 'son e-postadan ' . $k['min_gun_arasi'] . ' gün geçmedi'];
        }
    }
    if ($kanal === 'whatsapp' && (empty($lead['telefon']) || (int)$lead['whatsapp'] !== 1)) { return [false, 'cep telefonu yok']; }
    return [true, ''];
}

function lead_aktivite($db, int $leadId, string $tur, string $aciklama): void
{
    $db->Insert("INSERT INTO lead_aktiviteler (lead_id, tur, aciklama, admin_id) VALUES (?,?,?,?)",
        [$leadId, $tur, $aciklama, $_SESSION['admin_id'] ?? null]);
}

/** E-postayı mail kuyruğuna ekler (ret başlıkları ile). */
function lead_mail_kuyruga_ekle($db, array $lead, array $sablon, string $gonderen): bool
{
    $konu = lead_sablon_doldur((string)$sablon['konu'], $lead, 'email', $gonderen);
    $govde = lead_sablon_doldur((string)$sablon['govde'], $lead, 'email', $gonderen);
    $retUrl = lead_ret_linki((string)$lead['ret_token']);
    $payload = json_encode(['headers' => [
        'List-Unsubscribe' => '<' . $retUrl . '>',
        'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
    ], 'lead_id' => (int)$lead['lead_id'], 'sablon' => $sablon['kod']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $ok = $db->Insert("INSERT INTO mail_kuyrugu (channel, recipient, subject, message, source, status, attempts, payload, created_at) VALUES ('email', ?, ?, ?, 'lead_kampanya', 'pending', 0, ?, NOW())",
        [$lead['eposta'], $konu, $govde, $payload]);
    if ($ok === false) { return false; }
    $db->Update("UPDATE potansiyel_musteriler SET mail_sayisi = mail_sayisi + 1, son_mail = NOW(), son_iletisim = NOW(),
                 durum = IF(durum = 'yeni', 'iletisime_gecildi', durum),
                 sonraki_takip = IF(sonraki_takip IS NULL OR sonraki_takip < CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), sonraki_takip)
                 WHERE lead_id = ?", [(int)$lead['lead_id']]);
    lead_aktivite($db, (int)$lead['lead_id'], 'eposta', 'Kuyruğa eklendi: ' . $sablon['ad'] . ' → ' . $lead['eposta']);
    return true;
}

function lead_bugun_gonderilen_mail($db): int
{
    return (int)$db->getColumn("SELECT COUNT(*) FROM mail_kuyrugu WHERE source = 'lead_kampanya' AND created_at >= CURDATE()");
}

function lead_gonderen_adi(): string
{
    $ad = trim((string)($_SESSION['full_name'] ?? ''));
    return $ad !== '' ? $ad : 'Prolyn Ekibi';
}

function lead_durum_rozet(string $durum): string
{
    [$ad, $renk] = LEAD_DURUMLAR[$durum] ?? [$durum, 'secondary'];
    return '<span class="badge badge-light-' . $renk . '">' . htmlspecialchars($ad) . '</span>';
}

function lead_yeni_token(): string
{
    return bin2hex(random_bytes(16));
}

/** Takip tarihini "3 gün gecikti / Bugün / 2 gün sonra" biçiminde, renk sınıfıyla verir: [yazı, css sınıfı, tam tarih]. */
function lead_takip_etiket(?string $tarih, bool $kapali): array
{
    if (!$tarih) { return ['Planlanmadı', 'text-muted', '']; }
    $tamTarih = date('d.m.Y', strtotime($tarih));
    if ($kapali) { return [$tamTarih, 'text-muted', '']; }
    $fark = (int)(new DateTime(date('Y-m-d')))->diff(new DateTime($tarih))->format('%r%a');
    if ($fark < 0)   { return [abs($fark) . ' gün gecikti', 'text-danger fw-bold', $tamTarih]; }
    if ($fark === 0) { return ['Bugün', 'text-warning fw-bold', $tamTarih]; }
    if ($fark === 1) { return ['Yarın', 'text-gray-800 fw-semibold', $tamTarih]; }
    return [$fark . ' gün sonra', 'text-gray-700', $tamTarih];
}
