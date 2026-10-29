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
    // Kısa ve okunur bağlantı: anahtarın ilk 16 karakteri (64 bit) yeterli ve tahmin edilemez; ret.php ön ek eşleşmesiyle çözer
    return lead_site_url('ret') . '?t=' . rawurlencode(substr($token, 0, 16));
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
    lead_sablonlari_yenile($db);
}

/**
 * Varsayılan şablon metinleri yenilendiğinde, kullanıcının hiç değiştirmediği (ilk sürümle birebir aynı) şablonları günceller.
 * Elle düzenlenmiş şablonlara dokunulmaz. Değiştirilmemiş şablon yoksa tek bir hafif SELECT'ten ibarettir.
 */
function lead_sablonlari_yenile($db): void
{
    // kod => önceki varsayılan sürümlerin MD5'leri (bunlardan biriyle birebir aynıysa şablon hiç düzenlenmemiştir)
    $eskiHash = [
        'wa_tanisma' => ['9372df4df01268f9a8cae64bc124d099', '138376305b99491daf841e2c79833611', 'be93dd3c28b44b3980c2ae59faa9b062'],
        'wa_takip' => ['303f815d90099a0045ec6ce793c479e3', '0fd7b3ba90ea52cbacda3e3b994bfdf9', '3a9b46b6c8455e2ce4b050a43dd9d96b'],
        'wa_demo_sonrasi' => ['3e5fec4c07fde7e72ac7bde0a73333f3', '60ad2803b57ea2dd60a1a2efd4657932', 'e33fd0029867262d44a94c8c9af4ca99'],
        'mail_tanisma' => ['b2ff85b2c8630554de184729268a555d', 'c27956fa4334c704c8d95b772edf011b', 'cb3fed4d6a651afd7edac23e74b2fe08', '4896d32ae081fc24b887ba4f96d64272'],
        'mail_takip' => ['2d43f2e38b2c9a9e57fb0ce0d5c271e6', '8e428d49f99aa86f84b701e719563fc9', '4e64dfb9bdc71b1ced7cbcfd485a1487', '92f877585c5c81d13caef09e97c21e44'],
        'mail_son' => ['271ec8b845f2819f8d1ab82ed6de79ae', '8d7b2de8ed5ee670da17732381ca6d29', '5a6a28fa9d93039dc1569a100b0478fd', '09de7e040cd3880d69a34ada81c5ff40'],
    ];
    $satirlar = $db->getRowsAssoc("SELECT kod, MD5(govde) h FROM lead_sablonlari WHERE kod IN ('" . implode("','", array_keys($eskiHash)) . "')") ?: [];
    $yeni = null;
    foreach ($satirlar as $r) {
        if (!isset($eskiHash[$r['kod']]) || !in_array($r['h'], $eskiHash[$r['kod']], true)) { continue; }   // düzenlenmiş veya zaten yenilenmiş
        $yeni ??= array_column(lead_varsayilan_sablonlar(), null, 'kod');
        if (!isset($yeni[$r['kod']])) { continue; }
        $y = $yeni[$r['kod']];
        $db->Update("UPDATE lead_sablonlari SET ad = ?, konu = ?, govde = ? WHERE kod = ?", [$y['ad'], $y['konu'] ?? null, $y['govde'], $r['kod']]);
    }
}

