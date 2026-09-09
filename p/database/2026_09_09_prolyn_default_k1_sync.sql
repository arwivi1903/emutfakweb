-- prolyn_default (yeni kiracı sağlarken şablon olarak klonlanan veritabanı)
-- ile prolyn_k1 (canlı, güncel kiracı — ELIT MUTFAK) arasındaki şema
-- farkları karşılaştırıldı. Amaç: prolyn_default'ın güncel kalması,
-- aksi halde yeni kayıt olan her kiracı eksik tablo/kolonla başlıyor.
--
-- Bulunan fark: personel_giderleri, personel_maas, personel_prim_orani
-- (personel maaş/prim/gider takibi) tabloları yalnızca prolyn_k1'de vardı,
-- prolyn_default'ta hiç yoktu. Sadece tablo YAPISI kopyalandı, veri
-- kopyalanmadı (prolyn_k1'deki satırlar ELIT MUTFAK'a ait gerçek
-- bordro verisi; şablon veritabanı boş kalmalı).
--
-- Bu üç tablo dışında satır satır (information_schema.COLUMNS)
-- karşılaştırıldığında kolon adı, kolon yorumu, tip, null/default
-- açısından iki veritabanı arasında başka fark bulunmadı.
--
-- NOT (uygulanmadı — karar gerektirir): prolyn_k1.subeler.CustomerID
-- kolonunun DEFAULT'ı NULL, prolyn_default'ta ise 0. Tek başına önemsiz
-- görünüyor (şubeler tablosu zaten kiracıya özel tek DB içinde, ayrıca
-- bir CustomerID'ye göre filtrelenmesi beklenmiyor) ama hangisinin doğru
-- olduğu koddan kesin anlaşılamadı; değiştirilmedi.

CREATE TABLE IF NOT EXISTS `personel_giderleri` (
  `GiderID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar',
  `UserID` int(11) NOT NULL COMMENT 'users.UserID — ödemenin yapıldığı personel',
  `GiderTipi` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Gider türü: 1 Maaş, 2 Avans, 3 Prim, 4 Mesai, 5 Yol, 6 Yemek, 7 Kesinti (borcu azaltır, kasadan para çıkmaz), 99 Diğer',
  `Donem` char(7) NOT NULL COMMENT 'Ödemenin yazıldığı hesap dönemi, YYYY-MM. Ödeme tarihinden farklı olabilir (örn. Temmuz maaşı Ağustos başında ödenir).',
  `Tutar` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Ödeme tutarı (TL). Kesinti türünde de POZİTİF yazılır; eksi etkisi hesapta uygulanır.',
  `OdemeTarihi` date NOT NULL COMMENT 'Paranın fiilen ödendiği tarih',
  `OdemeSekli` tinyint(4) DEFAULT NULL COMMENT 'Kasadan düşüldüyse ödeme şekli: 1 Nakit, 2 Kredi Kartı, 3 Havale/EFT. NULL = kasaya işlenmedi, yalnızca kayıt.',
  `BankaID` int(11) DEFAULT NULL COMMENT 'bankalar.BankaID — kart/havale ödemesinde parayı çıkaran hesap. Nakit ve kasasız kayıtlarda NULL.',
  `KasaHareketID` int(11) DEFAULT NULL COMMENT 'kasahareketleri.KasaHareketID — bu ödemeden doğan Ana Kasa çıkışı. NULL ise kasaya işlenmemiştir. Dolu olan satırlar Ana Kasa ekranından tek başına silinemez.',
  `Aciklama` varchar(500) DEFAULT NULL COMMENT 'Serbest not — örn. Ekim ayı 2. taksit',
  `OlusturanID` int(11) DEFAULT NULL COMMENT 'users.UserID — ödemeyi kaydeden kullanıcı',
  `OlusturmaTarihi` datetime DEFAULT NULL COMMENT 'Kaydın oluşturulma anı',
  `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş. Silinen ödemenin kasa hareketi de geri alınır.',
  `GuncelleyenID` int(11) DEFAULT NULL COMMENT 'users.UserID — ödemeyi en son düzelten kullanıcı',
  `GuncellemeTarihi` datetime DEFAULT NULL COMMENT 'Son düzeltme anı. Dolu ise kayıt düzeltilmiştir.',
  PRIMARY KEY (`GiderID`),
  KEY `ix_pg_user` (`UserID`,`Donem`),
  KEY `ix_pg_tarih` (`OdemeTarihi`),
  KEY `ix_pg_durum` (`del_status`),
  KEY `ix_pg_kasa` (`KasaHareketID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Personel ödeme defteri — maaş, avans, prim, mesai, yol, yemek, kesinti. Her satır bir ödemedir. Kasadan düşülen ödemeler KasaHareketID ile Ana Kasa hareketine bağlanır; ödeme silinince veya düzeltilince o hareket de geri alınır.';

CREATE TABLE IF NOT EXISTS `personel_maas` (
  `MaasID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar',
  `UserID` int(11) NOT NULL COMMENT 'users.UserID — maaşı tanımlanan personel',
  `Tutar` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Aylık brüt maaş tutarı (TL)',
  `GecerliTarih` date NOT NULL COMMENT 'Bu tutarın yürürlüğe girdiği tarih. Öncesindeki dönemlerde bir önceki tanım geçerlidir.',
  `Aciklama` varchar(255) DEFAULT NULL COMMENT 'Serbest not — örn. 2026 Temmuz zammı',
  `OlusturanID` int(11) DEFAULT NULL COMMENT 'users.UserID — tanımı giren kullanıcı',
  `OlusturmaTarihi` datetime DEFAULT NULL COMMENT 'Kaydın oluşturulma anı',
  `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş. Kayıt fiziksel olarak silinmez.',
  PRIMARY KEY (`MaasID`),
  KEY `ix_pm_user` (`UserID`,`GecerliTarih`),
  KEY `ix_pm_durum` (`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Personel maaş tanımları — TARİHÇELİ. Zam yapıldığında eski satır güncellenmez, yeni satır eklenir. Bir dönem için, o dönemin son gününe kadar yürürlüğe girmiş EN SON tanım geçerlidir; böylece geçmiş dönem hesapları bozulmaz.';

CREATE TABLE IF NOT EXISTS `personel_prim_orani` (
  `PrimID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar',
  `UserID` int(11) NOT NULL COMMENT 'users.UserID — prim oranı tanımlanan satış personeli',
  `Oran` decimal(6,3) NOT NULL DEFAULT 0.000 COMMENT 'Prim oranı YÜZDE olarak (2.5 = %2,5). Satış tutarı × Oran / 100 = prim.',
  `GecerliTarih` date NOT NULL COMMENT 'Bu oranın yürürlüğe girdiği tarih. Öncesindeki dönemlerde bir önceki oran geçerlidir.',
  `Aciklama` varchar(255) DEFAULT NULL COMMENT 'Serbest not — örn. 2026 ikinci yarı prim düzenlemesi',
  `OlusturanID` int(11) DEFAULT NULL COMMENT 'users.UserID — tanımı giren kullanıcı',
  `OlusturmaTarihi` datetime DEFAULT NULL COMMENT 'Kaydın oluşturulma anı',
  `del_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
  PRIMARY KEY (`PrimID`),
  KEY `ix_ppo_user` (`UserID`,`GecerliTarih`),
  KEY `ix_ppo_durum` (`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Satış personeli prim oranları — TARİHÇELİ, personel_maas ile aynı mantık. Prim, dönem içinde imzalanan sözleşmelerin (sozlesmeler.NetTutar) toplamı üzerinden bu oranla hesaplanır. Oran değişince yeni satır eklenir; geçmiş dönemlerin primi eski oranla kalır.';
