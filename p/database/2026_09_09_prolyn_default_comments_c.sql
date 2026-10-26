-- prolyn_default içindeki finans (kasa/banka/çek), dosya, log ve genel
-- referans tablolarında (bankalar, kasahareketleri, cek_birlestirme*, log*,
-- dosyalar, edosyalar, faturafile, faturalar, duyurular, iller, ilceler,
-- musteri_ziyaret) COLUMN COMMENT eksikti. /Applications/XAMPP/xamppfiles/htdocs/prolyn/
-- (asıl ERP uygulaması) koduna bakılarak her kolonun gerçek anlamı
-- doğrulandı ve yorumlar eklendi. Veri veya kolon tipi değişmedi.
--
-- Not: log/logtypes tabloları lognew/logtypesnew'in eskisi — yerini
-- almış durumda ama geriye dönük uyumluluk için hâlâ birkaç ekranda
-- kullanılıyor (bkz. functions/logtut.php).

ALTER TABLE `bankalar` MODIFY COLUMN `BankID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `bankalar` MODIFY COLUMN `FirmaID` int(11) NOT NULL COMMENT 'firmalar.FirmaID — hesabın ait olduğu firma';
ALTER TABLE `bankalar` MODIFY COLUMN `TotalTutar` decimal(15,2) DEFAULT NULL COMMENT 'Hesabın güncel bakiyesi (TL); virman ve tahsilat/ödeme işlemleriyle otomatik güncellenir';
ALTER TABLE `bankalar` MODIFY COLUMN `IBAN` varchar(255) DEFAULT NULL COMMENT 'Hesap IBAN numarası; sistemde ve firma bazında benzersiz olmalıdır';
ALTER TABLE `bankalar` MODIFY COLUMN `Durum` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Hesap durumu: 0 = pasif, 1 = aktif (bankaiban.php üzerinden değiştirilir)';
ALTER TABLE `bankalar` MODIFY COLUMN `GüncelTarih` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Son güncellenme zamanı';
ALTER TABLE `bankalar` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `cek_birlestirme` MODIFY COLUMN `BirlestirmeID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `UserID` int(11) NOT NULL COMMENT 'users.UserID — birleştirmeyi yapan kullanıcı';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `CustomerID` int(11) NOT NULL COMMENT 'musteriler.CustomerID — çeklerin ait olduğu müşteri';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `ContractID` int(11) NOT NULL COMMENT 'sozlesmeler.ContractID — çeklerin bağlı olduğu sözleşme';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `ToplamTutar` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Birleştirilen çeklerin toplam tutarı (TL)';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `BankaID` int(11) DEFAULT NULL COMMENT 'bankalar.BankaID — birleştirilen çekin tahsil edileceği/edildiği banka hesabı';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `BankaSubeID` int(11) DEFAULT NULL COMMENT 'Bankanın şube kimliği (banka API/JSON referans listesinden)';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `CekSahibi` varchar(255) DEFAULT NULL COMMENT 'Çek üzerindeki keşideci/hamil adı';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `CekNo` varchar(100) DEFAULT NULL COMMENT 'Çek numarası';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `CekVade` date DEFAULT NULL COMMENT 'Çekin vade (ödeme) tarihi';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `OdemeTarihi` date NOT NULL COMMENT 'Birleştirme işleminin fiilen yapıldığı tarih';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `AdminDurum` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'İşlem durumu: 1 = normal/onaylı (diğer değerler iptal/beklemede olabilir)';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kaydın oluşturulma zamanı';
ALTER TABLE `cek_birlestirme` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `DetayID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `BirlestirmeID` int(11) NOT NULL COMMENT 'cek_birlestirme.BirlestirmeID — ait olduğu birleştirme işlemi';
ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `InstallmentID` int(11) NOT NULL COMMENT 'taksitler.InstallmentID — birleştirmeye dahil edilen taksit';
ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `TaksitKalan` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Birleştirme anında taksitten kalan bakiye (TL)';
ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `AlinanTutar` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Bu birleştirme ile taksitten tahsil edilen tutar (TL)';
ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `KismiMi` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Kısmi tahsilat mı: 1 = taksit tam kapanmadı (kısmi), 0 = taksit tamamen kapatıldı';
ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kaydın oluşturulma zamanı';
ALTER TABLE `cek_birlestirme_detay` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';
ALTER TABLE `cek_birlestirme_detay` COMMENT = 'Bir çek birleştirme işlemine dahil edilen taksitlerin satır satır dökümü (cek_birlestirme ile 1-N ilişkili)';

ALTER TABLE `kasahareketleri` MODIFY COLUMN `KasaHareketID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `AdminStatus` tinyint(11) NOT NULL DEFAULT 0 COMMENT 'İşlem durumu: 0 = normal/aktif kayıt (raporlarda kullanılan filtre); farklı değerler özel/iptal durumları işaretler';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `UserID` int(11) DEFAULT NULL COMMENT 'users.UserID — hareketin ait olduğu kullanıcı/şube kasası';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `TahsilatID` int(11) DEFAULT 0 COMMENT 'tahsilat.TahsilatID — hareket bir tahsilattan doğduysa referansı; doğrudan kasa girişi/gideri ise 0';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `KasaTip` tinyint(4) DEFAULT NULL COMMENT 'Hareket yönü ve kaynağı kodu: 1 = şube kasa girişi, 2 = şube kasa çıkışı, 3 = ana kasa girişi, 4 = ana kasa çıkışı (Yon/KasaKaynak kolonları bu değerden türetilir)';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `IslemTipi` tinyint(4) DEFAULT NULL COMMENT 'kasatips.KasaTipsID — işlemin gelir/gider alt tipi (bkz. kasatips tablosu, örn. Nakit, Kira, Maaş)';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `Detay` varchar(255) DEFAULT NULL COMMENT 'kasatips.KasaTipsID — işlemin ikinci taraf/asıl detay kodu (metin olarak saklanır); bazı özel kodlar (örn. 1003) belirli raporlarda hariç tutulur';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `KasaTutar` decimal(15,2) DEFAULT NULL COMMENT 'İşlem tutarı (TL)';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `KasaDesc` varchar(255) DEFAULT NULL COMMENT 'Serbest metin açıklama';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `TransferTarih` datetime DEFAULT current_timestamp() COMMENT 'İşlemin gerçekleştiği tarih/saat';
ALTER TABLE `kasahareketleri` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `kasatips` MODIFY COLUMN `KasaTipsID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar; kasahareketleri.IslemTipi/Detay ve zrapor.php gibi ekranlarda bu ID referans alınır';
ALTER TABLE `kasatips` MODIFY COLUMN `KasaTip` tinyint(4) DEFAULT NULL COMMENT 'Kategori: 1 = gelir/tahsilat türü (Nakit, Kredi Kartı, Çek vb.), 2 = gider türü (Kira, Maaş, Prim vb.)';
ALTER TABLE `kasatips` MODIFY COLUMN `KasaDetay` varchar(255) DEFAULT NULL COMMENT 'Türün görünen adı (örn. "Nakit", "Kira", "Personel Servis")';
ALTER TABLE `kasatips` MODIFY COLUMN `CreadetDate` datetime DEFAULT current_timestamp() COMMENT 'Kaydın oluşturulma zamanı';
ALTER TABLE `kasatips` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `nakitkasa` MODIFY COLUMN `NakitKasaID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `nakitkasa` MODIFY COLUMN `FirmaID` int(11) NOT NULL COMMENT 'firmalar.FirmaID — nakit kasanın ait olduğu firma';
ALTER TABLE `nakitkasa` MODIFY COLUMN `NakitKasaTutar` decimal(15,2) NOT NULL COMMENT 'Güncel nakit kasa bakiyesi (TL)';
ALTER TABLE `nakitkasa` MODIFY COLUMN `UpdateDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Son güncellenme zamanı';
ALTER TABLE `nakitkasa` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `virmanhareket` MODIFY COLUMN `VirmanID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `virmanhareket` MODIFY COLUMN `UserID` int(11) DEFAULT NULL COMMENT 'users.UserID — virmanı yapan kullanıcı';
ALTER TABLE `virmanhareket` MODIFY COLUMN `SendBank` int(11) DEFAULT NULL COMMENT 'bankalar.BankaID — paranın çıktığı hesap';
ALTER TABLE `virmanhareket` MODIFY COLUMN `ReceiveBank` int(11) DEFAULT NULL COMMENT 'bankalar.BankaID — paranın girdiği hesap';
ALTER TABLE `virmanhareket` MODIFY COLUMN `Amount` decimal(15,2) DEFAULT NULL COMMENT 'Transfer edilen tutar (TL)';
ALTER TABLE `virmanhareket` MODIFY COLUMN `VirmanDate` datetime DEFAULT current_timestamp() COMMENT 'İşlem tarihi';
ALTER TABLE `virmanhareket` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `dosyalar` MODIFY COLUMN `FileID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `dosyalar` MODIFY COLUMN `FileAppID` int(11) DEFAULT NULL COMMENT 'randevular.AppID — dosyanın ilişkili olduğu randevu/iş kaydı';
ALTER TABLE `dosyalar` MODIFY COLUMN `FileAuthorID` int(11) DEFAULT NULL COMMENT 'users.UserID — dosyayı yükleyen kullanıcı';
ALTER TABLE `dosyalar` MODIFY COLUMN `FilePath` mediumtext DEFAULT NULL COMMENT 'Dosyanın sunucudaki fiziksel/URL yolu';
ALTER TABLE `dosyalar` MODIFY COLUMN `FileName` mediumtext DEFAULT NULL COMMENT 'Orijinal dosya adı';
ALTER TABLE `dosyalar` MODIFY COLUMN `FileType` mediumtext DEFAULT NULL COMMENT 'Dosya MIME tipi veya uzantısı';
ALTER TABLE `dosyalar` MODIFY COLUMN `FileSize` longtext DEFAULT NULL COMMENT 'Dosya boyutu';
ALTER TABLE `dosyalar` MODIFY COLUMN `FileCreated` datetime DEFAULT NULL COMMENT 'Yükleme zamanı';
ALTER TABLE `dosyalar` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `edosyalar` MODIFY COLUMN `NonFileID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `edosyalar` MODIFY COLUMN `NonFileAppID` int(11) DEFAULT NULL COMMENT 'randevular.AppID — belgenin ilişkili olduğu randevu/iş kaydı';
ALTER TABLE `edosyalar` MODIFY COLUMN `NonFileAuthorID` int(11) DEFAULT NULL COMMENT 'users.UserID — belgeyi yükleyen kullanıcı';
ALTER TABLE `edosyalar` MODIFY COLUMN `NonFilePath` mediumtext DEFAULT NULL COMMENT 'Belgenin sunucudaki fiziksel/URL yolu';
ALTER TABLE `edosyalar` MODIFY COLUMN `NonFileName` mediumtext DEFAULT NULL COMMENT 'Orijinal belge adı';
ALTER TABLE `edosyalar` MODIFY COLUMN `NonFileType` mediumtext DEFAULT NULL COMMENT 'Belge MIME tipi veya uzantısı';
ALTER TABLE `edosyalar` MODIFY COLUMN `NonFileSize` longtext DEFAULT NULL COMMENT 'Belge boyutu';
ALTER TABLE `edosyalar` MODIFY COLUMN `NonFileCreated` datetime DEFAULT NULL COMMENT 'Yükleme zamanı';
ALTER TABLE `edosyalar` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `faturafile` MODIFY COLUMN `FaturaID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `faturafile` MODIFY COLUMN `UserID` int(11) DEFAULT NULL COMMENT 'users.UserID — faturayı yükleyen/gönderen kullanıcı';
ALTER TABLE `faturafile` MODIFY COLUMN `AppUser` int(11) NOT NULL COMMENT 'users.UserID — faturanın muhatabı olan kullanıcı (rozet/bildirim sayacı bu alana göre süzülür)';
ALTER TABLE `faturafile` MODIFY COLUMN `CustomerID` int(11) DEFAULT NULL COMMENT 'musteriler.CustomerID — faturanın ilişkili olduğu müşteri';
ALTER TABLE `faturafile` MODIFY COLUMN `FaturaNo` varchar(255) DEFAULT NULL COMMENT 'Fatura numarası';
ALTER TABLE `faturafile` MODIFY COLUMN `FaturaDosya` varchar(255) DEFAULT NULL COMMENT 'Fatura PDF/görsel dosyasının yolu';
ALTER TABLE `faturafile` MODIFY COLUMN `FatBildirim` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Bildirim/okunma durumu: 1 = okunmadı (üst menüde rozet olarak sayılır), 0 = görüntülendi';
ALTER TABLE `faturafile` MODIFY COLUMN `UpdateDate` datetime DEFAULT current_timestamp() COMMENT 'Yükleme/son güncellenme zamanı';
ALTER TABLE `faturafile` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `faturalar` MODIFY COLUMN `PrintID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `faturalar` MODIFY COLUMN `CustomerID` int(11) DEFAULT NULL COMMENT 'musteriler.CustomerID — faturanın kesildiği müşteri';
ALTER TABLE `faturalar` MODIFY COLUMN `CompanyName` varchar(255) DEFAULT NULL COMMENT 'Fatura üzerinde basılan firma unvanı';
ALTER TABLE `faturalar` MODIFY COLUMN `CompanyVNo` varchar(255) DEFAULT NULL COMMENT 'Fatura üzerinde basılan vergi numarası';
ALTER TABLE `faturalar` MODIFY COLUMN `CompanyVD` varchar(255) DEFAULT NULL COMMENT 'Fatura üzerinde basılan vergi dairesi';
ALTER TABLE `faturalar` MODIFY COLUMN `CreatedDate` datetime DEFAULT current_timestamp() COMMENT 'Faturanın oluşturulma/yazdırılma zamanı';
ALTER TABLE `faturalar` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `duyurular` MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `duyurular` MODIFY COLUMN `baslik` varchar(255) NOT NULL COMMENT 'Duyuru başlığı';
ALTER TABLE `duyurular` MODIFY COLUMN `icerik` text NOT NULL COMMENT 'Duyuru içeriği';
ALTER TABLE `duyurular` MODIFY COLUMN `sayfa` varchar(255) NOT NULL COMMENT 'Duyurunun gösterileceği sayfa/ekran anahtarı (tüm sistemde göstermek için genel bir değer kullanılabilir)';
ALTER TABLE `duyurular` MODIFY COLUMN `aktif` tinyint(1) DEFAULT 1 COMMENT 'Yayın durumu: 1 = aktif/gösteriliyor, 0 = pasif';
ALTER TABLE `duyurular` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `log` MODIFY COLUMN `LogID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `log` MODIFY COLUMN `UserID` int(11) DEFAULT NULL COMMENT 'users.UserID — işlemi yapan kullanıcı';
ALTER TABLE `log` MODIFY COLUMN `LogType` text DEFAULT NULL COMMENT 'İşlem tipi (serbest metin/etiket)';
ALTER TABLE `log` MODIFY COLUMN `LogDesc` mediumtext DEFAULT NULL COMMENT 'İşlem açıklaması';
ALTER TABLE `log` MODIFY COLUMN `LogDate` datetime DEFAULT current_timestamp() COMMENT 'İşlem zamanı';
ALTER TABLE `log` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';
ALTER TABLE `log` COMMENT = 'ESKİ basit sistem log tablosu — yerini lognew\'e bıraktı, geriye dönük uyumluluk için hâlâ birkaç ekranda kullanılıyor';

ALTER TABLE `lognew` MODIFY COLUMN `LogID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `lognew` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `logtypes` MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar; log.LogType ile ilişkilendirilir';
ALTER TABLE `logtypes` MODIFY COLUMN `names` varchar(255) DEFAULT NULL COMMENT 'İşlem tipinin görünen adı (örn. Ekleme, Silme)';
ALTER TABLE `logtypes` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';
ALTER TABLE `logtypes` COMMENT = 'ESKİ log tipi sözlüğü — log tablosunun karşılığı, yerini logtypesnew\'e bıraktı';