function lead_varsayilan_sablonlar(): array
{
    // Not: Metinlerde {yetkili} kullanılmaz (boşsa "Merhaba" döner). E-posta şablonlarında {ret_link} zorunludur.
    return [
        ['kanal' => 'whatsapp', 'kod' => 'wa_tanisma', 'ad' => '1. İlk tanışma',
         'govde' => "Merhaba, ben Prolyn'den {gonderen}.\n\nMutfak ve dolap projelerinizde ölçü karmaşasını ve eksik malzeme kaynaklı maliyetleri azaltmak ister misiniz?\n\nProlyn, mutfak, dolap ve banyo firmalarının günlük işini tek ekranda toplamak için geliştirildi:\n• Ölçü/montaj randevuları ve ekip planı\n• Eksik malzeme ve sorunlu iş takibi\n• Müşteri, sözleşme, tahsilat, kasa ve çek\n\n{firma} için 30 gün ücretsiz deneme hesabı açabilirim. Kurulumu ve mevcut müşteri listenizin aktarımını gizlilikle biz yapıyoruz.\n\nKısa tanıtım: {link}\n\nSistemi kendi iş akışınızda görmek isterseniz 10 dakikalık kısa bir ekran paylaşımıyla gösterebilirim; yüz yüze görüşmeyi tercih ederseniz randevu da talep edebilirsiniz. İlgilenmiyorsanız \"hayır\" yazmanız yeterli, tekrar yazmam."],
        ['kanal' => 'whatsapp', 'kod' => 'wa_takip', 'ad' => '2. Takip (3-5 gün sonra)',
         'govde' => "Merhaba, ben Prolyn'den {gonderen}. Geçen günkü mesajıma ek olarak, sistemin işleyişini kısaca özetlemek istedim:\n\nSabah panelde o günün ölçü ve montaj randevuları ekip ekip görünür. Montajda eksik çıkan parça aynı randevuya işlenir, iş bitince tahsilat müşterinin cari hesabına geçer. Defter, Excel ve WhatsApp grupları arasında kaybolan bilgi kalmaz.\n\nSistemi kendi iş akışınızda görmek isterseniz 10 dakikalık kısa bir ekran paylaşımıyla gösterebilirim; yüz yüze görüşmeyi tercih ederseniz randevu da talep edebilirsiniz. Size uygun gün ve saati yazmanız yeterli.\n\nAyrıntılar: {link}"],
        ['kanal' => 'whatsapp', 'kod' => 'wa_demo_sonrasi', 'ad' => '3. Görüşme sonrası deneme daveti',
         'govde' => "Merhaba, görüşmemiz için teşekkür ederim.\n\n30 günlük ücretsiz deneme hesabınızı buradan 2 dakikada açabilirsiniz:\n{kayit_link}\n\nKurulum ve müşteri listenizin aktarımı bizden: elinizdeki Excel listesini bu numaraya güvenle gönderebilirsiniz; müşteri bilgilerinizi gizlilikle, yalnızca sizin hesabınızda kullanılmak üzere aktarırız. İlk randevu ve sözleşme kayıtlarını birlikte girerek başlayabiliriz.\n\nAklınıza takılan her konuda bu numaradan yazabilirsiniz.\n\n{gonderen}\nProlyn"],
        ['kanal' => 'eposta', 'kod' => 'mail_tanisma', 'ad' => '1. İlk e-posta', 'konu' => '{firma} için ölçü ve montaj süreçlerini hızlandıracak bir öneri || Excel ve WhatsApp takibinden kurtulun ({firma})',
         'govde' => "Merhaba,\n\nBen Prolyn'den {gonderen}. Mutfak ve dolap projelerinizde ölçü karmaşasını ve eksik malzeme kaynaklı maliyetleri azaltmak ister misiniz?\n\nÇoğu mutfak, dolap ve banyo firmasında ölçü randevusu, montaj planı, eksik malzeme, sözleşme ve tahsilat defter, Excel ve WhatsApp grupları arasında dağılıyor. Prolyn bunları tek ve kolay kullanımlı bir panelde topluyor:\n\n- Ölçü ve montaj randevuları, ekiplerin günlük planı\n- Eksik malzeme ve sorunlu iş takibi\n- Müşteri, sözleşme, cari hesap, kasa, banka ve çek takibi\n- Şube ve personel bazında yetkilendirme\n\n{firma} için 30 günlük ücretsiz deneme hesabı açabiliriz. Kurulumu ve mevcut müşteri listenizin aktarımını gizlilikle biz yapıyoruz; kredi kartı istemiyoruz.\n\nKısa tanıtım ve ekran görüntüleri: {link}\n\nSistemi kendi iş akışınızda görmek isterseniz 10 dakikalık kısa bir ekran paylaşımıyla gösterebilirim; yüz yüze görüşmeyi tercih ederseniz randevu da talep edebilirsiniz. Bu e-postaya yanıt vermeniz yeterli.\n\nSaygılarımla,\n{gonderen}\nProlyn | {site}\n\n--\nBu e-postayı, Google Haritalar'da herkese açık yayımlanan işletme iletişim bilgileriniz üzerinden gönderdik. Bir daha almak istemezseniz: {ret_link}"],
        ['kanal' => 'eposta', 'kod' => 'mail_takip', 'ad' => '2. Takip e-postası', 'konu' => 'Günlük operasyonu kolaylaştıran kısa bir özet ({firma}) || {firma} için kısa bir hatırlatma',
         'govde' => "Merhaba,\n\nGeçen günkü e-postama ek olarak, sistemin işleyişini kısaca özetlemek istedim. Prolyn'de bir iş günü şöyle akıyor:\n\n1. Sabah panelde günün ölçü ve montaj randevuları ekip ekip listelenir.\n2. Montajda eksik çıkan parça aynı randevuya not edilir ve eksik özetinden takip edilir.\n3. İş bitince tahsilat aynı müşteri kaydına işlenir; cari bakiye ve kasa otomatik güncellenir.\n\nSonuç: kimin ne zaman nereye gittiği, hangi işin neyi eksik olduğu ve kimden ne kadar tahsilat kaldığı tek ekranda görünür.\n\nTanıtım sayfası: {link}\n\nSistemi kendi iş akışınızda görmek isterseniz 10 dakikalık kısa bir ekran paylaşımıyla gösterebilirim; yüz yüze görüşmeyi tercih ederseniz randevu da talep edebilirsiniz. Bu e-postaya yanıt vermeniz yeterli.\n\nSaygılarımla,\n{gonderen}\nProlyn | {site}\n\n--\nBir daha almak istemezseniz: {ret_link}"],
        ['kanal' => 'eposta', 'kod' => 'mail_son', 'ad' => '3. Son hatırlatma', 'konu' => 'Prolyn konusunu kapatmadan önce küçük bir soru || {firma} için dosyayı kapatıyorum',
         'govde' => "Merhaba,\n\nDaha önce iki kez yazdım ama dönüş alamadım; yoğunsunuzdur. Bu konuyu kapatmadan önce merak ettiğim bir şey var: Prolyn hakkında aklınıza yatmayan nedir?\n\nFiyat mı, kullanımın zorluğu mu, mevcut programınızdan geçiş mi, yoksa ihtiyacınıza uymaması mı? Tek satırla yazmanız yeterli; dürüst geri bildiriminiz bizim için çok değerli.\n\n\"Daha sonra\" yazarsanız birkaç ay sonra yalnızca bir kez hatırlatırım. Yanıt gelmezse konuyu kapatır ve tekrar yazmam.\n\nSaygılarımla,\n{gonderen}\nProlyn | {site}\n\nPS: Merak ederseniz, {firma} için 30 günlük ücretsiz deneme hesabını 2 dakikada açabilirsiniz (kredi kartı gerekmez): {kayit_link}\n\n--\nBir daha almak istemezseniz: {ret_link}"],
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

/**
 * Şablon konusunda " || " ile ayrılmış birden fazla konu varsa lead_id'ye göre sırayla biri seçilir (A/B testi).
 * Dönüş: [konu, varyant harfi]; tek konu varsa harf boş döner.
 */
function lead_konu_sec(string $konu, int $leadId): array
{
    $adaylar = array_values(array_filter(array_map('trim', explode('||', $konu)), static function ($k) { return $k !== ''; }));
    if (count($adaylar) <= 1) { return [$adaylar[0] ?? '', '']; }
    $i = $leadId % count($adaylar);
    return [$adaylar[$i], chr(65 + $i)];
}

/** E-postayı mail kuyruğuna ekler (ret başlıkları ile). */
function lead_mail_kuyruga_ekle($db, array $lead, array $sablon, string $gonderen): bool
{
    [$konuHam, $varyant] = lead_konu_sec((string)$sablon['konu'], (int)$lead['lead_id']);
    $konu = lead_sablon_doldur($konuHam, $lead, 'email', $gonderen);
    $govde = lead_sablon_doldur((string)$sablon['govde'], $lead, 'email', $gonderen);
    $retUrl = lead_ret_linki((string)$lead['ret_token']);
    $payload = json_encode(['headers' => [
        'List-Unsubscribe' => '<' . $retUrl . '>',
        'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
    ], 'lead_id' => (int)$lead['lead_id'], 'sablon' => $sablon['kod'], 'konu_varyant' => $varyant], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $ok = $db->Insert("INSERT INTO mail_kuyrugu (channel, recipient, subject, message, source, status, attempts, payload, created_at) VALUES ('email', ?, ?, ?, 'lead_kampanya', 'pending', 0, ?, NOW())",
        [$lead['eposta'], $konu, $govde, $payload]);
    if ($ok === false) { return false; }
    $db->Update("UPDATE potansiyel_musteriler SET mail_sayisi = mail_sayisi + 1, son_mail = NOW(), son_iletisim = NOW(),
                 durum = IF(durum = 'yeni', 'iletisime_gecildi', durum),
                 sonraki_takip = IF(sonraki_takip IS NULL OR sonraki_takip < CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), sonraki_takip)
                 WHERE lead_id = ?", [(int)$lead['lead_id']]);
    lead_aktivite($db, (int)$lead['lead_id'], 'eposta', 'Kuyruğa eklendi: ' . $sablon['ad'] . ($varyant !== '' ? ' [konu ' . $varyant . ']' : '') . ' → ' . $lead['eposta']);
    return true;
}

function lead_bugun_gonderilen_mail($db): int
{
    return (int)$db->getColumn("SELECT COUNT(*) FROM mail_kuyrugu WHERE source = 'lead_kampanya' AND created_at >= CURDATE()");
}

function lead_gonderen_adi(): string
{
    // Profil Detayları'ndaki "Kullanıcı Adı" doluysa o; boşsa "Tam Adı"; ikisi de yoksa "Prolyn Ekibi"
    $kullanici = trim((string)($_SESSION['username'] ?? ''));
    if ($kullanici !== '') { return $kullanici; }
    $tamAd = trim((string)($_SESSION['full_name'] ?? ''));
    return $tamAd !== '' ? $tamAd : 'Prolyn Ekibi';
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

/** Sosyal medya / mesajlaşma adresleri: gerçek web sitesi sayılmaz. */
const LEAD_SOSYAL_ALANLAR = ['instagram.com', 'facebook.com', 'fb.com', 'fb.me', 'linktr.ee', 'wa.me', 'tiktok.com', 'twitter.com', 'x.com', 'youtube.com', 'youtu.be'];

/**
 * Web alanını güvenli ve gösterilebilir hale getirir.
 * Dönüş: ['tur' => 'site'|'sosyal'|'', 'url' => yalnızca http(s), 'alan' => www'suz alan adı]
 */
function lead_web_bilgi(?string $web): array
{
    $web = trim((string)$web);
    if ($web === '') { return ['tur' => '', 'url' => '', 'alan' => '']; }
    if (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $web)) { $web = 'http://' . $web; }
    if (!preg_match('~^https?://~i', $web)) { return ['tur' => '', 'url' => '', 'alan' => '']; }   // javascript:, data: vb. reddedilir
    $host = strtolower((string)parse_url($web, PHP_URL_HOST));
    if ($host === '') { return ['tur' => '', 'url' => '', 'alan' => '']; }
    $alan = preg_replace('~^www\.~', '', $host);
    $sosyal = false;
    foreach (LEAD_SOSYAL_ALANLAR as $s) {
        if ($alan === $s || substr($alan, -strlen('.' . $s)) === '.' . $s) { $sosyal = true; break; }
    }
    return ['tur' => $sosyal ? 'sosyal' : 'site', 'url' => $web, 'alan' => $alan];
}

/** SQL parçası: web alanı sosyal medya adresi mi? Sabit listeden üretilir; kullanıcı girdisi içermez. (REGEXP: alan adı sınırı korunur, "netflix.com" x.com sayılmaz) */
function lead_sosyal_sql(): string
{
    $alanlar = implode('|', array_map(static function ($a) { return str_replace('.', '[.]', $a); }, LEAD_SOSYAL_ALANLAR));
    return "(web REGEXP '^(https?://)?([a-z0-9-]+[.])*(" . $alanlar . ")([/:?#]|$)')";
}
