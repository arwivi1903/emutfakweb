<?php
/**
 * Prolyn - prolyn_default şemasında eksik COLUMN COMMENT'leri ekler (Grup A, SADECE CLI)
 * ---------------------------------------------------------------------------------------
 * Kapsam: musteriler, randevular, silinenrandevular, sozlesmeler, sozlesme_sablonlari,
 * tahsilat, tahsilatcek, tahsilathavale, tahsilatkart, tahsilatnakit, taksitler,
 * ticarifatura, odemeler (198 kolon).
 *
 * Yorumlar, gerçek tenant ERP uygulamasının (/Applications/XAMPP/xamppfiles/htdocs/prolyn/)
 * kodu okunarak (özellikle islem.php, customeredit.php, cekbirlestir.php, cekupp.php,
 * taksitupp.php, ticaridetay.php, appedit.php) çıkarılmıştır — yalnızca kolon adından
 * tahmin edilmemiştir.
 *
 * Her kolonun tam mevcut tanımını information_schema'dan okur, sadece COMMENT ekleyerek
 * MODIFY COLUMN çalıştırır (tip/null/default/extra değişmez). Idempotent: tekrar
 * çalıştırmak güvenlidir.
 *
 * Çalıştırma: php p/database/2026_09_09_prolyn_default_comments_a.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../env.php';

$pdo = new PDO(
    'mysql:host=' . prolynweb_env('DB_HOST', '127.0.0.1') . ';dbname=prolyn_default;charset=utf8mb4',
    prolynweb_env('DB_USER', ''),
    prolynweb_env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$data = [
    'musteriler' => [
        'CustomerID' => 'Birincil anahtar',
        'CustomerNo' => 'Görünen müşteri numarası (fatura/rapor referansı; CustomerID ile aynı olmayabilir)',
        'CustomerType' => 'Müşteri tipi: 1 = Bireysel müşteri, 3 = Ticari (kurumsal) müşteri. Değere göre customeredit/customeradd formundaki alan seti değişir',
        'CustomerSales' => 'Cari yönü: 1 = Alış (tedarikçi), diğer = Satış (müşteri). ticarifatura ve dekont raporlarında bu alana göre Alış/Satış etiketlenir',
        'CustomerName' => 'Müşteri/firma adı',
        'CustomerPhone' => 'Birincil telefon numarası',
        'CustomerPhone2' => 'İkincil telefon numarası',
        'CustomerMail' => 'E-posta adresi',
        'CustomerTCVNo' => 'TC kimlik no (bireysel) veya vergi numarası (ticari)',
        'CustomerVD' => 'Vergi dairesi (ticari müşterilerde)',
        'CustomerCity' => 'İl',
        'CustomerTown' => 'İlçe',
        'CustomerAdress' => 'Açık adres',
        'CustomerPrint' => 'Fatura tercihi: 0 = Fatura Yok, 1 = Bireysel Fatura, 2 = Kurumsal Fatura (customeredit.php radio seçimi; faturaKaydiGerekli() bu değere bakar)',
        'CustomerDesc' => 'Serbest not/açıklama',
        'CreatedDate' => 'Kaydın oluşturulma zamanı',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'randevular' => [
        'AppID' => 'Birincil anahtar',
        'AppDate' => 'Randevu/montaj başlangıç tarihi',
        'AppDateEnd' => 'Randevu/montaj bitiş tarihi (çok günlük işlerde)',
        'AppDelivery' => 'Planlanan teslim tarihi',
        'AppOrderNo' => 'Sipariş/iş numarası (görünen referans)',
        'ContractNo' => 'İlişkili sözleşme numarası (serbest metin referans; asıl ilişki sozlesmeler.ContractID üzerinden değil burada metinsel tutulur)',
        'AppBranch' => 'subeler.BranchID — randevunun ait olduğu şube',
        'AppUser' => 'users.UserID — randevuyu oluşturan/sorumlu kullanıcı',
        'AppMontajUsta' => 'users.UserID — montajı yapacak usta',
        'AppCustomer' => 'musteriler.CustomerID — randevunun müşterisi',
        'AppPear' => "Eski/artık kullanılmayan alan: appedit.php formunda devre dışı bırakıldı, güncellemede yazılmıyor (bkz. islem.php 'VERİ KAYBI' notu)",
        'AppMetreTul' => "Eski/artık kullanılmayan alan: appedit.php formunda devre dışı bırakıldı, güncellemede yazılmıyor (bkz. islem.php 'VERİ KAYBI' notu)",
        'AppProduct' => 'Randevu konusu ürün/ürün grubu bilgisi',
        'AppStatus' => 'Randevu durum kodu (statusview() ile etiketlere çevrilir; nosend/eksik/minilist ekranları bu koda göre filtreler)',
        'AppDesc' => 'Randevu açıklaması/notu',
        'RecordDate' => 'Kaydın oluşturulma zamanı',
        'AppStatusUpdateDate' => 'Durumun en son değiştiği zaman',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş (silinince silinenrandevular tablosuna geçmiş kaydı da düşülür)',
    ],
    'silinenrandevular' => [
        'AppID' => 'Silinme öncesi randevular.AppID değeri (bu tabloda ayrıca auto_increment birincil anahtar olarak kullanılır)',
        'AppDate' => 'Randevunun silinmeden önceki başlangıç tarihi',
        'AppDateEnd' => 'Randevunun silinmeden önceki bitiş tarihi',
        'AppOrderNo' => 'Sipariş/iş numarası (silinme anındaki değer)',
        'AppBranch' => 'Şube bilgisi (silinme anındaki değer)',
        'AppUser' => 'Randevuyu oluşturan kullanıcı (silinme anındaki değer)',
        'AppMontajUsta' => 'Montaj ustası (silinme anındaki değer)',
        'AppCustomer' => 'Müşteri bilgisi (silinme anındaki değer)',
        'AppPhone' => 'Müşteri telefonu (silinme anında ayrıca saklanır; randevular tablosunda bu alan yoktur)',
        'il' => 'İl (silinme anındaki değer)',
        'ilce' => 'İlçe (silinme anındaki değer)',
        'AppPear' => 'Eski/artık aktif olarak kullanılmayan alan (bkz. randevular.AppPear)',
        'AppMetreTul' => 'Eski/artık aktif olarak kullanılmayan alan (bkz. randevular.AppMetreTul)',
        'AppProduct' => 'Ürün/ürün grubu bilgisi (silinme anındaki değer)',
        'AppStatus' => 'Silinme anındaki durum kodu',
        'AppDesc' => 'Randevu açıklaması (silinme anındaki değer)',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş (bu tablonun kendi kayıtları için)',
    ],
    'sozlesmeler' => [
        'ContractID' => 'Birincil anahtar',
        'AppUser' => 'users.UserID — sözleşmeyi oluşturan/satışı yapan kullanıcı',
        'CustomerID' => 'musteriler.CustomerID — sözleşmenin müşterisi',
        'ContractDate' => 'Sözleşmenin imzalandığı tarih',
        'AppDate' => 'İlişkili randevu/montaj tarihi',
        'DolapBoy' => 'Mutfak dolabı boy ölçüsü/seçeneği',
        'GovdeCinsi' => 'Dolap gövde malzemesi cinsi',
        'GovdeRenk' => 'Dolap gövde rengi',
        'KapakCinsi' => 'Dolap kapak malzemesi cinsi',
        'AltKapakRengi' => 'Alt kapak rengi',
        'UstKapakRengi' => 'Üst kapak rengi',
        'BoyKapakRengi' => 'Boy dolabı kapak rengi',
        'CamKapakModel' => 'Cam kapak modeli',
        'CamRengi' => 'Cam rengi',
        'AynaRengi' => 'Ayna rengi',
        'IsikBandi' => 'Işık bandı seçeneği',
        'TacBandi' => 'Taç bandı seçeneği',
        'Baza' => 'Baza seçeneği',
        'Kulp' => 'Kulp modeli/seçeneği',
        'Cekmece' => 'Çekmece sistemi seçeneği',
        'Mentese' => 'Menteşe modeli/seçeneği',
        'KalkarKapak' => 'Kalkar kapak mekanizması seçeneği',
        'Spotluk' => 'Spot aydınlatma seçeneği',
        'Yanlar' => 'Yan panel seçeneği',
        'MutfakToplam' => 'Mutfak bölümü toplam tutarı',
        'Evye' => 'Evye modeli/seçeneği',
        'EvyeToplam' => 'Evye bölümü toplam tutarı',
        'Aksesuar' => 'Seçilen aksesuarlar (serbest metin/liste)',
        'AksesuarToplam' => 'Aksesuar bölümü toplam tutarı',
        'Tezgah' => 'Tezgah malzemesi/modeli',
        'TezgahToplam' => 'Tezgah bölümü toplam tutarı',
        'BanyoDolap' => 'Banyo dolabı detay/seçenekleri (serbest metin)',
        'BanyoToplam' => 'Banyo dolabı bölümü toplam tutarı',
        'DigerDolap' => 'Diğer dolap kalemleri detayı (serbest metin)',
        'DolapToplam' => 'Diğer dolap kalemleri toplam tutarı',
        'Ankastre' => 'Ankastre ürün seçimi',
        'AnkastreToplam' => 'Ankastre bölümü toplam tutarı',
        'GenelToplam' => 'Tüm bölümlerin toplamı (iskonto uygulanmadan önce)',
        'Iskonto' => 'Uygulanan iskonto tutarı/oranı',
        'NetTutar' => 'İskonto sonrası net sözleşme tutarı; prim hesaplaması (personel_prim_orani) bu tutar üzerinden yapılır',
        'TaksitSayisi' => 'Sözleşmeye bağlı toplam taksit sayısı (taksitler.TaksitAdet ile karşılaştırılır; taksitupp.php taksit planı kurulunca güncellenir)',
        'ContractDesc' => 'Sözleşme ile ilgili serbest not',
        'CreatedDate' => 'Kaydın oluşturulma zamanı',
        'IsPrint' => 'Yazdırma kilidi: 1 = sözleşme en az bir kez yazdırıldı ve artık düzenlenemez (contractedit.php bu alana bakar), 0 = henüz yazdırılmadı, düzenlemeye açık',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'sozlesme_sablonlari' => [
        'SozlesmeID' => 'Birincil anahtar',
        'SozlesmeName' => 'Şablonun görünen adı (satış türüne göre seçilir)',
        'SozlesmeIcerik' => 'Şablonun tam metni/HTML içeriği (sözleşme yazdırılırken değişkenlerle birleştirilir)',
        'CreatedUserID' => 'users.UserID — şablonu oluşturan kullanıcı',
        'CreatedAt' => 'Şablonun oluşturulma zamanı',
        'UpdatedUserID' => 'users.UserID — şablonu en son düzenleyen kullanıcı',
        'UpdatedAt' => 'Şablonun son güncellenme zamanı',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'tahsilat' => [
        'TahsilatID' => 'Birincil anahtar',
        'UserID' => 'users.UserID — tahsilatı kaydeden kullanıcı',
        'InstallmentID' => 'taksitler.InstallmentID — tahsilatın karşıladığı taksit planı',
        'BankaID' => 'bankalar.BankaID — paranın girdiği banka hesabı',
        'CustomerID' => 'musteriler.CustomerID — tahsilatın müşterisi',
        'ContractID' => 'sozlesmeler.ContractID — tahsilatın ilişkili olduğu sözleşme',
        'AlinanTutar' => 'Tahsil edilen tutar (TL)',
        'OdemeTip' => 'Ödeme yöntemi kodu/etiketi (nakit/kart/havale/çek — detayları ilgili tahsilatnakit/tahsilatkart/tahsilathavale/tahsilatcek tablolarında tutulur)',
        'Durum' => 'Kayıt durumu: 1 = geçerli, 0 = iptal/pasif edilmiş (ör. birleştirme sonrası eski kayıtlar admincikiscek.php tarafından pasife çekilir)',
        'OdemeTarihi' => 'Ödemenin fiilen alındığı tarih',
        'BirlestirmeID' => 'cek_birlestirme.BirlestirmeID — bu tahsilat bir çek birleştirme işleminin sonucuysa ilişkili birleştirme kaydı',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'tahsilatcek' => [
        'CekTahsilatID' => 'Birincil anahtar',
        'UserID' => 'users.UserID — kaydı oluşturan kullanıcı',
        'InstallmentID' => 'taksitler.InstallmentID — ilişkili taksit',
        'CustomerID' => 'musteriler.CustomerID — çeki veren müşteri',
        'ContractID' => 'sozlesmeler.ContractID — ilişkili sözleşme',
        'AlinanTutar' => 'Çek tutarı (TL)',
        'OdemeTip' => 'Ödeme yöntemi etiketi',
        'Durum' => 'Genel durum: 1 = geçerli, 0 = iptal/pasif (cekupp.php bu alana göre silme/düzenleme izni verir)',
        'AdminDurum' => 'Yönetici onay durumu — Durum alanından bağımsız ayrı bir onay akışı (cekupp.php/admincekupp.php: 1 = düzenlenebilir/onay bekliyor; getFirmaCekStatusMeta() diğer değerleri etiketlere çevirir)',
        'BankaID' => 'bankalar.BankaID — çekin tahsil edileceği/edildiği banka',
        'BankaSubeID' => 'Bankanın şube bilgisi',
        'CekSahibi' => 'Çek üzerindeki keşideci/sahip adı',
        'CekNo' => 'Çek numarası',
        'CekVade' => 'Çekin vade tarihi',
        'TakasBankaID' => 'Çekin takasa girdiği/ciro edildiği banka (CekSahibi bankasından farklı olabilir)',
        'KasaDesc' => 'Kasa hareketi açıklaması (Ana Kasa entegrasyonunda kullanılır)',
        'CiroDesk' => 'Ciro/devir ile ilgili not',
        'OdemeTarihi' => 'Çekin işleme alındığı/tahsil edildiği tarih',
        'BirlestirmeID' => 'cek_birlestirme.BirlestirmeID — birden fazla taksite ait çek kaydı aynı fiziksel çeki temsil ediyorsa hepsi aynı BirlestirmeID ve CekNo ile işaretlenir (bkz. cekbirlestir.php)',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'tahsilathavale' => [
        'HavaleTahsilatID' => 'Birincil anahtar',
        'UserID' => 'users.UserID — kaydı oluşturan kullanıcı',
        'InstallmentID' => 'taksitler.InstallmentID — ilişkili taksit',
        'CustomerID' => 'musteriler.CustomerID — havaleyi gönderen müşteri',
        'ContractID' => 'sozlesmeler.ContractID — ilişkili sözleşme',
        'AlinanTutar' => 'Havale/EFT tutarı (TL)',
        'OdemeTip' => 'Ödeme yöntemi etiketi',
        'Durum' => 'Kayıt durumu: 1 = geçerli, 0 = iptal/pasif',
        'BankaID' => 'bankalar.BankaID — paranın girdiği banka hesabı',
        'OdemeTarihi' => 'Havalenin hesaba geçtiği tarih',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'tahsilatkart' => [
        'KartTahsilatID' => 'Birincil anahtar',
        'UserID' => 'users.UserID — kaydı oluşturan kullanıcı',
        'InstallmentID' => 'taksitler.InstallmentID — ilişkili taksit',
        'CustomerID' => 'musteriler.CustomerID — kart sahibi müşteri',
        'ContractID' => 'sozlesmeler.ContractID — ilişkili sözleşme',
        'AlinanTutar' => 'Kredi kartı/POS tahsilat tutarı (TL)',
        'OdemeTip' => 'Ödeme yöntemi etiketi',
        'Durum' => 'Kayıt durumu: 1 = geçerli, 0 = iptal/pasif',
        'BankaID' => 'bankalar.BankaID — POS işleminin bağlı olduğu banka',
        'OdemeTarihi' => 'Tahsilatın yapıldığı tarih',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'tahsilatnakit' => [
        'NakitTahsilatID' => 'Birincil anahtar',
        'UserID' => 'users.UserID — kaydı oluşturan kullanıcı',
        'InstallmentID' => 'taksitler.InstallmentID — ilişkili taksit',
        'CustomerID' => 'musteriler.CustomerID — nakit ödeyen müşteri',
        'ContractID' => 'sozlesmeler.ContractID — ilişkili sözleşme',
        'AlinanTutar' => 'Nakit tahsilat tutarı (TL)',
        'OdemeTip' => 'Ödeme yöntemi etiketi',
        'Durum' => 'Kayıt durumu: 1 = geçerli, 0 = iptal/pasif',
        'OdemeTarihi' => 'Tahsilatın yapıldığı tarih',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'taksitler' => [
        'InstallmentID' => 'Birincil anahtar',
        'CustomerID' => 'musteriler.CustomerID — taksit planının müşterisi',
        'UserID' => 'users.UserID — taksit planını oluşturan kullanıcı',
        'ContractID' => 'sozlesmeler.ContractID — taksitin ait olduğu sözleşme',
        'TaksitSayisi' => 'Sözleşmedeki toplam taksit sayısı (sozlesmeler.TaksitSayisi ile aynı değer, taksit planı kurulurken kopyalanır)',
        'TaksitAdet' => 'Bu satırın kaçıncı taksit olduğu (1, 2, 3, ... sırası)',
        'TaksitTutar' => 'Bu taksitin tutarı (TL)',
        'Durum' => 'Kayıt durumu: 1 = geçerli, 0 = iptal/pasif',
        'TaksitTarih' => 'Taksitin vade tarihi',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'ticarifatura' => [
        'TicariFaturaID' => 'Birincil anahtar',
        'FaturaNo' => 'Fatura numarası',
        'CustomerID' => 'musteriler.CustomerID — faturanın kesildiği/alındığı cari',
        'UserID' => 'users.UserID — faturayı kaydeden kullanıcı',
        'FirmaID' => 'firmalar.FirmaID — faturanın ait olduğu firma',
        'FaturaTip' => 'Fatura türü: 1 = Alış, 2 = Satış, 3 = Alış İade, 4 = Satış İade, 5 = Alış Dekontu, 6 = Satış Dekontu (ticaridetay.php raporlama bu koda göre gruplar)',
        'FaturaTutar' => 'Fatura tutarı (TL)',
        'FaturaTarih' => 'Fatura tarihi',
        'FaturaDosya' => 'Yüklenen fatura dosyasının adı/yolu',
        'FaturaDesc' => 'Fatura ile ilgili serbest not',
        'CreatedDate' => 'Kaydın oluşturulma zamanı',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'odemeler' => [
        'PayID' => 'Birincil anahtar',
        'ContractID' => 'sozlesmeler.ContractID — ödemenin ilişkili olduğu sözleşme',
        'CustomerID' => 'musteriler.CustomerID — ödemenin yapıldığı cari (CustomerSales=1 olan tedarikçi/alış tarafı müşteriler)',
        'OdemeAdet' => 'Ödemenin kaçıncı ödeme olduğu (taksitler.TaksitAdet ile benzer mantık)',
        'OdemeTutar' => 'Ödenen tutar (TL)',
        'OdemeTarihi' => 'Ödemenin yapıldığı tarih',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
];

$fixed = 0;
$errors = [];

foreach ($data as $table => $columns) {
    foreach ($columns as $col => $comment) {
        $stmt = $pdo->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLLATION_NAME
                                FROM information_schema.COLUMNS
                                WHERE TABLE_SCHEMA = 'prolyn_default' AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $col]);
        $meta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$meta) {
            $errors[] = "$table.$col: kolon bulunamadı";
            continue;
        }

        $def = "`$col` " . $meta['COLUMN_TYPE'];
        if ($meta['COLLATION_NAME']) {
            $def .= " COLLATE " . $meta['COLLATION_NAME'];
        }
        $def .= $meta['IS_NULLABLE'] === 'NO' ? ' NOT NULL' : ' NULL';

        $default = $meta['COLUMN_DEFAULT'];
        if ($default !== null && strtoupper(trim($default)) !== 'NULL') {
            if (in_array(strtoupper($default), ['CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP()'], true)) {
                $def .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif (is_numeric($default)) {
                $def .= " DEFAULT $default";
            } elseif (strlen($default) >= 2 && $default[0] === "'" && substr($default, -1) === "'") {
                $def .= " DEFAULT $default";
            } else {
                $def .= ' DEFAULT ' . $pdo->quote($default);
            }
        } elseif ($meta['IS_NULLABLE'] === 'YES') {
            $def .= ' DEFAULT NULL';
        }

        if (stripos($meta['EXTRA'], 'on update current_timestamp') !== false) {
            $def .= ' ON UPDATE CURRENT_TIMESTAMP';
        }
        if (stripos($meta['EXTRA'], 'auto_increment') !== false) {
            $def .= ' AUTO_INCREMENT';
        }

        $def .= ' COMMENT ' . $pdo->quote($comment);

        $sql = "ALTER TABLE `$table` MODIFY COLUMN $def";
        try {
            $pdo->exec($sql);
            $fixed++;
        } catch (Throwable $e) {
            $errors[] = "$table.$col: " . $e->getMessage() . " | SQL: $sql";
        }
    }
}

echo "Düzeltilen kolon sayısı: $fixed\n";
if ($errors) {
    echo "Hatalar:\n" . implode("\n", $errors) . "\n";
}