ALTER TABLE `logtypesnew` MODIFY COLUMN `id` tinyint(3) unsigned NOT NULL COMMENT 'Birincil anahtar; lognew.LogType ile ilişkilendirilir (functions/logtut.php > xlogs() tarafından kullanılır: 1 Error, 2 Warning, 3 Admin/Success, 4 Info, 5 Debug)';
ALTER TABLE `logtypesnew` MODIFY COLUMN `names` varchar(100) NOT NULL COMMENT 'İşlem tipinin görünen adı';
ALTER TABLE `logtypesnew` MODIFY COLUMN `description` varchar(255) DEFAULT NULL COMMENT 'İşlem tipinin açıklaması';
ALTER TABLE `logtypesnew` MODIFY COLUMN `color` varchar(20) DEFAULT 'primary' COMMENT 'Arayüzde kullanılacak rozet/rengi (bootstrap renk adı, örn. primary, danger)';
ALTER TABLE `logtypesnew` MODIFY COLUMN `icon` varchar(50) DEFAULT 'fas fa-info-circle' COMMENT 'Arayüzde kullanılacak ikon sınıfı (Font Awesome)';
ALTER TABLE `logtypesnew` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `ilceler` MODIFY COLUMN `ilce_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `ilceler` MODIFY COLUMN `ilceadi` varchar(255) NOT NULL COMMENT 'İlçe adı';
ALTER TABLE `ilceler` MODIFY COLUMN `iller_id` int(11) NOT NULL COMMENT 'iller.iller_id — ilçenin bağlı olduğu il';
ALTER TABLE `ilceler` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `iller` MODIFY COLUMN `iller_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `iller` MODIFY COLUMN `sehiradi` varchar(255) DEFAULT NULL COMMENT 'İl adı';
ALTER TABLE `iller` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

ALTER TABLE `musteri_ziyaret` MODIFY COLUMN `ZiyaretID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';
ALTER TABLE `musteri_ziyaret` MODIFY COLUMN `CustomerID` int(11) NOT NULL COMMENT 'musteriler.CustomerID — ziyaret edilen müşteri';
ALTER TABLE `musteri_ziyaret` MODIFY COLUMN `AppID` int(11) NOT NULL COMMENT 'randevular.AppID — ziyaretin ilişkili olduğu randevu';
ALTER TABLE `musteri_ziyaret` MODIFY COLUMN `ZiyaretCount` int(11) DEFAULT 1 COMMENT 'Bu müşteri için toplam ziyaret sayısı';
ALTER TABLE `musteri_ziyaret` MODIFY COLUMN `LastVisit` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Son ziyaret zamanı';
ALTER TABLE `musteri_ziyaret` MODIFY COLUMN `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş';

