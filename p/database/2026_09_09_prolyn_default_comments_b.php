<?php
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../../env.php';

$pdo = new PDO('mysql:host=' . prolynweb_env('DB_HOST','127.0.0.1') . ';dbname=prolyn_default;charset=utf8mb4', prolynweb_env('DB_USER',''), prolynweb_env('DB_PASS',''), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$DB = 'prolyn_default';

$data = [
    'users' => [
        'UserID' => 'Birincil anahtar',
        'FirmaID' => 'firmalar.FirmaID — kullanıcının bağlı olduğu firma',
        'UserName' => 'Kullanıcının adı soyadı',
        'UserMail' => 'Giriş için kullanılan e-posta adresi (benzersiz)',
        'UserPhone' => 'Telefon numarası',
        'UserPass' => 'Hash\'lenmiş parola',
        'UserAuthority' => 'Admin yetkisi: 1 = Var (tüm işlemleri yapabilir), 2 = Yok (normal kullanıcı)',
        'UserGroupID' => 'usergrup.UserGrupID — kullanıcının rolü/izin grubu',
        'BranchID' => 'subeler.BranchID — kullanıcının bağlı olduğu şube',
        'UserStatus' => 'Hesap durumu: 1 = Aktif, 2 = Pasif',
        'UserPicture' => 'Profil fotoğrafı dosya yolu',
        'SessionMail' => 'Oturum doğrulama için şifrelenmiş e-posta (sifreleme()); $_SESSION değeriyle karşılaştırılarak oturumun geçerliliği kontrol edilir',
        'IpAdress' => 'Son başarılı girişte kaydedilen IP adresi',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'usergrup' => [
        'UserGrupID' => 'Birincil anahtar',
        'UserGrupName' => 'Rol adı (örn. Admin, Satış, Montaj)',
        'UserGrupDesc' => 'Rolün açıklaması',
        'UserGrupStatus' => 'Durum: 1 = aktif',
        'permissions' => 'Bu role tanımlı izin anahtarlarının JSON dizisi; Auth sınıfı ve oturumdaki user_permissions ile karşılaştırılır',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'firmalar' => [
        'FirmaID' => 'Birincil anahtar',
        'CustomerID' => 'Muhtemelen merkez (prolyn_master) veritabanındaki kurumlar.customer_id referansı; canlı kodda yeni kayıt akışında set edilmiyor, sınırlı/eski kullanım',
        'FirmaAdi' => 'Firma/şirket adı',
        'FirmaDetay' => 'Firma hakkında ek açıklama',
        'FirmaLogo' => 'Firma logosu dosya yolu',
        'FirmaVergi' => 'Vergi dairesi',
        'FirmaVNO' => 'Vergi numarası veya TC kimlik numarası',
        'FirmaCity' => 'İl',
        'FirmaTown' => 'İlçe',
        'FirmaAdres' => 'Açık adres',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'subeler' => [
        'BranchID' => 'Birincil anahtar',
        'FirmaID' => 'firmalar.FirmaID — şubenin bağlı olduğu firma',
        'CustomerID' => 'Nadiren kullanılan eski bir alan; yeni şube ekleme akışında (branchadd.php) set edilmiyor',
        'BranchName' => 'Şube adı',
        'BranchResmi' => 'Şube görseli/fotoğrafı dosya yolu',
        'BranchShort' => 'Şube kısa adı/kodu',
        'BranchArea' => 'Şubenin bulunduğu bölge/semt',
        'BranchDesc' => 'Şube açıklaması',
        'BranchPhone' => 'Şube telefon numarası',
        'BranchAuthorized' => 'Şube yetkilisinin adı',
        'BranchStatus' => 'Durum: 1 = aktif',
        'BranchMail' => 'Şube e-posta adresi',
        'BranchAdres' => 'Şube açık adresi',
        'BranchLogo' => 'Şube logosu dosya yolu',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'ayarlar' => [
        'MetaID' => 'Birincil anahtar',
        'SiteTitle' => 'Panel/site başlığı',
        'SiteDesc' => 'Site açıklaması (meta description)',
        'SiteAuthor' => 'Site yazarı/sahibi meta bilgisi',
        'SiteSeo' => 'Ek SEO meta metni',
        'SiteKeyword' => 'SEO anahtar kelimeleri',
        'SiteLogo' => 'Site logosu dosya yolu',
        'SiteAvatar' => 'Favicon/avatar dosya yolu',
        'CompanyName' => 'Şirket adı',
        'SitePhone' => 'İletişim telefonu',
        'SiteEmail' => 'İletişim e-postası',
        'SiteURL' => 'Site adresi',
        'SiteAddress' => 'Şirket adresi',
        'MetaRobots' => 'robots meta etiketi değeri (örn. index,follow)',
        'SiteLanguage' => 'Site dili kodu',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'admincek' => [
        'CekID' => 'Birincil anahtar',
        'CekTahsilatID' => 'İlgili tahsilat kaydına referans (çek bu tahsilatla ilişkilendirildiyse dolu)',
        'AdminStatus' => 'Yönetici onay/işlem durumu',
        'UserID' => 'users.UserID — çeki kaydeden/işleyen kullanıcı',
        'CustomerID' => 'musteriler.CustomerID — çekin ait olduğu müşteri',
        'IslemTip' => 'İşlem tipi (çek girişi/çıkışı vb.)',
        'Tutar' => 'Çek tutarı',
        'OdemeTip' => 'Ödeme/tahsil tipi',
        'Durum' => 'Çekin genel durumu (bekliyor/tahsil edildi/karşılıksız vb.)',
        'AdminDurum' => 'Yönetici tarafındaki ayrı durum alanı (admincekupp.php akışında kullanılır)',
        'BankaID' => 'bankalar.BankaID — çekin ait olduğu banka',
        'BankaSubeID' => 'Banka şubesi',
        'CekSahibi' => 'Çek/senet üzerindeki sahibin adı',
        'CekNo' => 'Çek/senet numarası',
        'CekVade' => 'Çekin vade (ödeme) tarihi',
        'TakasBankaID' => 'Çekin tahsil için gönderildiği takas bankası',
        'KasaDesc' => 'Kasa hareketi açıklaması',
        'CreatedDate' => 'Kaydın oluşturulma tarihi',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'adminkasa' => [
        'AdminKasaID' => 'Birincil anahtar',
        'UserID' => 'users.UserID — kasa hareketini yapan kullanıcı',
        'AdminKasaTip' => 'Hareket tipi (örn. 4 = firma çeki tahsilatı; adminfirmacek.php tarafından kullanılır)',
        'AdminKasaDetay' => 'Hareketin alt tipi/detay kodu',
        'AdminDesc' => 'Hareket açıklaması',
        'AdminKasaTutar' => 'Hareket tutarı (TL)',
        'AdminRecordDate' => 'Hareketin kaydedilme tarihi',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'stok_hareketleri' => [
        'HareketID' => 'Birincil anahtar',
        'FirmaID' => 'firmalar.FirmaID — hareketin ait olduğu firma',
        'StokID' => 'stok_kalemleri.StokID — hareketin ait olduğu stok kalemi',
        'Miktar' => 'Hareket miktarı (giriş için pozitif, çıkış için negatif olabilir)',
        'OncekiMiktar' => 'Hareket öncesi stok miktarı (denetim/geri alma için anlık görüntü)',
        'SonrakiMiktar' => 'Hareket sonrası stok miktarı (denetim/geri alma için anlık görüntü)',
        'Aciklama' => 'Hareketin serbest metin açıklaması',
        'UserID' => 'users.UserID — hareketi yapan kullanıcı',
        'CustomerID' => 'musteriler.CustomerID — hareketin ilişkili olduğu müşteri (varsa)',
        'ContractID' => 'sozlesmeler.ContractID — hareketin ilişkili olduğu sözleşme (varsa)',
        'ProjectNo' => 'İlgili proje/iş numarası',
        'HareketTarih' => 'Hareketin gerçekleştiği tarih',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'stok_kalemleri' => [
        'StokID' => 'Birincil anahtar',
        'FirmaID' => 'firmalar.FirmaID — stok kaleminin ait olduğu firma',
        'StokKod' => 'Stok kartı kodu (SKU)',
        'StokAd' => 'Stok kalemi adı',
        'Birim' => 'Ölçü birimi (örn. ADET, KG, M2)',
        'Fiyat' => 'Birim fiyat',
        'MevcutMiktar' => 'Güncel stok miktarı (stok_hareketleri ile senkron tutulur)',
        'StokDurum' => 'Durum: 1 = aktif',
        'Grup' => 'Stok grubu/kategorisi (serbest metin)',
        'Grubu' => 'Grup alanının alternatif/eski yazımı; ikisi bir arada bulunuyor, hangisinin güncel kullanıldığı ekran bazında değişebilir',
        'MinMiktar' => 'Kritik stok seviyesi — bu değerin altına düşünce uyarı amaçlı kullanılır',
        'Aciklama' => 'Serbest metin açıklama',
        'CreatedBy' => 'users.UserID — kaydı oluşturan kullanıcı',
        'CreatedDate' => 'Kaydın oluşturulma tarihi',
        'UpdatedDate' => 'Kaydın son güncellenme tarihi',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'urunler' => [
        'ProductID' => 'Birincil anahtar',
        'ProductName' => 'Ürün adı (randevu/sözleşme satırlarında seçilen ürün kataloğu — stok_kalemleri\'nden bağımsız, basit bir ürün listesi)',
        'ProductDesc' => 'Ürün açıklaması',
        'ProductStatus' => 'Durum: 1 = aktif',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'urungrup' => [
        'UrunGrupID' => 'Birincil anahtar',
        'UrunGrupName' => 'Ürün grup/kategori adı',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş; NOT: bu tablo canlı kod tabanında hiçbir yerde kullanılmıyor (muhtemelen kullanılmayan/eski bir tablo)',
    ],
    'dolapboy' => [
        'DolapBoyID' => 'Birincil anahtar',
        'DolapBoyName' => 'Dolap boyu tanımı adı (üretim/mutfak konfigürasyonunda kullanılır)',
        'bDolapStandart' => 'Standart bir dolap boyu mu (1) yoksa özel ölçü mü (0)',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'govdecinsi' => [
        'GovdeCinsiID' => 'Birincil anahtar',
        'GovdeCinsiName' => 'Gövde cinsi/malzeme tanımı adı',
        'bGovdeStandart' => 'Standart bir gövde cinsi mi (1) yoksa özel mi (0)',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'govderenk' => [
        'GovdeRenkID' => 'Birincil anahtar',
        'GovdeRenkName' => 'Gövde renk tanımı adı',
        'bGRenkStandart' => 'Standart bir gövde rengi mi (1) yoksa özel mi (0)',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'kapakcinsi' => [
        'KapakCinsiID' => 'Birincil anahtar',
        'KapakCinsiName' => 'Kapak cinsi/malzeme tanımı adı',
        'bKapakStandart' => 'Standart bir kapak cinsi mi (1) yoksa özel mi (0)',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
    'montaj_problemler' => [
        'ProblemID' => 'Birincil anahtar',
        'CreatedAt' => 'Kaydın oluşturulma zamanı',
        'UpdatedAt' => 'Kaydın son güncellenme zamanı',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ],
];

$fixed = 0;
$errors = [];

foreach ($data as $table => $columns) {
    foreach ($columns as $col => $comment) {
        $stmt = $pdo->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLLATION_NAME
                                FROM information_schema.COLUMNS
                                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$DB, $table, $col]);
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
