-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: prolyn_default
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admincek`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admincek` (
  `CekID` int(11) NOT NULL AUTO_INCREMENT,
  `CekTahsilatID` int(11) DEFAULT NULL,
  `AdminStatus` tinyint(4) DEFAULT 0,
  `UserID` int(11) DEFAULT NULL,
  `CustomerID` int(11) NOT NULL,
  `IslemTip` int(11) DEFAULT NULL,
  `Tutar` varchar(255) DEFAULT NULL,
  `OdemeTip` tinyint(4) DEFAULT 4,
  `Durum` tinyint(4) DEFAULT 1,
  `AdminDurum` tinyint(4) DEFAULT 1,
  `BankaID` int(11) DEFAULT NULL,
  `BankaSubeID` int(11) DEFAULT NULL,
  `CekSahibi` varchar(255) DEFAULT NULL,
  `CekNo` varchar(255) DEFAULT NULL,
  `CekVade` date DEFAULT NULL,
  `TakasBankaID` int(11) DEFAULT NULL,
  `KasaDesc` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`CekID`) USING BTREE,
  KEY `idx_admincek_UserID` (`UserID`),
  KEY `idx_admincek_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri çekleri ve senetlerinin takip edildiği ana çek tablosu.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admincek`
--


--
-- Table structure for table `adminkasa`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adminkasa` (
  `AdminKasaID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `AdminKasaTip` int(11) NOT NULL,
  `AdminKasaDetay` int(11) NOT NULL,
  `AdminDesc` varchar(255) NOT NULL,
  `AdminKasaTutar` double(10,2) NOT NULL,
  `AdminRecordDate` datetime NOT NULL DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`AdminKasaID`) USING BTREE,
  KEY `idx_adminkasa_UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='Yönetici veya ana kasa hesaplarının takip edildiği tablo.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adminkasa`
--


--
-- Table structure for table `ayarlar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ayarlar` (
  `MetaID` int(11) NOT NULL AUTO_INCREMENT,
  `SiteTitle` varchar(255) DEFAULT NULL,
  `SiteDesc` varchar(255) DEFAULT NULL,
  `SiteAuthor` varchar(255) DEFAULT NULL,
  `SiteSeo` varchar(255) DEFAULT NULL,
  `SiteKeyword` mediumtext DEFAULT NULL,
  `SiteLogo` varchar(255) DEFAULT NULL,
  `SiteAvatar` varchar(255) DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `SitePhone` varchar(50) DEFAULT NULL,
  `SiteEmail` varchar(255) DEFAULT NULL,
  `SiteURL` varchar(255) DEFAULT NULL,
  `SiteAddress` varchar(255) DEFAULT NULL,
  `MetaRobots` varchar(100) DEFAULT NULL,
  `SiteLanguage` varchar(10) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`MetaID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Uygulamaya ait dinamik sistem ayarlarının tutulduğu tablo.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

INSERT INTO `ayarlar` VALUES (1,' | ONLINE ERP SİSTEMİ','','BİLAL SAMİ ZAHİT ÖZGÜL - bilalozgul@echoshift.net - 05327398000','https://www.prolyn.net',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'tr',1);

--
-- Table structure for table `bankalar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankalar` (
  `BankID` int(11) NOT NULL AUTO_INCREMENT,
  `BankaID` int(11) NOT NULL COMMENT '1 "Akbank"\r\n2 "Denizbank"\r\n3 "ING Bank"\r\n4 "QNB Finansbank"\r\n5 "Türk Ekonomi Bankası"\r\n6 "Ziraat Bankası"\r\n7 "Garanti Bankası"\r\n8 "Halk Bankası"\r\n9 "İş Bankası"\r\n10 "Vakıflar Bankası"\r\n11 "Yapı ve Kredi Bankası"\r\n12 "Kuveyt Türk"',
  `FirmaID` int(11) NOT NULL,
  `TotalTutar` double(10,2) DEFAULT NULL,
  `IBAN` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) NOT NULL DEFAULT 0,
  `GüncelTarih` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`BankID`),
  KEY `idx_bankalar_FirmaID` (`FirmaID`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemde tanımlı banka hesap bilgilerinin tutulduğu tablo.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bankalar`
--

INSERT INTO `bankalar` VALUES (1,1,1,0.00,NULL,0,NULL,1),(2,2,1,0.00,NULL,0,NULL,1),(3,3,1,0.00,NULL,0,NULL,1),(4,4,1,0.00,NULL,0,NULL,1),(5,5,1,0.00,NULL,0,NULL,1),(6,6,1,0.00,NULL,0,NULL,1),(7,7,1,0.00,NULL,0,NULL,1),(8,8,1,0.00,NULL,0,NULL,1),(9,9,1,0.00,NULL,0,NULL,1),(10,10,1,0.00,NULL,0,NULL,1),(11,11,1,0.00,NULL,0,NULL,1),(12,12,1,0.00,NULL,0,NULL,1);

--
-- Table structure for table `cek_birlestirme`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cek_birlestirme` (
  `BirlestirmeID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `ContractID` int(11) NOT NULL,
  `ToplamTutar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `BankaID` int(11) DEFAULT NULL,
  `BankaSubeID` int(11) DEFAULT NULL,
  `CekSahibi` varchar(255) DEFAULT NULL,
  `CekNo` varchar(100) DEFAULT NULL,
  `CekVade` date DEFAULT NULL,
  `OdemeTarihi` date NOT NULL,
  `AdminDurum` tinyint(1) NOT NULL DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`BirlestirmeID`),
  KEY `idx_cek_birlestirme_UserID` (`UserID`),
  KEY `idx_cek_birlestirme_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Birden fazla çekin tek bir tahsilat/ödeme altında birleştirilmesi işlemleri.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cek_birlestirme`
--


--
-- Table structure for table `dolapboy`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dolapboy` (
  `DolapBoyID` int(11) NOT NULL AUTO_INCREMENT,
  `DolapBoyName` varchar(255) DEFAULT NULL,
  `bDolapStandart` tinyint(1) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`DolapBoyID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Üretim/Stok süreçleri için dolap boyu standartlarının tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dolapboy`
--

INSERT INTO `dolapboy` VALUES (1,'60 CM',1,1),(2,'72 CM',1,1),(3,'96 CM',1,1),(4,'ÇİFT KATLI',1,1),(5,'ÖZEL',0,1);

--
-- Table structure for table `dosyalar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dosyalar` (
  `FileID` int(11) NOT NULL AUTO_INCREMENT,
  `FileAppID` int(11) DEFAULT NULL,
  `FileAuthorID` int(11) DEFAULT NULL,
  `FilePath` mediumtext DEFAULT NULL,
  `FileName` mediumtext DEFAULT NULL,
  `FileType` mediumtext DEFAULT NULL,
  `FileSize` longtext DEFAULT NULL,
  `FileCreated` datetime DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`FileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sisteme yüklenen genel dosyaların ve eklerin kayıtları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dosyalar`
--


--
-- Table structure for table `duyurular`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `duyurular` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baslik` varchar(255) NOT NULL,
  `icerik` text NOT NULL,
  `sayfa` varchar(255) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kullanıcılara veya şubelere yapılan sistem duyuruları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `duyurular`
--


--
-- Table structure for table `edosyalar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edosyalar` (
  `NonFileID` int(11) NOT NULL AUTO_INCREMENT,
  `NonFileAppID` int(11) DEFAULT NULL,
  `NonFileAuthorID` int(11) DEFAULT NULL,
  `NonFilePath` mediumtext DEFAULT NULL,
  `NonFileName` mediumtext DEFAULT NULL,
  `NonFileType` mediumtext DEFAULT NULL,
  `NonFileSize` longtext DEFAULT NULL,
  `NonFileCreated` datetime DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`NonFileID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri veya sözleşmelere ait dijital/elektronik dokümanlar.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edosyalar`
--


--
-- Table structure for table `faturafile`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faturafile` (
  `FaturaID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `AppUser` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `FaturaNo` varchar(255) DEFAULT NULL,
  `FaturaDosya` varchar(255) DEFAULT NULL,
  `FatBildirim` tinyint(4) NOT NULL DEFAULT 1,
  `UpdateDate` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`FaturaID`),
  KEY `idx_faturafile_UserID` (`UserID`),
  KEY `idx_faturafile_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Faturalara ait görsel veya PDF belgelerinin yolları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faturafile`
--


--
-- Table structure for table `faturalar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faturalar` (
  `PrintID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `CompanyVNo` varchar(255) DEFAULT NULL,
  `CompanyVD` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`PrintID`) USING BTREE,
  KEY `idx_faturalar_CustomerID` (`CustomerID`),
  KEY `idx_opt_fatura_tarih` (`CreatedDate`,`del_status`),
  KEY `idx_opt_fatura_customer` (`CustomerID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kesilen ve alınan ticari faturaların ana kayıtları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faturalar`
--


--
-- Table structure for table `firmalar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firmalar` (
  `FirmaID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `FirmaAdi` varchar(255) NOT NULL,
  `FirmaDetay` varchar(255) NOT NULL,
  `FirmaLogo` varchar(255) DEFAULT NULL,
  `FirmaVergi` varchar(255) NOT NULL,
  `FirmaVNO` char(11) NOT NULL,
  `BayiCity` varchar(255) NOT NULL,
  `BayiTown` varchar(255) NOT NULL,
  `BayiAdres` varchar(255) NOT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`FirmaID`),
  KEY `idx_firmalar_firmaid` (`FirmaID`),
  KEY `idx_firmalar_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemi kullanan bayi veya alt firmaların ana tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firmalar`
--


--
-- Table structure for table `govdecinsi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `govdecinsi` (
  `GovdeCinsiID` int(11) NOT NULL AUTO_INCREMENT,
  `GovdeCinsiName` varchar(255) DEFAULT NULL,
  `bGovdeStandart` tinyint(4) NOT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`GovdeCinsiID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Üretim için gövde cinsi materyal tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `govdecinsi`
--

INSERT INTO `govdecinsi` VALUES (1,'SUNTALAM',1,1),(2,'MDFLAM',1,1);

--
-- Table structure for table `govderenk`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `govderenk` (
  `GovdeRenkID` int(11) NOT NULL AUTO_INCREMENT,
  `GovdeRenkName` varchar(255) DEFAULT NULL,
  `bGRenkStandart` tinyint(4) NOT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`GovdeRenkID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Üretim için gövde renk standartları tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `govderenk`
--

INSERT INTO `govderenk` VALUES (1,'BEYAZ',1,1),(2,'KREM',1,1);

--
-- Table structure for table `ilceler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ilceler` (
  `ilce_id` int(11) NOT NULL AUTO_INCREMENT,
  `ilceadi` varchar(255) NOT NULL,
  `iller_id` int(11) NOT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ilce_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=974 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Türkiye geneli veya bölgesel ilçe tanımları sözlüğü.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ilceler`
--

INSERT INTO `ilceler` VALUES (1,'ALADAĞ',1,1),(2,'CEYHAN',1,1),(3,'ÇUKUROVA',1,1),(4,'FEKE',1,1),(5,'İMAMOĞLU',1,1),(6,'KARAİSALI',1,1),(7,'KARATAŞ',1,1),(8,'KOZAN',1,1),(9,'POZANTI',1,1),(10,'SAİMBEYLİ',1,1),(11,'SARIÇAM',1,1),(12,'SEYHAN',1,1),(13,'TUFANBEYLİ',1,1),(14,'YUMURTALIK',1,1),(15,'YÜREĞİR',1,1),(16,'BESNİ',2,1),(17,'ÇELİKHAN',2,1),(18,'GERGER',2,1),(19,'GÖLBAŞI',2,1),(20,'KAHTA',2,1),(21,'MERKEZ',2,1),(22,'SAMSAT',2,1),(23,'SİNCİK',2,1),(24,'TUT',2,1),(25,'BAŞMAKÇI',3,1),(26,'BAYAT',3,1),(27,'BOLVADİN',3,1),(28,'ÇAY',3,1),(29,'ÇOBANLAR',3,1),(30,'DAZKIRI',3,1),(31,'DİNAR',3,1),(32,'EMİRDAĞ',3,1),(33,'EVCİLER',3,1),(34,'HOCALAR',3,1),(35,'İHSANİYE',3,1),(36,'İSCEHİSAR',3,1),(37,'KIZILÖREN',3,1),(38,'MERKEZ',3,1),(39,'SANDIKLI',3,1),(40,'SİNANPAŞA',3,1),(41,'SULTANDAĞI',3,1),(42,'ŞUHUT',3,1),(43,'DİYADİN',4,1),(44,'DOĞUBAYAZIT',4,1),(45,'ELEŞKİRT',4,1),(46,'HAMUR',4,1),(47,'MERKEZ',4,1),(48,'PATNOS',4,1),(49,'TAŞLIÇAY',4,1),(50,'TUTAK',4,1),(51,'AĞAÇÖREN',68,1),(52,'ESKİL',68,1),(53,'GÜLAĞAÇ',68,1),(54,'GÜZELYURT',68,1),(55,'MERKEZ',68,1),(56,'ORTAKÖY',68,1),(57,'SARIYAHŞİ',68,1),(58,'SULTANHANI',68,1),(59,'GÖYNÜCEK',5,1),(60,'GÜMÜŞHACIKÖY',5,1),(61,'HAMAMÖZÜ',5,1),(62,'MERKEZ',5,1),(63,'MERZİFON',5,1),(64,'SULUOVA',5,1),(65,'TAŞOVA',5,1),(66,'AKYURT',6,1),(67,'ALTINDAĞ',6,1),(68,'AYAŞ',6,1),(69,'BALA',6,1),(70,'BEYPAZARI',6,1),(71,'ÇAMLIDERE',6,1),(72,'ÇANKAYA',6,1),(73,'ÇUBUK',6,1),(74,'ELMADAĞ',6,1),(75,'ETİMESGUT',6,1),(76,'EVREN',6,1),(77,'GÖLBAŞI',6,1),(78,'GÜDÜL',6,1),(79,'HAYMANA',6,1),(80,'KAHRAMANKAZAN',6,1),(81,'KALECİK',6,1),(82,'KEÇİÖREN',6,1),(83,'KIZILCAHAMAM',6,1),(84,'MAMAK',6,1),(85,'NALLIHAN',6,1),(86,'POLATLI',6,1),(87,'PURSAKLAR',6,1),(88,'SİNCAN',6,1),(89,'ŞEREFLİKOÇHİSAR',6,1),(90,'YENİMAHALLE',6,1),(91,'AKSEKİ',7,1),(92,'AKSU',7,1),(93,'ALANYA',7,1),(94,'DEMRE',7,1),(95,'DÖŞEMEALTI',7,1),(96,'ELMALI',7,1),(97,'FİNİKE',7,1),(98,'GAZİPAŞA',7,1),(99,'GÜNDOĞMUŞ',7,1),(100,'İBRADI',7,1),(101,'KAŞ',7,1),(102,'KEMER',7,1),(103,'KEPEZ',7,1),(104,'KONYAALTI',7,1),(105,'KORKUTELİ',7,1),(106,'KUMLUCA',7,1),(107,'MANAVGAT',7,1),(108,'MURATPAŞA',7,1),(109,'SERİK',7,1),(110,'ÇILDIR',75,1),(111,'DAMAL',75,1),(112,'GÖLE',75,1),(113,'HANAK',75,1),(114,'MERKEZ',75,1),(115,'POSOF',75,1),(116,'ARDANUÇ',8,1),(117,'ARHAVİ',8,1),(118,'BORÇKA',8,1),(119,'HOPA',8,1),(120,'KEMALPAŞA',8,1),(121,'MERKEZ',8,1),(122,'MURGUL',8,1),(123,'ŞAVŞAT',8,1),(124,'YUSUFELİ',8,1),(125,'BOZDOĞAN',9,1),(126,'BUHARKENT',9,1),(127,'ÇİNE',9,1),(128,'DİDİM',9,1),(129,'EFELER',9,1),(130,'GERMENCİK',9,1),(131,'İNCİRLİOVA',9,1),(132,'KARACASU',9,1),(133,'KARPUZLU',9,1),(134,'KOÇARLI',9,1),(135,'KÖŞK',9,1),(136,'KUŞADASI',9,1),(137,'KUYUCAK',9,1),(138,'NAZİLLİ',9,1),(139,'SÖKE',9,1),(140,'SULTANHİSAR',9,1),(141,'YENİPAZAR',9,1),(142,'ALTIEYLÜL',10,1),(143,'AYVALIK',10,1),(144,'BALYA',10,1),(145,'BANDIRMA',10,1),(146,'BİGADİÇ',10,1),(147,'BURHANİYE',10,1),(148,'DURSUNBEY',10,1),(149,'EDREMİT',10,1),(150,'ERDEK',10,1),(151,'GÖMEÇ',10,1),(152,'GÖNEN',10,1),(153,'HAVRAN',10,1),(154,'İVRİNDİ',10,1),(155,'KARESİ',10,1),(156,'KEPSUT',10,1),(157,'MANYAS',10,1),(158,'MARMARA',10,1),(159,'SAVAŞTEPE',10,1),(160,'SINDIRGI',10,1),(161,'SUSURLUK',10,1),(162,'AMASRA',74,1),(163,'KURUCAŞİLE',74,1),(164,'MERKEZ',74,1),(165,'ULUS',74,1),(166,'BEŞİRİ',72,1),(167,'GERCÜŞ',72,1),(168,'HASANKEYF',72,1),(169,'KOZLUK',72,1),(170,'MERKEZ',72,1),(171,'SASON',72,1),(172,'AYDINTEPE',69,1),(173,'DEMİRÖZÜ',69,1),(174,'MERKEZ',69,1),(175,'BOZÜYÜK',11,1),(176,'GÖLPAZARI',11,1),(177,'İNHİSAR',11,1),(178,'MERKEZ',11,1),(179,'OSMANELİ',11,1),(180,'PAZARYERİ',11,1),(181,'SÖĞÜT',11,1),(182,'YENİPAZAR',11,1),(183,'ADAKLI',12,1),(184,'GENÇ',12,1),(185,'KARLIOVA',12,1),(186,'KİĞI',12,1),(187,'MERKEZ',12,1),(188,'SOLHAN',12,1),(189,'YAYLADERE',12,1),(190,'YEDİSU',12,1),(191,'ADİLCEVAZ',13,1),(192,'AHLAT',13,1),(193,'GÜROYMAK',13,1),(194,'HİZAN',13,1),(195,'MERKEZ',13,1),(196,'MUTKİ',13,1),(197,'TATVAN',13,1),(198,'DÖRTDİVAN',14,1),(199,'GEREDE',14,1),(200,'GÖYNÜK',14,1),(201,'KIBRISCIK',14,1),(202,'MENGEN',14,1),(203,'MERKEZ',14,1),(204,'MUDURNU',14,1),(205,'SEBEN',14,1),(206,'YENİÇAĞA',14,1),(207,'AĞLASUN',15,1),(208,'ALTINYAYLA',15,1),(209,'BUCAK',15,1),(210,'ÇAVDIR',15,1),(211,'ÇELTİKÇİ',15,1),(212,'GÖLHİSAR',15,1),(213,'KARAMANLI',15,1),(214,'KEMER',15,1),(215,'MERKEZ',15,1),(216,'TEFENNİ',15,1),(217,'YEŞİLOVA',15,1),(218,'BÜYÜKORHAN',16,1),(219,'GEMLİK',16,1),(220,'GÜRSU',16,1),(221,'HARMANCIK',16,1),(222,'İNEGÖL',16,1),(223,'İZNİK',16,1),(224,'KARACABEY',16,1),(225,'KELES',16,1),(226,'KESTEL',16,1),(227,'MUDANYA',16,1),(228,'MUSTAFAKEMALPAŞA',16,1),(229,'NİLÜFER',16,1),(230,'ORHANELİ',16,1),(231,'ORHANGAZİ',16,1),(232,'OSMANGAZİ',16,1),(233,'YENİŞEHİR',16,1),(234,'YILDIRIM',16,1),(235,'AYVACIK',17,1),(236,'BAYRAMİÇ',17,1),(237,'BİGA',17,1),(238,'BOZCAADA',17,1),(239,'ÇAN',17,1),(240,'ECEABAT',17,1),(241,'EZİNE',17,1),(242,'GELİBOLU',17,1),(243,'GÖKÇEADA',17,1),(244,'LAPSEKİ',17,1),(245,'MERKEZ',17,1),(246,'YENİCE',17,1),(247,'ATKARACALAR',18,1),(248,'BAYRAMÖREN',18,1),(249,'ÇERKEŞ',18,1),(250,'ELDİVAN',18,1),(251,'ILGAZ',18,1),(252,'KIZILIRMAK',18,1),(253,'KORGUN',18,1),(254,'KURŞUNLU',18,1),(255,'MERKEZ',18,1),(256,'ORTA',18,1),(257,'ŞABANÖZÜ',18,1),(258,'YAPRAKLI',18,1),(259,'ALACA',19,1),(260,'BAYAT',19,1),(261,'BOĞAZKALE',19,1),(262,'DODURGA',19,1),(263,'İSKİLİP',19,1),(264,'KARGI',19,1),(265,'LAÇİN',19,1),(266,'MECİTÖZÜ',19,1),(267,'MERKEZ',19,1),(268,'OĞUZLAR',19,1),(269,'ORTAKÖY',19,1),(270,'OSMANCIK',19,1),(271,'SUNGURLU',19,1),(272,'UĞURLUDAĞ',19,1),(273,'ACIPAYAM',20,1),(274,'BABADAĞ',20,1),(275,'BAKLAN',20,1),(276,'BEKİLLİ',20,1),(277,'BEYAĞAÇ',20,1),(278,'BOZKURT',20,1),(279,'BULDAN',20,1),(280,'ÇAL',20,1),(281,'ÇAMELİ',20,1),(282,'ÇARDAK',20,1),(283,'ÇİVRİL',20,1),(284,'GÜNEY',20,1),(285,'HONAZ',20,1),(286,'KALE',20,1),(287,'MERKEZEFENDİ',20,1),(288,'PAMUKKALE',20,1),(289,'SARAYKÖY',20,1),(290,'SERİNHİSAR',20,1),(291,'TAVAS',20,1),(292,'BAĞLAR',21,1),(293,'BİSMİL',21,1),(294,'ÇERMİK',21,1),(295,'ÇINAR',21,1),(296,'ÇÜNGÜŞ',21,1),(297,'DİCLE',21,1),(298,'EĞİL',21,1),(299,'ERGANİ',21,1),(300,'HANİ',21,1),(301,'HAZRO',21,1),(302,'KAYAPINAR',21,1),(303,'KOCAKÖY',21,1),(304,'KULP',21,1),(305,'LİCE',21,1),(306,'SİLVAN',21,1),(307,'SUR',21,1),(308,'YENİŞEHİR',21,1),(309,'AKÇAKOCA',81,1),(310,'CUMAYERİ',81,1),(311,'ÇİLİMLİ',81,1),(312,'GÖLYAKA',81,1),(313,'GÜMÜŞOVA',81,1),(314,'KAYNAŞLI',81,1),(315,'MERKEZ',81,1),(316,'YIĞILCA',81,1),(317,'ENEZ',22,1),(318,'HAVSA',22,1),(319,'İPSALA',22,1),(320,'KEŞAN',22,1),(321,'LALAPAŞA',22,1),(322,'MERİÇ',22,1),(323,'MERKEZ',22,1),(324,'SÜLOĞLU',22,1),(325,'UZUNKÖPRÜ',22,1),(326,'AĞIN',23,1),(327,'ALACAKAYA',23,1),(328,'ARICAK',23,1),(329,'BASKİL',23,1),(330,'KARAKOÇAN',23,1),(331,'KEBAN',23,1),(332,'KOVANCILAR',23,1),(333,'MADEN',23,1),(334,'MERKEZ',23,1),(335,'PALU',23,1),(336,'SİVRİCE',23,1),(337,'ÇAYIRLI',24,1),(338,'İLİÇ',24,1),(339,'KEMAH',24,1),(340,'KEMALİYE',24,1),(341,'MERKEZ',24,1),(342,'OTLUKBELİ',24,1),(343,'REFAHİYE',24,1),(344,'TERCAN',24,1),(345,'ÜZÜMLÜ',24,1),(346,'AŞKALE',25,1),(347,'AZİZİYE',25,1),(348,'ÇAT',25,1),(349,'HINIS',25,1),(350,'HORASAN',25,1),(351,'İSPİR',25,1),(352,'KARAÇOBAN',25,1),(353,'KARAYAZI',25,1),(354,'KÖPRÜKÖY',25,1),(355,'NARMAN',25,1),(356,'OLTU',25,1),(357,'OLUR',25,1),(358,'PALANDÖKEN',25,1),(359,'PASİNLER',25,1),(360,'PAZARYOLU',25,1),(361,'ŞENKAYA',25,1),(362,'TEKMAN',25,1),(363,'TORTUM',25,1),(364,'UZUNDERE',25,1),(365,'YAKUTİYE',25,1),(366,'ALPU',26,1),(367,'BEYLİKOVA',26,1),(368,'ÇİFTELER',26,1),(369,'GÜNYÜZÜ',26,1),(370,'HAN',26,1),(371,'İNÖNÜ',26,1),(372,'MAHMUDİYE',26,1),(373,'MİHALGAZİ',26,1),(374,'MİHALIÇÇIK',26,1),(375,'ODUNPAZARI',26,1),(376,'SARICAKAYA',26,1),(377,'SEYİTGAZİ',26,1),(378,'SİVRİHİSAR',26,1),(379,'TEPEBAŞI',26,1),(380,'ARABAN',27,1),(381,'İSLAHİYE',27,1),(382,'KARKAMIŞ',27,1),(383,'NİZİP',27,1),(384,'NURDAĞI',27,1),(385,'OĞUZELİ',27,1),(386,'ŞAHİNBEY',27,1),(387,'ŞEHİTKAMİL',27,1),(388,'YAVUZELİ',27,1),(389,'ALUCRA',28,1),(390,'BULANCAK',28,1),(391,'ÇAMOLUK',28,1),(392,'ÇANAKÇI',28,1),(393,'DERELİ',28,1),(394,'DOĞANKENT',28,1),(395,'ESPİYE',28,1),(396,'EYNESİL',28,1),(397,'GÖRELE',28,1),(398,'GÜCE',28,1),(399,'KEŞAP',28,1),(400,'MERKEZ',28,1),(401,'PİRAZİZ',28,1),(402,'ŞEBİNKARAHİSAR',28,1),(403,'TİREBOLU',28,1),(404,'YAĞLIDERE',28,1),(405,'KELKİT',29,1),(406,'KÖSE',29,1),(407,'KÜRTÜN',29,1),(408,'MERKEZ',29,1),(409,'ŞİRAN',29,1),(410,'TORUL',29,1),(411,'ÇUKURCA',30,1),(412,'DERECİK',30,1),(413,'MERKEZ',30,1),(414,'ŞEMDİNLİ',30,1),(415,'YÜKSEKOVA',30,1),(416,'ALTINÖZÜ',31,1),(417,'ANTAKYA',31,1),(418,'ARSUZ',31,1),(419,'BELEN',31,1),(420,'DEFNE',31,1),(421,'DÖRTYOL',31,1),(422,'ERZİN',31,1),(423,'HASSA',31,1),(424,'İSKENDERUN',31,1),(425,'KIRIKHAN',31,1),(426,'KUMLU',31,1),(427,'PAYAS',31,1),(428,'REYHANLI',31,1),(429,'SAMANDAĞ',31,1),(430,'YAYLADAĞI',31,1),(431,'ARALIK',76,1),(432,'KARAKOYUNLU',76,1),(433,'MERKEZ',76,1),(434,'TUZLUCA',76,1),(435,'AKSU',32,1),(436,'ATABEY',32,1),(437,'EĞİRDİR',32,1),(438,'GELENDOST',32,1),(439,'GÖNEN',32,1),(440,'KEÇİBORLU',32,1),(441,'MERKEZ',32,1),(442,'SENİRKENT',32,1),(443,'SÜTÇÜLER',32,1),(444,'ŞARKİKARAAĞAÇ',32,1),(445,'ULUBORLU',32,1),(446,'YALVAÇ',32,1),(447,'YENİŞARBADEMLİ',32,1),(448,'ADALAR',34,1),(449,'ARNAVUTKÖY',34,1),(450,'ATAŞEHİR',34,1),(451,'AVCILAR',34,1),(452,'BAĞCILAR',34,1),(453,'BAHÇELİEVLER',34,1),(454,'BAKIRKÖY',34,1),(455,'BAŞAKŞEHİR',34,1),(456,'BAYRAMPAŞA',34,1),(457,'BEŞİKTAŞ',34,1),(458,'BEYKOZ',34,1),(459,'BEYLİKDÜZÜ',34,1),(460,'BEYOĞLU',34,1),(461,'BÜYÜKÇEKMECE',34,1),(462,'ÇATALCA',34,1),(463,'ÇEKMEKÖY',34,1),(464,'ESENLER',34,1),(465,'ESENYURT',34,1),(466,'EYÜPSULTAN',34,1),(467,'FATİH',34,1),(468,'GAZİOSMANPAŞA',34,1),(469,'GÜNGÖREN',34,1),(470,'KADIKÖY',34,1),(471,'KAĞITHANE',34,1),(472,'KARTAL',34,1),(473,'KÜÇÜKÇEKMECE',34,1),(474,'MALTEPE',34,1),(475,'PENDİK',34,1),(476,'SANCAKTEPE',34,1),(477,'SARIYER',34,1),(478,'SİLİVRİ',34,1),(479,'SULTANBEYLİ',34,1),(480,'SULTANGAZİ',34,1),(481,'ŞİLE',34,1),(482,'ŞİŞLİ',34,1),(483,'TUZLA',34,1),(484,'ÜMRANİYE',34,1),(485,'ÜSKÜDAR',34,1),(486,'ZEYTİNBURNU',34,1),(487,'ALİAĞA',35,1),(488,'BALÇOVA',35,1),(489,'BAYINDIR',35,1),(490,'BAYRAKLI',35,1),(491,'BERGAMA',35,1),(492,'BEYDAĞ',35,1),(493,'BORNOVA',35,1),(494,'BUCA',35,1),(495,'ÇEŞME',35,1),(496,'ÇİĞLİ',35,1),(497,'DİKİLİ',35,1),(498,'FOÇA',35,1),(499,'GAZİEMİR',35,1),(500,'GÜZELBAHÇE',35,1),(501,'KARABAĞLAR',35,1),(502,'KARABURUN',35,1),(503,'KARŞIYAKA',35,1),(504,'KEMALPAŞA',35,1),(505,'KINIK',35,1),(506,'KİRAZ',35,1),(507,'KONAK',35,1),(508,'MENDERES',35,1),(509,'MENEMEN',35,1),(510,'NARLIDERE',35,1),(511,'ÖDEMİŞ',35,1),(512,'SEFERİHİSAR',35,1),(513,'SELÇUK',35,1),(514,'TİRE',35,1),(515,'TORBALI',35,1),(516,'URLA',35,1),(517,'AFŞİN',46,1),(518,'ANDIRIN',46,1),(519,'ÇAĞLAYANCERİT',46,1),(520,'DULKADİROĞLU',46,1),(521,'EKİNÖZÜ',46,1),(522,'ELBİSTAN',46,1),(523,'GÖKSUN',46,1),(524,'NURHAK',46,1),(525,'ONİKİŞUBAT',46,1),(526,'PAZARCIK',46,1),(527,'TÜRKOĞLU',46,1),(528,'EFLANİ',78,1),(529,'ESKİPAZAR',78,1),(530,'MERKEZ',78,1),(531,'OVACIK',78,1),(532,'SAFRANBOLU',78,1),(533,'YENİCE',78,1),(534,'AYRANCI',70,1),(535,'BAŞYAYLA',70,1),(536,'ERMENEK',70,1),(537,'KAZIMKARABEKİR',70,1),(538,'MERKEZ',70,1),(539,'SARIVELİLER',70,1),(540,'AKYAKA',36,1),(541,'ARPAÇAY',36,1),(542,'DİGOR',36,1),(543,'KAĞIZMAN',36,1),(544,'MERKEZ',36,1),(545,'SARIKAMIŞ',36,1),(546,'SELİM',36,1),(547,'SUSUZ',36,1),(548,'ABANA',37,1),(549,'AĞLI',37,1),(550,'ARAÇ',37,1),(551,'AZDAVAY',37,1),(552,'BOZKURT',37,1),(553,'CİDE',37,1),(554,'ÇATALZEYTİN',37,1),(555,'DADAY',37,1),(556,'DEVREKANİ',37,1),(557,'DOĞANYURT',37,1),(558,'HANÖNÜ',37,1),(559,'İHSANGAZİ',37,1),(560,'İNEBOLU',37,1),(561,'KÜRE',37,1),(562,'MERKEZ',37,1),(563,'PINARBAŞI',37,1),(564,'SEYDİLER',37,1),(565,'ŞENPAZAR',37,1),(566,'TAŞKÖPRÜ',37,1),(567,'TOSYA',37,1),(568,'AKKIŞLA',38,1),(569,'BÜNYAN',38,1),(570,'DEVELİ',38,1),(571,'FELAHİYE',38,1),(572,'HACILAR',38,1),(573,'İNCESU',38,1),(574,'KOCASİNAN',38,1),(575,'MELİKGAZİ',38,1),(576,'ÖZVATAN',38,1),(577,'PINARBAŞI',38,1),(578,'SARIOĞLAN',38,1),(579,'SARIZ',38,1),(580,'TALAS',38,1),(581,'TOMARZA',38,1),(582,'YAHYALI',38,1),(583,'YEŞİLHİSAR',38,1),(584,'BAHŞILI',71,1),(585,'BALIŞEYH',71,1),(586,'ÇELEBİ',71,1),(587,'DELİCE',71,1),(588,'KARAKEÇİLİ',71,1),(589,'KESKİN',71,1),(590,'MERKEZ',71,1),(591,'SULAKYURT',71,1),(592,'YAHŞİHAN',71,1),(593,'BABAESKİ',39,1),(594,'DEMİRKÖY',39,1),(595,'KOFÇAZ',39,1),(596,'LÜLEBURGAZ',39,1),(597,'MERKEZ',39,1),(598,'PEHLİVANKÖY',39,1),(599,'PINARHİSAR',39,1),(600,'VİZE',39,1),(601,'AKÇAKENT',40,1),(602,'AKPINAR',40,1),(603,'BOZTEPE',40,1),(604,'ÇİÇEKDAĞI',40,1),(605,'KAMAN',40,1),(606,'MERKEZ',40,1),(607,'MUCUR',40,1),(608,'ELBEYLİ',79,1),(609,'MERKEZ',79,1),(610,'MUSABEYLİ',79,1),(611,'POLATELİ',79,1),(612,'BAŞİSKELE',41,1),(613,'ÇAYIROVA',41,1),(614,'DARICA',41,1),(615,'DERİNCE',41,1),(616,'DİLOVASI',41,1),(617,'GEBZE',41,1),(618,'GÖLCÜK',41,1),(619,'İZMİT',41,1),(620,'KANDIRA',41,1),(621,'KARAMÜRSEL',41,1),(622,'KARTEPE',41,1),(623,'KÖRFEZ',41,1),(624,'AHIRLI',42,1),(625,'AKÖREN',42,1),(626,'AKŞEHİR',42,1),(627,'ALTINEKİN',42,1),(628,'BEYŞEHİR',42,1),(629,'BOZKIR',42,1),(630,'CİHANBEYLİ',42,1),(631,'ÇELTİK',42,1),(632,'ÇUMRA',42,1),(633,'DERBENT',42,1),(634,'DEREBUCAK',42,1),(635,'DOĞANHİSAR',42,1),(636,'EMİRGAZİ',42,1),(637,'EREĞLİ',42,1),(638,'GÜNEYSINIR',42,1),(639,'HADİM',42,1),(640,'HALKAPINAR',42,1),(641,'HÜYÜK',42,1),(642,'ILGIN',42,1),(643,'KADINHANI',42,1),(644,'KARAPINAR',42,1),(645,'KARATAY',42,1),(646,'KULU',42,1),(647,'MERAM',42,1),(648,'SARAYÖNÜ',42,1),(649,'SELÇUKLU',42,1),(650,'SEYDİŞEHİR',42,1),(651,'TAŞKENT',42,1),(652,'TUZLUKÇU',42,1),(653,'YALIHÜYÜK',42,1),(654,'YUNAK',42,1),(655,'ALTINTAŞ',43,1),(656,'ASLANAPA',43,1),(657,'ÇAVDARHİSAR',43,1),(658,'DOMANİÇ',43,1),(659,'DUMLUPINAR',43,1),(660,'EMET',43,1),(661,'GEDİZ',43,1),(662,'HİSARCIK',43,1),(663,'MERKEZ',43,1),(664,'PAZARLAR',43,1),(665,'SİMAV',43,1),(666,'ŞAPHANE',43,1),(667,'TAVŞANLI',43,1),(668,'AKÇADAĞ',44,1),(669,'ARAPGİR',44,1),(670,'ARGUVAN',44,1),(671,'BATTALGAZİ',44,1),(672,'DARENDE',44,1),(673,'DOĞANŞEHİR',44,1),(674,'DOĞANYOL',44,1),(675,'HEKİMHAN',44,1),(676,'KALE',44,1),(677,'KULUNCAK',44,1),(678,'PÜTÜRGE',44,1),(679,'YAZIHAN',44,1),(680,'YEŞİLYURT',44,1),(681,'AHMETLİ',45,1),(682,'AKHİSAR',45,1),(683,'ALAŞEHİR',45,1),(684,'DEMİRCİ',45,1),(685,'GÖLMARMARA',45,1),(686,'GÖRDES',45,1),(687,'KIRKAĞAÇ',45,1),(688,'KÖPRÜBAŞI',45,1),(689,'KULA',45,1),(690,'SALİHLİ',45,1),(691,'SARIGÖL',45,1),(692,'SARUHANLI',45,1),(693,'SELENDİ',45,1),(694,'SOMA',45,1),(695,'ŞEHZADELER',45,1),(696,'TURGUTLU',45,1),(697,'YUNUSEMRE',45,1),(698,'ARTUKLU',47,1),(699,'DARGEÇİT',47,1),(700,'DERİK',47,1),(701,'KIZILTEPE',47,1),(702,'MAZIDAĞI',47,1),(703,'MİDYAT',47,1),(704,'NUSAYBİN',47,1),(705,'ÖMERLİ',47,1),(706,'SAVUR',47,1),(707,'YEŞİLLİ',47,1),(708,'AKDENİZ',33,1),(709,'ANAMUR',33,1),(710,'AYDINCIK',33,1),(711,'BOZYAZI',33,1),(712,'ÇAMLIYAYLA',33,1),(713,'ERDEMLİ',33,1),(714,'GÜLNAR',33,1),(715,'MEZİTLİ',33,1),(716,'MUT',33,1),(717,'SİLİFKE',33,1),(718,'TARSUS',33,1),(719,'TOROSLAR',33,1),(720,'YENİŞEHİR',33,1),(721,'BODRUM',48,1),(722,'DALAMAN',48,1),(723,'DATÇA',48,1),(724,'FETHİYE',48,1),(725,'KAVAKLIDERE',48,1),(726,'KÖYCEĞİZ',48,1),(727,'MARMARİS',48,1),(728,'MENTEŞE',48,1),(729,'MİLAS',48,1),(730,'ORTACA',48,1),(731,'SEYDİKEMER',48,1),(732,'ULA',48,1),(733,'YATAĞAN',48,1),(734,'BULANIK',49,1),(735,'HASKÖY',49,1),(736,'KORKUT',49,1),(737,'MALAZGİRT',49,1),(738,'MERKEZ',49,1),(739,'VARTO',49,1),(740,'ACIGÖL',50,1),(741,'AVANOS',50,1),(742,'DERİNKUYU',50,1),(743,'GÜLŞEHİR',50,1),(744,'HACIBEKTAŞ',50,1),(745,'KOZAKLI',50,1),(746,'MERKEZ',50,1),(747,'ÜRGÜP',50,1),(748,'ALTUNHİSAR',51,1),(749,'BOR',51,1),(750,'ÇAMARDI',51,1),(751,'ÇİFTLİK',51,1),(752,'MERKEZ',51,1),(753,'ULUKIŞLA',51,1),(754,'AKKUŞ',52,1),(755,'ALTINORDU',52,1),(756,'AYBASTI',52,1),(757,'ÇAMAŞ',52,1),(758,'ÇATALPINAR',52,1),(759,'ÇAYBAŞI',52,1),(760,'FATSA',52,1),(761,'GÖLKÖY',52,1),(762,'GÜLYALI',52,1),(763,'GÜRGENTEPE',52,1),(764,'İKİZCE',52,1),(765,'KABADÜZ',52,1),(766,'KABATAŞ',52,1),(767,'KORGAN',52,1),(768,'KUMRU',52,1),(769,'MESUDİYE',52,1),(770,'PERŞEMBE',52,1),(771,'ULUBEY',52,1),(772,'ÜNYE',52,1),(773,'BAHÇE',80,1),(774,'DÜZİÇİ',80,1),(775,'HASANBEYLİ',80,1),(776,'KADİRLİ',80,1),(777,'MERKEZ',80,1),(778,'SUMBAS',80,1),(779,'TOPRAKKALE',80,1),(780,'ARDEŞEN',53,1),(781,'ÇAMLIHEMŞİN',53,1),(782,'ÇAYELİ',53,1),(783,'DEREPAZARI',53,1),(784,'FINDIKLI',53,1),(785,'GÜNEYSU',53,1),(786,'HEMŞİN',53,1),(787,'İKİZDERE',53,1),(788,'İYİDERE',53,1),(789,'KALKANDERE',53,1),(790,'MERKEZ',53,1),(791,'PAZAR',53,1),(792,'ADAPAZARI',54,1),(793,'AKYAZI',54,1),(794,'ARİFİYE',54,1),(795,'ERENLER',54,1),(796,'FERİZLİ',54,1),(797,'GEYVE',54,1),(798,'HENDEK',54,1),(799,'KARAPÜRÇEK',54,1),(800,'KARASU',54,1),(801,'KAYNARCA',54,1),(802,'KOCAALİ',54,1),(803,'PAMUKOVA',54,1),(804,'SAPANCA',54,1),(805,'SERDİVAN',54,1),(806,'SÖĞÜTLÜ',54,1),(807,'TARAKLI',54,1),(808,'19 MAYIS',55,1),(809,'ALAÇAM',55,1),(810,'ASARCIK',55,1),(811,'ATAKUM',55,1),(812,'AYVACIK',55,1),(813,'BAFRA',55,1),(814,'CANİK',55,1),(815,'ÇARŞAMBA',55,1),(816,'HAVZA',55,1),(817,'İLKADIM',55,1),(818,'KAVAK',55,1),(819,'LADİK',55,1),(820,'SALIPAZARI',55,1),(821,'TEKKEKÖY',55,1),(822,'TERME',55,1),(823,'VEZİRKÖPRÜ',55,1),(824,'YAKAKENT',55,1),(825,'BAYKAN',56,1),(826,'ERUH',56,1),(827,'KURTALAN',56,1),(828,'MERKEZ',56,1),(829,'PERVARİ',56,1),(830,'ŞİRVAN',56,1),(831,'TİLLO',56,1),(832,'AYANCIK',57,1),(833,'BOYABAT',57,1),(834,'DİKMEN',57,1),(835,'DURAĞAN',57,1),(836,'ERFELEK',57,1),(837,'GERZE',57,1),(838,'MERKEZ',57,1),(839,'SARAYDÜZÜ',57,1),(840,'TÜRKELİ',57,1),(841,'AKINCILAR',58,1),(842,'ALTINYAYLA',58,1),(843,'DİVRİĞİ',58,1),(844,'DOĞANŞAR',58,1),(845,'GEMEREK',58,1),(846,'GÖLOVA',58,1),(847,'GÜRÜN',58,1),(848,'HAFİK',58,1),(849,'İMRANLI',58,1),(850,'KANGAL',58,1),(851,'KOYULHİSAR',58,1),(852,'MERKEZ',58,1),(853,'SUŞEHRİ',58,1),(854,'ŞARKIŞLA',58,1),(855,'ULAŞ',58,1),(856,'YILDIZELİ',58,1),(857,'ZARA',58,1),(858,'AKÇAKALE',63,1),(859,'BİRECİK',63,1),(860,'BOZOVA',63,1),(861,'CEYLANPINAR',63,1),(862,'EYYÜBİYE',63,1),(863,'HALFETİ',63,1),(864,'HALİLİYE',63,1),(865,'HARRAN',63,1),(866,'HİLVAN',63,1),(867,'KARAKÖPRÜ',63,1),(868,'SİVEREK',63,1),(869,'SURUÇ',63,1),(870,'VİRANŞEHİR',63,1),(871,'BEYTÜŞŞEBAP',73,1),(872,'CİZRE',73,1),(873,'GÜÇLÜKONAK',73,1),(874,'İDİL',73,1),(875,'MERKEZ',73,1),(876,'SİLOPİ',73,1),(877,'ULUDERE',73,1),(878,'ÇERKEZKÖY',59,1),(879,'ÇORLU',59,1),(880,'ERGENE',59,1),(881,'HAYRABOLU',59,1),(882,'KAPAKLI',59,1),(883,'MALKARA',59,1),(884,'MARMARAEREĞLİSİ',59,1),(885,'MURATLI',59,1),(886,'SARAY',59,1),(887,'SÜLEYMANPAŞA',59,1),(888,'ŞARKÖY',59,1),(889,'ALMUS',60,1),(890,'ARTOVA',60,1),(891,'BAŞÇİFTLİK',60,1),(892,'ERBAA',60,1),(893,'MERKEZ',60,1),(894,'NİKSAR',60,1),(895,'PAZAR',60,1),(896,'REŞADİYE',60,1),(897,'SULUSARAY',60,1),(898,'TURHAL',60,1),(899,'YEŞİLYURT',60,1),(900,'ZİLE',60,1),(901,'AKÇAABAT',61,1),(902,'ARAKLI',61,1),(903,'ARSİN',61,1),(904,'BEŞİKDÜZÜ',61,1),(905,'ÇARŞIBAŞI',61,1),(906,'ÇAYKARA',61,1),(907,'DERNEKPAZARI',61,1),(908,'DÜZKÖY',61,1),(909,'HAYRAT',61,1),(910,'KÖPRÜBAŞI',61,1),(911,'MAÇKA',61,1),(912,'OF',61,1),(913,'ORTAHİSAR',61,1),(914,'SÜRMENE',61,1),(915,'ŞALPAZARI',61,1),(916,'TONYA',61,1),(917,'VAKFIKEBİR',61,1),(918,'YOMRA',61,1),(919,'ÇEMİŞGEZEK',62,1),(920,'HOZAT',62,1),(921,'MAZGİRT',62,1),(922,'MERKEZ',62,1),(923,'NAZIMİYE',62,1),(924,'OVACIK',62,1),(925,'PERTEK',62,1),(926,'PÜLÜMÜR',62,1),(927,'BANAZ',64,1),(928,'EŞME',64,1),(929,'KARAHALLI',64,1),(930,'MERKEZ',64,1),(931,'SİVASLI',64,1),(932,'ULUBEY',64,1),(933,'BAHÇESARAY',65,1),(934,'BAŞKALE',65,1),(935,'ÇALDIRAN',65,1),(936,'ÇATAK',65,1),(937,'EDREMİT',65,1),(938,'ERCİŞ',65,1),(939,'GEVAŞ',65,1),(940,'GÜRPINAR',65,1),(941,'İPEKYOLU',65,1),(942,'MURADİYE',65,1),(943,'ÖZALP',65,1),(944,'SARAY',65,1),(945,'TUŞBA',65,1),(946,'ALTINOVA',77,1),(947,'ARMUTLU',77,1),(948,'ÇINARCIK',77,1),(949,'ÇİFTLİKKÖY',77,1),(950,'MERKEZ',77,1),(951,'TERMAL',77,1),(952,'AKDAĞMADENİ',66,1),(953,'AYDINCIK',66,1),(954,'BOĞAZLIYAN',66,1),(955,'ÇANDIR',66,1),(956,'ÇAYIRALAN',66,1),(957,'ÇEKEREK',66,1),(958,'KADIŞEHRİ',66,1),(959,'MERKEZ',66,1),(960,'SARAYKENT',66,1),(961,'SARIKAYA',66,1),(962,'SORGUN',66,1),(963,'ŞEFAATLİ',66,1),(964,'YENİFAKILI',66,1),(965,'YERKÖY',66,1),(966,'ALAPLI',67,1),(967,'ÇAYCUMA',67,1),(968,'DEVREK',67,1),(969,'EREĞLİ',67,1),(970,'GÖKÇEBEY',67,1),(971,'KİLİMLİ',67,1),(972,'KOZLU',67,1),(973,'MERKEZ',67,1);

--
-- Table structure for table `iller`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iller` (
  `iller_id` int(11) NOT NULL AUTO_INCREMENT,
  `sehiradi` varchar(255) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`iller_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Türkiye geneli il tanımları sözlüğü.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iller`
--

INSERT INTO `iller` VALUES (1,'ADANA',1),(2,'ADIYAMAN',1),(3,'AFYON',1),(4,'AĞRI',1),(5,'AMASYA',1),(6,'ANKARA',1),(7,'ANTALYA',1),(8,'ARTVİN',1),(9,'AYDIN',1),(10,'BALIKESİR',1),(11,'BİLECİK',1),(12,'BİNGÖL',1),(13,'BİTLİS',1),(14,'BOLU',1),(15,'BURDUR',1),(16,'BURSA',1),(17,'ÇANAKKALE',1),(18,'ÇANKIRI',1),(19,'ÇORUM',1),(20,'DENİZLİ',1),(21,'DİYARBAKIR',1),(22,'EDİRNE',1),(23,'ELAZIĞ',1),(24,'ERZİNCAN',1),(25,'ERZURUM',1),(26,'ESKİŞEHİR',1),(27,'GAZİANTEP',1),(28,'GİRESUN',1),(29,'GÜMÜŞHANE',1),(30,'HAKKARİ',1),(31,'HATAY',1),(32,'ISPARTA',1),(33,'İÇEL',1),(34,'İSTANBUL',1),(35,'İZMİR',1),(36,'KARS',1),(37,'KASTAMONU',1),(38,'KAYSERİ',1),(39,'KIRKLARELİ',1),(40,'KIRŞEHİR',1),(41,'KOCAELİ',1),(42,'KONYA',1),(43,'KÜTAHYA',1),(44,'MALATYA',1),(45,'MANİSA',1),(46,'KAHRAMANMARAŞ',1),(47,'MARDİN',1),(48,'MUĞLA',1),(49,'MUŞ',1),(50,'NEVŞEHİR',1),(51,'NİĞDE',1),(52,'ORDU',1),(53,'RİZE',1),(54,'SAKARYA',1),(55,'SAMSUN',1),(56,'SİİRT',1),(57,'SİNOP',1),(58,'SİVAS',1),(59,'TEKİRDAĞ',1),(60,'TOKAT',1),(61,'TRABZON',1),(62,'TUNCELİ',1),(63,'ŞANLIURFA',1),(64,'UŞAK',1),(65,'VAN',1),(66,'YOZGAT',1),(67,'ZONGULDAK',1),(68,'AKSARAY',1),(69,'BAYBURT',1),(70,'KARAMAN',1),(71,'KIRIKKALE',1),(72,'BATMAN',1),(73,'ŞIRNAK',1),(74,'BARTIN',1),(75,'ARDAHAN',1),(76,'IĞDIR',1),(77,'YALOVA',1),(78,'KARABÜK',1),(79,'KİLİS',1),(80,'OSMANİYE',1),(81,'DÜZCE',1);

--
-- Table structure for table `kapakcinsi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kapakcinsi` (
  `KapakCinsiID` int(11) NOT NULL AUTO_INCREMENT,
  `KapakCinsiName` varchar(255) DEFAULT NULL,
  `bKapakStandart` tinyint(4) NOT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`KapakCinsiID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Üretim için kapak cinsi materyal tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kapakcinsi`
--

INSERT INTO `kapakcinsi` VALUES (1,'SAFİRLAM',1,1),(2,'HİGHGLASS',1,1),(3,'MEBRAN',1,1),(4,'AKRİLİK',1,1),(5,'LAKE',1,1);

--
-- Table structure for table `kasahareketleri`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kasahareketleri` (
  `KasaHareketID` int(11) NOT NULL AUTO_INCREMENT,
  `AdminStatus` tinyint(11) NOT NULL DEFAULT 0,
  `UserID` int(11) DEFAULT NULL,
  `TahsilatID` int(11) DEFAULT 0,
  `KasaTip` tinyint(4) DEFAULT NULL,
  `IslemTipi` tinyint(4) DEFAULT NULL,
  `Detay` varchar(255) DEFAULT NULL,
  `KasaTutar` double(10,2) DEFAULT NULL,
  `KasaDesc` varchar(255) DEFAULT NULL,
  `TransferTarih` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`KasaHareketID`),
  KEY `idx_kasahareketleri_islemtipi` (`IslemTipi`),
  KEY `idx_kasahareketleri_UserID` (`UserID`),
  KEY `idx_opt_kasa_tahsilat` (`TahsilatID`,`del_status`),
  KEY `idx_opt_kasa_tarih` (`TransferTarih`,`del_status`),
  KEY `idx_opt_kasa_islem` (`KasaTip`,`IslemTipi`,`del_status`),
  KEY `idx_opt_kasa_user` (`UserID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tüm kasalara (nakit, banka) giren ve çıkan nakit hareketleri.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasahareketleri`
--


--
-- Table structure for table `kasatips`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kasatips` (
  `KasaTipsID` int(11) NOT NULL AUTO_INCREMENT,
  `KasaTip` tinyint(4) DEFAULT NULL,
  `KasaDetay` varchar(255) DEFAULT NULL,
  `CreadetDate` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`KasaTipsID`) USING BTREE,
  KEY `idx_kasatips_kasatipsid` (`KasaTipsID`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemdeki kasa türleri (Nakit, Banka, POS vb.) tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasatips`
--

INSERT INTO `kasatips` VALUES (1,1,'Nakit','2023-11-03 01:48:49',1),(2,1,'Kredi Kartı','2023-11-03 01:49:11',1),(3,1,'Banka Havalesi','2023-11-03 01:49:26',1),(4,1,'Çek','2023-11-03 01:49:39',1),(5,1,'Diğer','2023-11-03 01:49:46',1),(6,2,'Kira','2023-11-03 01:49:57',1),(7,2,'Elektrik','2023-11-03 01:50:16',1),(8,2,'Telefon','2023-11-03 01:53:13',1),(9,2,'Araç Yakıt Giderleri','2023-11-03 01:53:25',1),(10,2,'Şube Yakıt (Aidat) Giderleri','2023-11-03 01:53:44',1),(11,2,'Otoban Ücretleri','2023-11-03 01:55:23',1),(12,2,'Montaj Giderleri','2023-11-03 01:55:34',1),(13,2,'Mağaza Giderleri','2023-11-03 01:55:43',1),(14,2,'Yemek Parası','2023-11-03 01:55:58',1),(15,2,'Yol Parası','2023-11-03 01:56:07',1),(16,2,'Prim','2023-11-03 01:56:18',1),(17,2,'Maaş','2023-11-03 01:57:42',1),(18,2,'Avans','2023-11-03 01:57:48',1),(19,2,'Merkeze Gönderilen Para','2023-11-03 01:58:20',1),(20,2,'Diğer Yerlere Giden','2023-11-03 01:58:40',1),(21,2,'Avukata Verilen Senetler','2023-11-03 01:58:51',1),(22,2,'İskonto','2023-11-03 01:58:59',1),(23,2,'Kırtasiye','2023-11-03 01:59:08',1),(24,1,'Sözleşme','2023-11-03 02:11:20',1),(27,2,'Mal Alış','2024-04-01 12:42:38',1),(28,2,'Banka Komisyon','2024-04-01 17:06:30',1),(29,2,'Kasaya Nakit Girdi','2024-05-10 03:06:29',1),(30,2,'Araç Tamir','2024-11-18 10:53:05',1),(31,2,'Su','2024-11-18 12:12:47',1),(32,2,'Mutfak ve Yemek Giderleri','2024-11-18 13:09:49',1),(33,2,'Sosyal Medya, Katalog, Reklam vb.','2024-11-18 15:26:24',1),(34,2,'Araç Sigortaları','2024-11-18 15:35:11',1),(35,2,'Nakliye, Kargo vb. Giderleri','2024-11-18 15:40:33',1),(36,2,'SSK Ödemeleri','2024-11-18 21:29:13',1),(37,2,'Stopaj Ödemeleri','2024-11-18 21:29:42',1),(38,2,'Sair Giderler','2024-11-26 14:50:28',1),(39,2,'KDV Ödemeleri','2024-11-26 15:04:25',1),(40,2,'Personel Servis','2025-01-13 15:05:56',1),(41,2,'Banka Kredileri','2025-11-19 12:52:19',1),(42,2,'Aksesuar Alış','2026-02-12 14:42:49',1),(43,2,'Vergiler','2026-02-20 09:23:53',1);

--
-- Table structure for table `log`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `LogID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `LogType` text DEFAULT NULL,
  `LogDesc` mediumtext DEFAULT NULL,
  `LogDate` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`LogID`) USING BTREE,
  KEY `idx_log_UserID` (`UserID`),
  KEY `idx_opt_log_user_date` (`UserID`,`LogDate`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kullanıcı işlemlerinin tutulduğu sistem aktivite logları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--


--
-- Table structure for table `lognew`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lognew` (
  `LogID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `LogType` tinyint(3) unsigned NOT NULL DEFAULT 3 COMMENT 'Log Tipi: 1=Hata, 2=Uyarı, 3=Admin İşlemi, 4=Bilgi, 5=Hata Ayıklama',
  `LogDesc` varchar(500) NOT NULL COMMENT 'İşlem Açıklaması',
  `LogStatus` enum('success','error','warning','pending','failed','info') DEFAULT 'info' COMMENT 'İşlem Durumu',
  `UserID` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'İşlemi yapan kullanıcı ID''si',
  `IpAddress` varchar(45) DEFAULT '0.0.0.0' COMMENT 'İstek yapılan IP adresi (IPv4 veya IPv6)',
  `UserAgent` varchar(255) DEFAULT NULL COMMENT 'Tarayıcı / İstemci bilgisi',
  `RequestMethod` varchar(10) DEFAULT 'GET' COMMENT 'HTTP Method: GET, POST, PUT, DELETE, PATCH',
  `RequestPath` varchar(255) DEFAULT NULL COMMENT 'İstek yapılan sayfa yolu (URI)',
  `OldValue` mediumtext DEFAULT NULL COMMENT 'Eski değer (JSON formatında)',
  `NewValue` mediumtext DEFAULT NULL COMMENT 'Yeni değer (JSON formatında)',
  `EntityType` varchar(100) DEFAULT NULL COMMENT 'İşlem yapılan tablo/varlık tipi: users, musteriler, sozlesmeler, vb.',
  `EntityID` int(10) unsigned DEFAULT NULL COMMENT 'İşlem yapılan kaydın ID''si',
  `AdditionalData` mediumtext DEFAULT NULL COMMENT 'İlave veri (JSON): changed_fields, metadata, vb.',
  `LogDate` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Log oluşturulma tarihi',
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`LogID`),
  KEY `idx_user_id` (`UserID`),
  KEY `idx_log_type` (`LogType`),
  KEY `idx_log_status` (`LogStatus`),
  KEY `idx_entity` (`EntityType`,`EntityID`),
  KEY `idx_log_date` (`LogDate`),
  KEY `idx_ip_address` (`IpAddress`),
  KEY `idx_log_type_date` (`LogType`,`LogDate`),
  KEY `idx_user_date` (`UserID`,`LogDate`),
  KEY `idx_opt_lognew_created` (`LogDate`),
  KEY `idx_opt_lognew_user_date` (`UserID`,`LogDate`),
  FULLTEXT KEY `ft_log_desc` (`LogDesc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Genişletilmiş yapıyla tutulan yeni sistem aktivite logları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lognew`
--


--
-- Table structure for table `logtypes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `names` varchar(255) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Loglarda kullanılan aktivite tiplerinin (Ekleme, Silme vb.) tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logtypes`
--

INSERT INTO `logtypes` VALUES (1,'Sistem Girişi',1),(2,'Kullanıcı İşlemleri',1),(3,'Randevu İşlemleri',1),(4,'Ürün İşlemleri',1),(5,'Şube İşlemleri',1),(6,'Raporlama İşlemleri',1),(7,'Kullanıcı Grup İşlemleri',1),(8,'Montaj Usta İşlemleri',1);

--
-- Table structure for table `logtypesnew`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logtypesnew` (
  `id` tinyint(3) unsigned NOT NULL,
  `names` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT 'primary',
  `icon` varchar(50) DEFAULT 'fas fa-info-circle',
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Genişletilmiş log tipleri tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logtypesnew`
--

INSERT INTO `logtypesnew` VALUES (1,'Hata','Sistem hataları ve istisnalar','danger','fas fa-exclamation-circle',1),(2,'Uyarı','Uyarı ve güvenlik olayları','warning','fas fa-exclamation-triangle',1),(3,'İşlem','Admin ve sistem işlemleri','info','fas fa-check-circle',1),(4,'Bilgi','Bilgilendirme ve raporlama','secondary','fas fa-info-circle',1),(5,'Hata Ayıklama','Geliştirme ve hata ayıklama','dark','fas fa-bug',1);

--
-- Table structure for table `montaj_problemler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `montaj_problemler` (
  `ProblemID` int(11) NOT NULL AUTO_INCREMENT,
  `AppID` int(11) NOT NULL COMMENT 'Randevu ID - randevular tablosu ile ilişki',
  `CustomerID` int(11) NOT NULL COMMENT 'Müşteri ID - musteriler tablosu ile ilişki',
  `ProblemNo` int(11) DEFAULT 1 COMMENT 'Sıra numarası',
  `ProblemDesc` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci DEFAULT NULL COMMENT 'Yapılacak İş',
  `ProblemReason` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci DEFAULT NULL COMMENT 'Hata Sebebi',
  `ProblemStatus` tinyint(1) DEFAULT 0 COMMENT 'İşin Durumu: 0=Bekliyor, 1=Devam Ediyor, 2=Tamamlandı',
  `ProblemResult` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci DEFAULT NULL COMMENT 'Sonuç',
  `ProblemDate` date DEFAULT NULL COMMENT 'Tarih',
  `VisitCount` int(11) DEFAULT 1 COMMENT 'Ziyaret sayısı - her güncelleme artırılabilir',
  `CreatedBy` int(11) DEFAULT NULL COMMENT 'Oluşturan kullanıcı ID',
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ProblemID`),
  KEY `idx_app_id` (`AppID`),
  KEY `idx_customer_id` (`CustomerID`),
  KEY `idx_problem_status` (`ProblemStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Saha veya montaj sırasında karşılaşılan sorunların kayıtları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `montaj_problemler`
--


--
-- Table structure for table `musteri_ziyaret`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `musteri_ziyaret` (
  `ZiyaretID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `AppID` int(11) NOT NULL,
  `ZiyaretCount` int(11) DEFAULT 1 COMMENT 'Bu randevuya kaç kez gidildi',
  `LastVisit` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ZiyaretID`),
  UNIQUE KEY `unique_customer_app` (`CustomerID`,`AppID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Satış temsilcilerinin gerçekleştirdiği müşteri ziyaret notları ve tarihleri.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteri_ziyaret`
--


--
-- Table structure for table `musteriler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `musteriler` (
  `CustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerNo` int(11) DEFAULT NULL,
  `CustomerType` varchar(255) DEFAULT NULL,
  `CustomerSales` int(11) NOT NULL DEFAULT 0,
  `CustomerName` varchar(255) DEFAULT NULL,
  `CustomerPhone` varchar(255) DEFAULT NULL,
  `CustomerPhone2` varchar(255) DEFAULT NULL,
  `CustomerMail` varchar(255) DEFAULT NULL,
  `CustomerTCVNo` varchar(255) DEFAULT NULL,
  `CustomerVD` varchar(255) DEFAULT NULL,
  `CustomerCity` varchar(255) DEFAULT NULL,
  `CustomerTown` varchar(255) DEFAULT NULL,
  `CustomerAdress` varchar(255) DEFAULT NULL,
  `CustomerPrint` varchar(255) DEFAULT '0',
  `CustomerDesc` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`CustomerID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sisteme kayıtlı tüm bireysel ve kurumsal müşteri kartları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--


--
-- Table structure for table `nakitkasa`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nakitkasa` (
  `NakitKasaID` int(11) NOT NULL AUTO_INCREMENT,
  `FirmaID` int(11) NOT NULL,
  `NakitKasaTutar` double(10,2) NOT NULL,
  `UpdateDate` datetime NOT NULL DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`NakitKasaID`),
  KEY `idx_nakitkasa_FirmaID` (`FirmaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sadece nakit kasa üzerinden yapılan işlemlerin kayıtları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nakitkasa`
--


--
-- Table structure for table `odemeler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `odemeler` (
  `PayID` int(11) NOT NULL AUTO_INCREMENT,
  `ContractID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `OdemeAdet` int(11) DEFAULT NULL,
  `OdemeTutar` varchar(255) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`PayID`) USING BTREE,
  KEY `idx_odemeler_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tedarikçilere veya kurumlara yapılan ödemelerin kayıtları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `odemeler`
--


--
-- Table structure for table `randevular`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `randevular` (
  `AppID` int(11) NOT NULL AUTO_INCREMENT,
  `AppDate` date DEFAULT NULL,
  `AppDateEnd` date DEFAULT NULL,
  `AppDelivery` date DEFAULT NULL,
  `AppOrderNo` varchar(255) DEFAULT NULL,
  `ContractNo` varchar(255) DEFAULT NULL,
  `AppBranch` varchar(255) DEFAULT NULL,
  `AppUser` varchar(255) DEFAULT NULL,
  `AppMontajUsta` varchar(255) DEFAULT NULL,
  `AppCustomer` varchar(255) DEFAULT NULL,
  `AppPear` varchar(255) DEFAULT NULL,
  `AppMetreTul` varchar(255) DEFAULT NULL,
  `AppProduct` varchar(255) DEFAULT NULL,
  `AppStatus` varchar(255) DEFAULT NULL,
  `AppDesc` longtext DEFAULT NULL,
  `RecordDate` datetime NOT NULL DEFAULT current_timestamp(),
  `AppStatusUpdateDate` datetime DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`AppID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteriler veya montaj işlemleri için oluşturulan takvim randevuları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `randevular`
--

/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_appstatus_date` BEFORE UPDATE ON `randevular` FOR EACH ROW BEGIN
    IF NEW.AppStatus <> OLD.AppStatus THEN
        SET NEW.AppStatusUpdateDate = NOW();
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `silinenrandevular`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `silinenrandevular` (
  `AppID` int(11) NOT NULL AUTO_INCREMENT,
  `AppDate` date DEFAULT NULL,
  `AppDateEnd` date DEFAULT NULL,
  `AppOrderNo` varchar(255) DEFAULT NULL,
  `AppBranch` varchar(255) DEFAULT NULL,
  `AppUser` varchar(255) DEFAULT NULL,
  `AppMontajUsta` varchar(255) DEFAULT NULL,
  `AppCustomer` varchar(255) DEFAULT NULL,
  `AppPhone` varchar(255) DEFAULT NULL,
  `il` varchar(255) DEFAULT NULL,
  `ilce` varchar(255) DEFAULT NULL,
  `AppPear` varchar(255) DEFAULT NULL,
  `AppMetreTul` varchar(255) DEFAULT NULL,
  `AppProduct` varchar(255) DEFAULT NULL,
  `AppStatus` varchar(255) DEFAULT NULL,
  `AppDesc` varchar(255) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`AppID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='İptal edilen veya silinen randevuların geçmiş kayıtları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `silinenrandevular`
--


--
-- Table structure for table `sozlesme_sablonlari`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sozlesme_sablonlari` (
  `SozlesmeID` int(11) NOT NULL AUTO_INCREMENT,
  `SozlesmeName` varchar(255) DEFAULT NULL,
  `SozlesmeIcerik` longtext DEFAULT NULL,
  `CreatedUserID` int(11) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedUserID` int(11) DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`SozlesmeID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Farklı satış türleri için oluşturulmuş dinamik sözleşme metin şablonları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sozlesme_sablonlari`
--

INSERT INTO `sozlesme_sablonlari` VALUES (1,'Mutfak Sözleşme','<p style=\"margin-left:40px;\">{firma_logo}</p><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:72.15%;\"><col style=\"width:27.85%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" rowspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:22px;\"><span style=\"text-align:center;\"><strong>SİPARİŞ VE HİZMET SÖZLEŞMESİ</strong></span>&nbsp;</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME TARİHİ : {sozlesme_tarihi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">MONTAJ TARİHİ &nbsp; : {montaj_tarihi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME NO : {sozlesme_no}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:20.06%;\"><col style=\"width:2.54%;\"><col style=\"width:77.4%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" colspan=\"3\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 1 - FİRMA BİLGİLERİ</strong></span></h4></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">FİRMA ÜNVANI&nbsp;</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TELEFON</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADRES</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:22.07%;\"><col style=\"width:3.65%;\"><col style=\"width:74.28%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" colspan=\"3\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 2 - MÜŞTERİ BİLGİLERİ</strong></span></h4></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Müşteri Adı Soyadı</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{MusteriAdi}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Vergi K. No veya T.C. K. No</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{KimlikNo}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Telefon</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Telefon}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Sevkiyat Adresi</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{SevkiyatAdres}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">E-Posta Adresi</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{MusteriEposta}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:95.59%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:22.3%;\"><col style=\"width:3.7%;\"><col style=\"width:74%;\"></colgroup><tbody><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 3 - ÜRÜN BİLGİLERİ</strong></span></h4></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"2\"><h5 style=\"text-align:center;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MUTFAK ÜRÜN GRUBU</strong></span></h5></td><td style=\"padding:0px;vertical-align:top;\"><h5><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>&nbsp;AÇIKLAMA</strong></span></h5></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>Üst Dolap Boyu</span></span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{DolapBoy}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde Cinsi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{GovdeCinsi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{GovdeRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Kapak Cinsi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{KapakCinsi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Alt Kapak Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{AltKapakRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Üst Kapak Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{UstKapakRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Boy Kapak Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{BoyKapakRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Camlı Kapak Cinsi ve Modeli</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{CamKapakModel}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Cam Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{CamRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Ayna Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{AynaRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Işık Bandı Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{IsikBandi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Taç Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{TacBandi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Baza Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Baza}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Kulp Modeli ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Kulp}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Çekmece Marka ve Modeli</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Cekmece}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Menteşe Marka ve Modeli</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Mentese}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Kalkar Kapak Sistemi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{KalkarKapak}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Spotluk ve Işık</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Spotluk}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Görünen Yanlar Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Yanlar}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Mutfak Toplam Tutar : {MutfakToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>EVYE GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Evye Marka ve Modeli</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Evye}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Evye Toplam Tutar : {EvyeToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>TEZGAH GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Tezgah Adı - Renk - Ölçü</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Tezgah}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Tezgah Toplam Tutar : {TezgahToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>AKSESUAR GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>Adı ve Modeli</span></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>:</span></span></td><td class=\"uruns\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>{Aksesuar}</span></span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Aksesuar Toplam Tutar : {AksesuarToplam} &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>BANYO DOLABI GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde - Kapak - Renk - Tezgah - Ölçü</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{BanyoDolap}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Banyo Dolap Toplam Tutar : {BanyoToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>DİĞER DOLAP GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde - Kapak - Renk - Ölçü</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{DigerDolap}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Diğer Dolap Toplam Tutar : {DolapToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>ANKASTRE ÜRÜN GRUBU</u></strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Marka - Model - Renk</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Ankastre}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Ankastre Toplam Tutar : {AnkastreToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Genel Toplam : {GenelToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>İskonto : {Iskonto} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Net Tutar : {NetTutar} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 4 - ÖDEME BİLGİLERİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Yukarıda belirtilen satışa konu olan malların toplam net satış bedeli : &nbsp; {NetTutar} &nbsp;. Bu bedelin ödeme şekli &nbsp; “ {OdemeTipi} ” &nbsp; şeklindedir. Ödeme planı aşağıdaki tabloda gösterilmiştir.</span></p><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{OdemeTablosu}</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 5 - SÖZLEŞME METNİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İş bu sözleşme müşteri ve {firma_adi} tarafından düzenlenmiş olup, {firma_adi} tarafından sunulacak ürün ve hizmetlere ilişkin belirlenen kuralları ihtiva eder.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Müşteri, {firma_adi} tarafından firmanın sunmuş olduğu ve aşağıda ilgili bölümde ayrıntıları belirtilen ürün ve hizmetlerden işbu sözleşmede yer alan ve taraflarca karşılıklı müzakere edilerek kabul edilmiş hükümler çerçevesinde yararlanabileceğini, her ürün ve hizmete ilişkin belirlenen kurallara uyacağını kabul ve taahhüt eder. İşbu sözleşme, müşterinin yararlanacağı ürün ve hizmetlerin işleyişini, uygulanacak kuralları ve müşteri ile firmamızın hak ve sorumluluklarını belirlemek amacıyla hazırlanıp imzalanmıştır.</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 6 - AÇIKLAMALAR</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (a) - Projeler ve teklifler karşılıklı imzalanmadığı takdirde eposta, mesaj ve whatsapp üzerinden gönderilebilmektedir. Müşteri ürünü veya hizmeti satın aldıktan sonra, kişiye ait iletişim bilgilerinden gönderilen proje ve teklifler karşılıklı kabul edildiğinde TBK madde 4 gereği tarafları bağlayıcı nitelikte olup işbu sözleşme akdedilmiş sayılır.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (b) - Müşteri, satın aldığı ürünü onayladığı anda, ödeme tutarının tamamını ödemekle yükümlüdür. Ödeme yapılmadığı takdirde, montaj işlemi gerçekleştirilmeyecektir. Ödeme yapılmayan ürünler için, montaj talebi reddedilecek ve yeni bir montaj tarihi, ödeme tamamlandıktan sonra 45 gün sonrasına verilecektir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (c) - Montaj yapıldıktan sonra {firma_adi} adına kalan ödemelerde ücreti ödenmediği takdirde TTK madde 8 gereği yasal faizleri ile birlikte müşterinin ödemesi gerekmektedir. Fazlaya ilişkin hakları saklıdır.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (d) - Bu ürünler müşteri evine göre özel yapılmaktadır. Sipariş üretime geçtikten sonra, proje değişikliği yapılamaz ve iade edilemez.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (e) - Firma vermiş olduğu hizmet kapsamında ki projelerde, dolapların sağında veya solunda 3 ile 6 cm arasında duvar yamukluklarından dolayı kapama ile kapatabilir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (f) - Firma kabul edilen hizmet kapsamında dolapların montajı yapıldıktan sonra tezgah ölçüsünü alır 3 ile 7 gün arasında tezgah montajı yapar.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (g) - Firma proje hazırlanırken müşteri tarafından özel ölçü belirtilmeyen üst dolap ve alt dolap yükseklikleri için standart ölçüler olan <strong>Üst Dolap 72 cm</strong>\' dir, <strong>Yerden Yükseklik 217 cm</strong>\'de biter. <strong>Alt Dolap 72,6 cm</strong> <strong>Tezgahla Beraber Yerden Yükseklik 90 cm</strong>\'dir. Derinlik kapakla beraber <strong>Üst Dolap 35 cm</strong>, <strong>Alt Dolap 58 cm</strong>\'dir. Eviye dolaplarında tesisatlardan dolayı arkalık yoktur, sadece 11 cm altta kayıt vardır.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (h) - Firma sözleşme kapsamında müşterinin konutunun içerisinde çok sayıda veya zor olan kolon, kiriş, duvar su boruları gibi yerinde uygulama yapılması gereken yerlerde montaj ekibi yerinde keserek uygulama yapabilir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (ı) - Tezgah uygulamalarında yerine birebir uygulama olması durumunda, müşterinin konutu içerisinde mutfakta kesim yapılabilir,, müşteri bu duruma rıza göstermek zorundadır. <strong>L</strong> tezgahlar 2 parça halinde montaj yapılır. Ek yerleri belli olabilir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (i) - Elektrik, doğalgaz, su tesisatları ve seramikler ile firmamızın hiç bir ilgisi ve bilgisi yoktur, müşteriye aittir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (j) - Buzdolabınızın genişlik ve yüksekliğini mutlaka tasarımcı arkadaşımızla paylaşmanız gerekmektedir. Aksi halde genişlik 80 cm, yükseklik 192 cm boşluk bırakılacaktır. Alt dolaplarımızın derinlikleri kapak dahil 60 cm olduğundan, Buzdolabınızın derinliği 60 cm dolapların içinde kalacaktır. Geriye kalan kısmı öne doğru çıkmaktadır. Hiç dışarıda kalmamasını isterseniz, bütün alt dolapların derinlikleri standartlıktan çıkar, buzdolabı derinliğine göre özel yapılması gerekir. Bunun bedelini ödeyerek, tasarım yapıldığında belirtmeniz gerekmektedir.</span></p></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table><tbody><tr><td style=\"vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 8 - BANKA BİLGİLERİ</strong></span></td></tr></tbody></table></figure><p>{IBAN}</p><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 9 – İHTİLAFLARININ HALLİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İşbu sözleşme ve ekleri &nbsp; {sozlesme_tarihi} &nbsp; tarihinde taraflarca her sayfası paraflanıp son sayfası imzalandıktan sonra 2 nüsha olarak hazırlanmış olup bu sözleşmeden doğacak ihtilaflarının hallinde Türkiye Cumhuriyeti Mahkemeleri ve İcra Daireleri yetkilidir.</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 10 - GARANTİ SÜRESİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Montaj tarihinden itibaren ürettiğimiz dolaplar imalat hatalarına karşı 2 yıl firma garantisi kapsamındadır.</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:10.72%;\"><col style=\"width:3.65%;\"><col style=\"width:37.63%;\"><col style=\"width:4.1%;\"><col style=\"width:3.65%;\"><col style=\"width:40.25%;\"></colgroup><tbody><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SATICI</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td><td style=\"padding:0px;text-align:justify;vertical-align:top;\" colspan=\"3\" rowspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Bu sözleşmeyi okudum ve anladım. Yukarıda belirtilen ürün bilgileri ve ürünlere ait teknik terimleri sordum ve anlamını öğrendim. Sözleşmede yazılı her maddeyi kabul ediyorum ve itirazım olmayacaktır.</strong></span></td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ŞUBE</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_sube}</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ALICI</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{MusteriAdi}</span></td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{SatisPersonel}</span></td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">KAŞE, İMZA</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İMZA</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\">&nbsp;</td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td></tr></tbody></table></figure>',1,'2023-03-03 13:41:05',1,'2025-01-07 13:38:59',1),(2,'Yedek Sözleşme','<p style=\"margin-left:40px;\">{firma_logo}</p><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:72.15%;\"><col style=\"width:27.85%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" rowspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:22px;\"><span style=\"text-align:center;\"><strong>SİPARİŞ VE HİZMET SÖZLEŞMESİ</strong></span>&nbsp;</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME TARİHİ : {sozlesme_tarihi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">MONTAJ TARİHİ &nbsp; : {montaj_tarihi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME NO : {sozlesme_no}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:20.06%;\"><col style=\"width:2.54%;\"><col style=\"width:77.4%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" colspan=\"3\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 1 - FİRMA BİLGİLERİ</strong></span></h4></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">FİRMA ÜNVANI&nbsp;</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TELEFON</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADRES</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:22.07%;\"><col style=\"width:3.65%;\"><col style=\"width:74.28%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" colspan=\"3\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 2 - MÜŞTERİ BİLGİLERİ</strong></span></h4></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Müşteri Adı Soyadı</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{MusteriAdi}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Vergi K. No veya T.C. K. No</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{KimlikNo}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Telefon</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Telefon}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Sevkiyat Adresi</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{SevkiyatAdres}</span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">E-Posta Adresi</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{MusteriEposta}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:95.59%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:22.3%;\"><col style=\"width:3.7%;\"><col style=\"width:74%;\"></colgroup><tbody><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 3 - ÜRÜN BİLGİLERİ</strong></span></h4></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"2\"><h5 style=\"text-align:center;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MUTFAK ÜRÜN GRUBU</strong></span></h5></td><td style=\"padding:0px;vertical-align:top;\"><h5><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>&nbsp;AÇIKLAMA</strong></span></h5></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>Üst Dolap Boyu</span></span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{DolapBoy}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde Cinsi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{GovdeCinsi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{GovdeRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Kapak Cinsi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{KapakCinsi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Alt Kapak Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{AltKapakRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Üst Kapak Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{UstKapakRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Boy Kapak Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{BoyKapakRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Camlı Kapak Cinsi ve Modeli</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{CamKapakModel}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Cam Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{CamRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Ayna Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{AynaRengi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Işık Bandı Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{IsikBandi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Taç Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{TacBandi}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Baza Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Baza}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Kulp Modeli ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Kulp}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Çekmece Marka ve Modeli</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Cekmece}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Menteşe Marka ve Modeli</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Mentese}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Kalkar Kapak Sistemi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{KalkarKapak}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Spotluk ve Işık</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Spotluk}</span></td></tr><tr><td class=\"urun\" style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Görünen Yanlar Cinsi ve Rengi</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"border-bottom:1px solid #cdcdcd;padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Yanlar}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Mutfak Toplam Tutar : {MutfakToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>EVYE GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Evye Marka ve Modeli</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Evye}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Evye Toplam Tutar : {EvyeToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>TEZGAH GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Tezgah Adı - Renk - Ölçü</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Tezgah}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Tezgah Toplam Tutar : {TezgahToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>AKSESUAR GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>Adı ve Modeli</span></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>:</span></span></td><td class=\"uruns\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><span>{Aksesuar}</span></span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Aksesuar Toplam Tutar : {AksesuarToplam} &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>BANYO DOLABI GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde - Kapak - Renk - Tezgah - Ölçü</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{BanyoDolap}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Banyo Dolap Toplam Tutar : {BanyoToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>DİĞER DOLAP GRUBU</u></strong></span></td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Gövde - Kapak - Renk - Ölçü</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{DigerDolap}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Diğer Dolap Toplam Tutar : {DolapToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td class=\"urun\" style=\"padding:0px;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong><u>ANKASTRE ÜRÜN GRUBU</u></strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Marka - Model - Renk</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Ankastre}</span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Ankastre Toplam Tutar : {AnkastreToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;vertical-align:top;\" colspan=\"3\">&nbsp;</td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Genel Toplam : {GenelToplam} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>İskonto : {Iskonto} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr><tr><td style=\"padding:0px;text-align:right;vertical-align:top;\" colspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Net Tutar : {NetTutar} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong></span></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 4 - ÖDEME BİLGİLERİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Yukarıda belirtilen satışa konu olan malların toplam net satış bedeli : &nbsp; {NetTutar} &nbsp;. Bu bedelin ödeme şekli &nbsp; “ {OdemeTipi} ” &nbsp; şeklindedir. Ödeme planı aşağıdaki tabloda gösterilmiştir.</span></p><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{OdemeTablosu}</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 5 - SÖZLEŞME METNİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İş bu sözleşme müşteri ve {firma_adi} tarafından düzenlenmiş olup, {firma_adi} tarafından sunulacak ürün ve hizmetlere ilişkin belirlenen kuralları ihtiva eder.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Müşteri, {firma_adi} tarafından firmanın sunmuş olduğu ve aşağıda ilgili bölümde ayrıntıları belirtilen ürün ve hizmetlerden işbu sözleşmede yer alan ve taraflarca karşılıklı müzakere edilerek kabul edilmiş hükümler çerçevesinde yararlanabileceğini, her ürün ve hizmete ilişkin belirlenen kurallara uyacağını kabul ve taahhüt eder. İşbu sözleşme, müşterinin yararlanacağı ürün ve hizmetlerin işleyişini, uygulanacak kuralları ve müşteri ile firmamızın hak ve sorumluluklarını belirlemek amacıyla hazırlanıp imzalanmıştır.</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 6 - AÇIKLAMALAR</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (a) - Projeler ve teklifler karşılıklı imzalanmadığı takdirde eposta, mesaj ve whatsapp üzerinden gönderilebilmektedir. Müşteri ürünü veya hizmeti satın aldıktan sonra, kişiye ait iletişim bilgilerinden gönderilen proje ve teklifler karşılıklı kabul edildiğinde TBK madde 4 gereği tarafları bağlayıcı nitelikte olup işbu sözleşme akdedilmiş sayılır.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (b) - Müşteri, satın aldığı ürünü onayladığı anda, ödeme tutarının tamamını ödemekle yükümlüdür. Ödeme yapılmadığı takdirde, montaj işlemi gerçekleştirilmeyecektir. Ödeme yapılmayan ürünler için, montaj talebi reddedilecek ve yeni bir montaj tarihi, ödeme tamamlandıktan sonra 45 gün sonrasına verilecektir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (c) - Montaj yapıldıktan sonra {firma_adi} adına kalan ödemelerde ücreti ödenmediği takdirde TTK madde 8 gereği yasal faizleri ile birlikte müşterinin ödemesi gerekmektedir. Fazlaya ilişkin hakları saklıdır.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (d) - Bu ürünler müşteri evine göre özel yapılmaktadır. Sipariş üretime geçtikten sonra, proje değişikliği yapılamaz ve iade edilemez.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (e) - Firma vermiş olduğu hizmet kapsamında ki projelerde, dolapların sağında veya solunda 3 ile 6 cm arasında duvar yamukluklarından dolayı kapama ile kapatabilir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (f) - Firma kabul edilen hizmet kapsamında dolapların montajı yapıldıktan sonra tezgah ölçüsünü alır 3 ile 7 gün arasında tezgah montajı yapar.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (g) - Firma proje hazırlanırken müşteri tarafından özel ölçü belirtilmeyen üst dolap ve alt dolap yükseklikleri için standart ölçüler olan <strong>Üst Dolap 72 cm</strong>\' dir, <strong>Yerden Yükseklik 217 cm</strong>\'de biter. <strong>Alt Dolap 72,6 cm</strong> <strong>Tezgahla Beraber Yerden Yükseklik 90 cm</strong>\'dir. Derinlik kapakla beraber <strong>Üst Dolap 35 cm</strong>, <strong>Alt Dolap 58 cm</strong>\'dir. Eviye dolaplarında tesisatlardan dolayı arkalık yoktur, sadece 11 cm altta kayıt vardır.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (h) - Firma sözleşme kapsamında müşterinin konutunun içerisinde çok sayıda veya zor olan kolon, kiriş, duvar su boruları gibi yerinde uygulama yapılması gereken yerlerde montaj ekibi yerinde keserek uygulama yapabilir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (ı) - Tezgah uygulamalarında yerine birebir uygulama olması durumunda, müşterinin konutu içerisinde mutfakta kesim yapılabilir,, müşteri bu duruma rıza göstermek zorundadır. <strong>L</strong> tezgahlar 2 parça halinde montaj yapılır. Ek yerleri belli olabilir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (i) - Elektrik, doğalgaz, su tesisatları ve seramikler ile firmamızın hiç bir ilgisi ve bilgisi yoktur, müşteriye aittir.</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Madde 6 (j) - Buzdolabınızın genişlik ve yüksekliğini mutlaka tasarımcı arkadaşımızla paylaşmanız gerekmektedir. Aksi halde genişlik 80 cm, yükseklik 192 cm boşluk bırakılacaktır. Alt dolaplarımızın derinlikleri kapak dahil 60 cm olduğundan, Buzdolabınızın derinliği 60 cm dolapların içinde kalacaktır. Geriye kalan kısmı öne doğru çıkmaktadır. Hiç dışarıda kalmamasını isterseniz, bütün alt dolapların derinlikleri standartlıktan çıkar, buzdolabı derinliğine göre özel yapılması gerekir. Bunun bedelini ödeyerek, tasarım yapıldığında belirtmeniz gerekmektedir.</span></p></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table><tbody><tr><td style=\"vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 8 - BANKA BİLGİLERİ</strong></span></td></tr></tbody></table></figure><p>{IBAN}</p><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 9 – İHTİLAFLARININ HALLİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İşbu sözleşme ve ekleri &nbsp; {sozlesme_tarihi} &nbsp; tarihinde taraflarca her sayfası paraflanıp son sayfası imzalandıktan sonra 2 nüsha olarak hazırlanmış olup bu sözleşmeden doğacak ihtilaflarının hallinde Türkiye Cumhuriyeti Mahkemeleri ve İcra Daireleri yetkilidir.</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><h4><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:14px;\"><strong>MADDE 10 - GARANTİ SÜRESİ</strong></span></h4><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Montaj tarihinden itibaren ürettiğimiz dolaplar imalat hatalarına karşı 2 yıl firma garantisi kapsamındadır.</span></p></td></tr></tbody></table></figure><figure class=\"table\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:10.72%;\"><col style=\"width:3.65%;\"><col style=\"width:37.63%;\"><col style=\"width:4.1%;\"><col style=\"width:3.65%;\"><col style=\"width:40.25%;\"></colgroup><tbody><tr><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SATICI</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;vertical-align:top;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td><td style=\"padding:0px;text-align:justify;vertical-align:top;\" colspan=\"3\" rowspan=\"3\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>Bu sözleşmeyi okudum ve anladım. Yukarıda belirtilen ürün bilgileri ve ürünlere ait teknik terimleri sordum ve anlamını öğrendim. Sözleşmede yazılı her maddeyi kabul ediyorum ve itirazım olmayacaktır.</strong></span></td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ŞUBE</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_sube}</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ALICI</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{MusteriAdi}</span></td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{SatisPersonel}</span></td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">KAŞE, İMZA</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İMZA</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\">&nbsp;</td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td></tr></tbody></table></figure>',1,'2023-03-03 13:59:04',1,'2025-01-07 13:39:24',1),(3,'Dekont','<p>{firma_logo}</p><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:68.13%;\"><col style=\"width:31.87%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" rowspan=\"4\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:30px;\"><span style=\"text-align:center;\"><strong>PARA MAKBUZU</strong></span></span></td><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME TARİHİ : {sozlesme_tarihi}</span></td></tr><tr><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TAHSİLAT TARİHİ : {tahsilat_tarihi}</span></td></tr><tr><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME NO : {sozlesme_no}</span></td></tr><tr><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TAHSİLAT NO : {tahsilat_no}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:20.06%;\"><col style=\"width:2.54%;\"><col style=\"width:77.4%;\"></colgroup><tbody><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">FİRMA ÜNVANI&nbsp;</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TELEFON</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADRES</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Sayın &nbsp; {MusteriAdi} &nbsp; &nbsp;\'den yalnız &nbsp;<strong><u>{NetTutar}</u></strong> &nbsp;teslim aldım, alınan tutar “ <strong>{OdemeTipi}</strong> ” ödeme şeklindedir.</span></p><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Cek_Bilgileri}</span></p><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{OdemeTablosu}</span></p></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:10.72%;\"><col style=\"width:3.65%;\"><col style=\"width:85.63%;\"></colgroup><tbody><tr><td colspan=\"2\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">PARAYI ALANIN&nbsp;</span></td><td>&nbsp;</td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADI SOYADI</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{SatisPersonel}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ŞUBE</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_sube}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">KAŞE, İMZA</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\">&nbsp;</td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td></tr></tbody></table></figure>',1,'2023-10-23 09:30:32',1,'2025-11-11 20:16:38',1),(4,'Yedek_Dekont','<p>{firma_logo}</p><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:68.13%;\"><col style=\"width:31.87%;\"></colgroup><tbody><tr><td style=\"padding:0px;\" rowspan=\"4\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:30px;\"><span style=\"text-align:center;\"><strong>PARA MAKBUZU</strong></span></span></td><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME TARİHİ : {sozlesme_tarihi}</span></td></tr><tr><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TAHSİLAT TARİHİ : {tahsilat_tarihi}</span></td></tr><tr><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">SÖZLEŞME NO : {sozlesme_no}</span></td></tr><tr><td style=\"padding:0px;text-align:right;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TAHSİLAT NO : {tahsilat_no}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:20.06%;\"><col style=\"width:2.54%;\"><col style=\"width:77.4%;\"></colgroup><tbody><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">FİRMA ÜNVANI&nbsp;</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TELEFON</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADRES</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr></tbody></table></figure><figure class=\"table\"><table><tbody><tr><td style=\"padding:0px;text-align:justify;vertical-align:top;\"><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">Sayın &nbsp; {MusteriAdi} &nbsp; &nbsp;\'den yalnız &nbsp;<strong><u>{NetTutar}</u></strong> &nbsp;teslim aldım, alınan tutar “ <strong>{OdemeTipi}</strong> ” ödeme şeklindedir.</span></p><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{Cek_Bilgileri}</span></p><p><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{OdemeTablosu}</span></p></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:10.72%;\"><col style=\"width:3.65%;\"><col style=\"width:85.63%;\"></colgroup><tbody><tr><td colspan=\"2\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">PARAYI ALANIN&nbsp;</span></td><td>&nbsp;</td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADI SOYADI</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{SatisPersonel}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ŞUBE</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_sube}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">KAŞE, İMZA</span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\">&nbsp;</td></tr><tr><td style=\"padding:0px;\">&nbsp;</td><td style=\"padding:0px;width:2.1%;\">&nbsp;</td><td style=\"padding:0px;\">&nbsp;</td></tr></tbody></table></figure>',1,'2023-10-23 09:30:32',1,'2025-11-11 20:16:46',1),(5,'Cek_Dekont','<p>{firma_logo}</p><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:20.06%;\"><col style=\"width:2.54%;\"><col style=\"width:77.4%;\"></colgroup><tbody><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">FİRMA ÜNVANI&nbsp;</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TELEFON</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADRES</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İŞLEM YAPAN</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{username}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"float:left;width:95.31%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:14.26%;\"><col style=\"width:3.29%;\"><col style=\"width:49.99%;\"><col style=\"width:3.01%;\"><col style=\"width:2.28%;\"><col style=\"width:27.17%;\"></colgroup><tbody><tr><td>Nakit Ödeme</td><td><p style=\"text-align:center;\">:</p></td><td>{nakit_odeme}</td><td>Tarih&nbsp;</td><td><p style=\"text-align:center;\">:</p></td><td>{islem_tarih}</td></tr><tr><td>Banka (Kart/Havale) Ödeme</td><td><p style=\"text-align:center;\">:</p></td><td>{banka_odeme}</td><td style=\"padding:0px;\" colspan=\"3\" rowspan=\"3\"><span style=\"font-size:22px;\"><strong>{AD} MAKBUZU</strong></span></td></tr><tr><td>Çekle Ödeme</td><td><p style=\"text-align:center;\">:</p></td><td>{cek_odeme}</td></tr><tr><td>Tahsil Edilen Toplam</td><td><p style=\"text-align:center;\">:</p></td><td>{toplam_odeme}</td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:100%;\"></colgroup><tbody><tr><td><p style=\"margin-left:40px;\">Yalnız : &nbsp;___________ {yazi_odeme} ____ ₺ tahsil edilmiştir.</p></td></tr></tbody></table></figure><figure class=\"table\"><table style=\"border-color:hsl(0, 0%, 0%);border-style:solid;\"><tbody><tr><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:400px;\"><p style=\"text-align:center;\">Banka</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:400px;\"><p style=\"text-align:center;\">Şube</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:200px;\"><p style=\"text-align:center;\">&nbsp;Çek No</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:100px;\"><p style=\"text-align:center;\">Hes No</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:200px;\"><p style=\"text-align:center;\">Tarih</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:400px;\"><p style=\"text-align:center;\">Tutar</p></td></tr><tr><td style=\"width:400px;\"><p style=\"text-align:center;\">{banka}</p></td><td style=\"width:400px;\"><p style=\"text-align:center;\">{sube}</p></td><td style=\"width:200px;\"><p style=\"text-align:center;\">{cekno}</p></td><td style=\"width:100px;\"><p style=\"text-align:center;\">&nbsp;</p></td><td style=\"width:200px;\"><p style=\"text-align:center;\">{vadetarih}</p></td><td style=\"width:400px;\"><p style=\"text-align:center;\">{NetTutar}</p></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:10.72%;\"><col style=\"width:3.65%;\"><col style=\"width:85.63%;\"></colgroup><tbody><tr><td colspan=\"2\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>PARAYI ALANIN&nbsp;</strong></span></td><td>&nbsp;</td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>ADI SOYADI</strong></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><strong>:</strong></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">&nbsp;{MusteriAdi}&nbsp;</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>AÇIKLAMA</strong></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>:</strong></span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">&nbsp;{Aciklama}&nbsp;</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>KAŞE, İMZA</strong></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>:</strong></span></td><td style=\"height:60px;padding:0px;\">&nbsp;</td></tr></tbody></table></figure><p>Bu makbuz elektronik ortamda oluşturulmuştur ve geçerlidir.</p>',1,'2025-10-12 20:11:48',NULL,NULL,1),(6,'YedekCek_Dekont','<p>{firma_logo}</p><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:20.06%;\"><col style=\"width:2.54%;\"><col style=\"width:77.4%;\"></colgroup><tbody><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">FİRMA ÜNVANI&nbsp;</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">:</span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adi}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">TELEFON</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_telefon}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">ADRES</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{firma_adres}</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">İŞLEM YAPAN</span></td><td style=\"padding:0px;text-align:center;width:2.1%;\">:</td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">{username}</span></td></tr></tbody></table></figure><figure class=\"table\" style=\"float:left;width:95.31%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:14.26%;\"><col style=\"width:3.29%;\"><col style=\"width:49.99%;\"><col style=\"width:3.01%;\"><col style=\"width:2.28%;\"><col style=\"width:27.17%;\"></colgroup><tbody><tr><td>Nakit Ödeme</td><td><p style=\"text-align:center;\">:</p></td><td>{nakit_odeme}</td><td>Tarih&nbsp;</td><td><p style=\"text-align:center;\">:</p></td><td>{islem_tarih}</td></tr><tr><td>Banka (Kart/Havale) Ödeme</td><td><p style=\"text-align:center;\">:</p></td><td>{banka_odeme}</td><td style=\"padding:0px;\" colspan=\"3\" rowspan=\"3\"><span style=\"font-size:22px;\"><strong>{AD} MAKBUZU</strong></span></td></tr><tr><td>Çekle Ödeme</td><td><p style=\"text-align:center;\">:</p></td><td>{cek_odeme}</td></tr><tr><td>Tahsil Edilen Toplam</td><td><p style=\"text-align:center;\">:</p></td><td>{toplam_odeme}</td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:100%;\"></colgroup><tbody><tr><td><p style=\"margin-left:40px;\">Yalnız : &nbsp;___________ {yazi_odeme} ____ ₺ tahsil edilmiştir.</p></td></tr></tbody></table></figure><figure class=\"table\"><table style=\"border-color:hsl(0, 0%, 0%);border-style:solid;\"><tbody><tr><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:400px;\"><p style=\"text-align:center;\">Banka</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:400px;\"><p style=\"text-align:center;\">Şube</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:200px;\"><p style=\"text-align:center;\">&nbsp;Çek No</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:100px;\"><p style=\"text-align:center;\">Hes No</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:200px;\"><p style=\"text-align:center;\">Tarih</p></td><td style=\"background-color:hsl(0, 0%, 60%);text-align:center;width:400px;\"><p style=\"text-align:center;\">Tutar</p></td></tr><tr><td style=\"width:400px;\"><p style=\"text-align:center;\">{banka}</p></td><td style=\"width:400px;\"><p style=\"text-align:center;\">{sube}</p></td><td style=\"width:200px;\"><p style=\"text-align:center;\">{cekno}</p></td><td style=\"width:100px;\"><p style=\"text-align:center;\">&nbsp;</p></td><td style=\"width:200px;\"><p style=\"text-align:center;\">{vadetarih}</p></td><td style=\"width:400px;\"><p style=\"text-align:center;\">{NetTutar}</p></td></tr></tbody></table></figure><figure class=\"table\" style=\"width:100%;\"><table class=\"ck-table-resized\"><colgroup><col style=\"width:10.72%;\"><col style=\"width:3.65%;\"><col style=\"width:85.63%;\"></colgroup><tbody><tr><td colspan=\"2\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>PARAYI ALANIN&nbsp;</strong></span></td><td>&nbsp;</td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>ADI SOYADI</strong></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><strong>:</strong></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">&nbsp;{MusteriAdi}&nbsp;</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>AÇIKLAMA</strong></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>:</strong></span></td><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\">&nbsp;{Aciklama}&nbsp;</span></td></tr><tr><td style=\"padding:0px;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>KAŞE, İMZA</strong></span></td><td style=\"padding:0px;text-align:center;vertical-align:top;width:2.1%;\"><span style=\"font-family:Calibri, Geneva, sans-serif;font-size:12px;\"><strong>:</strong></span></td><td style=\"height:60px;padding:0px;\">&nbsp;</td></tr></tbody></table></figure><p>Bu makbuz elektronik ortamda oluşturulmuştur ve geçerlidir.</p>',1,'2025-10-12 20:11:53',NULL,NULL,1);

--
-- Table structure for table `sozlesmeler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sozlesmeler` (
  `ContractID` int(11) NOT NULL AUTO_INCREMENT,
  `AppUser` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractDate` date DEFAULT NULL,
  `AppDate` date DEFAULT NULL,
  `DolapBoy` varchar(255) DEFAULT NULL,
  `GovdeCinsi` varchar(255) DEFAULT NULL,
  `GovdeRenk` varchar(255) DEFAULT NULL,
  `KapakCinsi` varchar(255) DEFAULT NULL,
  `AltKapakRengi` varchar(255) DEFAULT NULL,
  `UstKapakRengi` varchar(255) DEFAULT NULL,
  `BoyKapakRengi` varchar(255) DEFAULT NULL,
  `CamKapakModel` varchar(255) DEFAULT NULL,
  `CamRengi` varchar(255) DEFAULT NULL,
  `AynaRengi` varchar(255) DEFAULT NULL,
  `IsikBandi` varchar(255) DEFAULT NULL,
  `TacBandi` varchar(255) DEFAULT NULL,
  `Baza` varchar(255) DEFAULT NULL,
  `Kulp` varchar(255) DEFAULT NULL,
  `Cekmece` varchar(255) DEFAULT NULL,
  `Mentese` varchar(255) DEFAULT NULL,
  `KalkarKapak` varchar(255) DEFAULT NULL,
  `Spotluk` varchar(255) DEFAULT NULL,
  `Yanlar` varchar(255) DEFAULT NULL,
  `MutfakToplam` varchar(255) DEFAULT NULL,
  `Evye` varchar(255) DEFAULT NULL,
  `EvyeToplam` varchar(255) DEFAULT NULL,
  `Aksesuar` varchar(255) DEFAULT NULL,
  `AksesuarToplam` varchar(255) DEFAULT NULL,
  `Tezgah` varchar(255) DEFAULT NULL,
  `TezgahToplam` varchar(255) DEFAULT NULL,
  `BanyoDolap` text DEFAULT NULL,
  `BanyoToplam` varchar(255) DEFAULT NULL,
  `DigerDolap` text DEFAULT NULL,
  `DolapToplam` varchar(255) DEFAULT NULL,
  `Ankastre` varchar(255) DEFAULT NULL,
  `AnkastreToplam` varchar(255) DEFAULT NULL,
  `GenelToplam` varchar(255) DEFAULT NULL,
  `Iskonto` varchar(255) DEFAULT NULL,
  `NetTutar` varchar(255) DEFAULT NULL,
  `TaksitSayisi` int(11) DEFAULT NULL,
  `ContractDesc` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  `IsPrint` int(11) NOT NULL DEFAULT 0,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ContractID`) USING BTREE,
  KEY `idx_sozlesmeler_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilerle imzalanan resmi satış sözleşmeleri.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sozlesmeler`
--


--
-- Table structure for table `stok_hareketleri`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_hareketleri` (
  `HareketID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `FirmaID` int(10) unsigned NOT NULL,
  `StokID` int(10) unsigned NOT NULL,
  `HareketTip` tinyint(4) NOT NULL COMMENT '1: GIRIS, 2: CIKIS',
  `Miktar` decimal(12,3) NOT NULL,
  `OncekiMiktar` decimal(12,3) NOT NULL DEFAULT 0.000,
  `SonrakiMiktar` decimal(12,3) NOT NULL DEFAULT 0.000,
  `Aciklama` varchar(255) DEFAULT NULL,
  `UserID` int(10) unsigned DEFAULT NULL,
  `CustomerID` int(10) unsigned DEFAULT NULL,
  `ContractID` int(10) unsigned DEFAULT NULL,
  `ProjectNo` varchar(100) DEFAULT NULL,
  `HareketTarih` datetime NOT NULL DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`HareketID`),
  KEY `idx_firma_stok_tarih` (`FirmaID`,`StokID`,`HareketTarih`),
  KEY `idx_firma_tarih` (`FirmaID`,`HareketTarih`),
  KEY `fk_stok_hareket_stok` (`StokID`),
  KEY `idx_hareket_customer` (`CustomerID`),
  KEY `idx_hareket_contract` (`ContractID`),
  KEY `idx_stok_hareketleri_UserID` (`UserID`),
  KEY `idx_opt_stok_tarih_del` (`HareketTarih`,`del_status`),
  KEY `idx_opt_stok_customer_del` (`CustomerID`,`del_status`),
  CONSTRAINT `fk_stok_hareket_stok` FOREIGN KEY (`StokID`) REFERENCES `stok_kalemleri` (`StokID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürünlerin depoya giriş ve çıkış işlemlerinin logları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareketleri`
--


--
-- Table structure for table `stok_kalemleri`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_kalemleri` (
  `StokID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FirmaID` int(10) unsigned NOT NULL,
  `StokKod` varchar(64) NOT NULL,
  `StokAd` varchar(255) NOT NULL,
  `Birim` varchar(20) NOT NULL DEFAULT 'ADET',
  `Fiyat` decimal(12,2) NOT NULL DEFAULT 0.00,
  `MevcutMiktar` decimal(12,3) NOT NULL DEFAULT 0.000,
  `StokDurum` tinyint(4) NOT NULL DEFAULT 1,
  `Grup` varchar(255) DEFAULT NULL,
  `Grubu` varchar(255) DEFAULT NULL,
  `MinMiktar` decimal(12,3) NOT NULL DEFAULT 5.000,
  `Aciklama` varchar(255) DEFAULT NULL,
  `CreatedBy` int(10) unsigned DEFAULT NULL,
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedDate` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`StokID`),
  UNIQUE KEY `uk_firma_stokkod` (`FirmaID`,`StokKod`),
  KEY `idx_firma_durum` (`FirmaID`,`StokDurum`)
) ENGINE=InnoDB AUTO_INCREMENT=2295 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fatura veya irsaliyelerdeki detay stok hareket kalemleri.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_kalemleri`
--


--
-- Table structure for table `subeler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subeler` (
  `BranchID` int(11) NOT NULL AUTO_INCREMENT,
  `FirmaID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `BranchName` varchar(255) DEFAULT NULL,
  `BranchResmi` varchar(255) DEFAULT NULL,
  `BranchShort` varchar(255) DEFAULT NULL,
  `BranchArea` varchar(255) DEFAULT NULL,
  `BranchDesc` varchar(255) DEFAULT NULL,
  `BranchPhone` varchar(255) DEFAULT NULL,
  `BranchAuthorized` varchar(255) DEFAULT NULL,
  `BranchStatus` tinyint(4) DEFAULT 1,
  `BranchMail` varchar(255) DEFAULT NULL,
  `BranchAdres` varchar(255) DEFAULT NULL,
  `BranchLogo` varchar(255) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`BranchID`) USING BTREE,
  KEY `idx_subeler_branchid_firmaid` (`BranchID`,`FirmaID`),
  KEY `idx_subeler_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Firmalara bağlı şubelerin tanımları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subeler`
--


--
-- Table structure for table `tahsilat`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahsilat` (
  `TahsilatID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `BankaID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) NOT NULL DEFAULT 1,
  `OdemeTarihi` date DEFAULT NULL,
  `BirlestirmeID` int(11) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`TahsilatID`),
  KEY `idx_tahsilat_installmentid` (`InstallmentID`),
  KEY `idx_tahsilat_UserID` (`UserID`),
  KEY `idx_opt_tahsilat_customer` (`CustomerID`,`del_status`),
  KEY `idx_opt_tahsilat_contract` (`ContractID`,`del_status`),
  KEY `idx_opt_tahsilat_tarih` (`OdemeTarihi`,`del_status`),
  KEY `idx_opt_tahsilat_banka` (`BankaID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilerden alınan genel tahsilat ana kayıtları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilat`
--


--
-- Table structure for table `tahsilatcek`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahsilatcek` (
  `CekTahsilatID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` varchar(255) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `AdminDurum` int(11) DEFAULT 1,
  `BankaID` int(11) DEFAULT NULL,
  `BankaSubeID` int(11) DEFAULT NULL,
  `CekSahibi` varchar(255) DEFAULT NULL,
  `CekNo` varchar(255) DEFAULT NULL,
  `CekVade` date DEFAULT NULL,
  `TakasBankaID` int(11) DEFAULT NULL,
  `KasaDesc` varchar(255) DEFAULT NULL,
  `CiroDesk` varchar(255) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL,
  `BirlestirmeID` int(11) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`CekTahsilatID`),
  KEY `idx_tahsilatcek_UserID` (`UserID`),
  KEY `idx_tahsilatcek_CustomerID` (`CustomerID`),
  KEY `idx_opt_tahsilatcek_customer_del` (`CustomerID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Çek/Senet ile yapılan tahsilatların detayları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilatcek`
--


--
-- Table structure for table `tahsilathavale`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahsilathavale` (
  `HavaleTahsilatID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `BankaID` int(11) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`HavaleTahsilatID`),
  KEY `idx_tahsilathavale_installmentid_tutar` (`InstallmentID`,`AlinanTutar`),
  KEY `idx_tahsilathavale_UserID` (`UserID`),
  KEY `idx_tahsilathavale_CustomerID` (`CustomerID`),
  KEY `idx_opt_tahsilathavale_customer_del` (`CustomerID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Banka Havalesi/EFT ile yapılan tahsilatların detayları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilathavale`
--


--
-- Table structure for table `tahsilatkart`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahsilatkart` (
  `KartTahsilatID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `BankaID` int(11) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`KartTahsilatID`),
  KEY `idx_tahsilatkart_installmentid_tutar` (`InstallmentID`,`AlinanTutar`),
  KEY `idx_tahsilatkart_UserID` (`UserID`),
  KEY `idx_tahsilatkart_CustomerID` (`CustomerID`),
  KEY `idx_opt_tahsilatkart_customer_del` (`CustomerID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kredi kartı/POS ile yapılan tahsilatların detayları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilatkart`
--


--
-- Table structure for table `tahsilatnakit`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahsilatnakit` (
  `NakitTahsilatID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `OdemeTarihi` date DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`NakitTahsilatID`),
  KEY `idx_tahsilatnakit_UserID` (`UserID`),
  KEY `idx_tahsilatnakit_CustomerID` (`CustomerID`),
  KEY `idx_opt_tahsilatnakit_customer_del` (`CustomerID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nakit olarak yapılan tahsilatların detayları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilatnakit`
--


--
-- Table structure for table `taksitler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taksitler` (
  `InstallmentID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) DEFAULT NULL,
  `UserID` int(11) NOT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `TaksitSayisi` int(11) DEFAULT NULL,
  `TaksitAdet` int(11) DEFAULT NULL,
  `TaksitTutar` double(10,2) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `TaksitTarih` date DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`InstallmentID`),
  KEY `idx_taksitler_CustomerID` (`CustomerID`),
  KEY `idx_taksitler_UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sözleşme veya faturalara bağlı oluşturulan ödeme taksit planları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksitler`
--


--
-- Table structure for table `ticarifatura`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticarifatura` (
  `TicariFaturaID` int(11) NOT NULL AUTO_INCREMENT,
  `FaturaNo` varchar(255) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `FirmaID` int(11) DEFAULT NULL,
  `FaturaTip` int(11) DEFAULT NULL,
  `FaturaTutar` double(10,2) DEFAULT NULL,
  `FaturaTarih` date DEFAULT NULL,
  `FaturaDosya` varchar(255) DEFAULT NULL,
  `FaturaDesc` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`TicariFaturaID`),
  KEY `idx_ticarifatura_CustomerID` (`CustomerID`),
  KEY `idx_ticarifatura_UserID` (`UserID`),
  KEY `idx_ticarifatura_FirmaID` (`FirmaID`),
  KEY `idx_opt_ticarifatura_tarih` (`FaturaTarih`,`del_status`),
  KEY `idx_opt_ticarifatura_customer` (`CustomerID`,`del_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Resmi/Ticari fatura işlemleri ve numaralarının takibi.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticarifatura`
--


--
-- Table structure for table `urungrup`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urungrup` (
  `UrunGrupID` int(11) NOT NULL AUTO_INCREMENT,
  `UrunGrupName` varchar(255) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`UrunGrupID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürünlerin sınıflandırıldığı kategori ve gruplar.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urungrup`
--

INSERT INTO `urungrup` VALUES (1,'Mutfak Dolapları',1),(2,'Aksesuar',1),(3,'Tezgah',1),(4,'Banyo Dolabı',1),(5,'Diğer Dolaplar',1),(6,'Ankastre Ürünler',1),(7,'İlave Ürünler',1),(8,'Eksik Ürünler',1);

--
-- Table structure for table `urunler`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urunler` (
  `ProductID` int(11) NOT NULL AUTO_INCREMENT,
  `ProductName` varchar(255) DEFAULT NULL,
  `ProductDesc` varchar(255) DEFAULT NULL,
  `ProductStatus` tinyint(4) DEFAULT 1,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ProductID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemde satışa veya üretime sunulan ürünlerin ana kartları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

INSERT INTO `urunler` VALUES (1,'Mutfak','Mutfak Malzemeleri',1,1),(2,'Banyo','Banyo Malzemeleri',1,1),(3,'Portmanto','Portmanto Ürünleri',1,1),(4,'Vestiyer','Vestiyer Ürünleri',1,1),(5,'Giyinme Dolabı','Giyinme Dolap Ürünleri',1,1),(6,'TV Ünitesi','TV Ünite Grupları',1,1),(7,'Mutfak (İlave)','Ekstra Mutfak Malzemeleri',1,1),(8,'Diğer Ürünler','Listede Olmayan Ürünler',1,1),(9,'Ankastre','Ankastre Ürünler',1,1);

--
-- Table structure for table `usergrup`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usergrup` (
  `UserGrupID` int(11) NOT NULL AUTO_INCREMENT,
  `UserGrupName` varchar(255) DEFAULT NULL,
  `UserGrupDesc` varchar(255) DEFAULT NULL,
  `UserGrupStatus` tinyint(4) NOT NULL DEFAULT 1,
  `permissions` longtext DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`UserGrupID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kullanıcı yetki rolleri (Admin, Satış, Montaj vb.) ve izin grupları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usergrup`
--

INSERT INTO `usergrup` VALUES (1,'Admin','Yetkili Personel',1,NULL,1),(2,'Satış Elemanı','Satış Personeli',1,NULL,1),(3,'Depo Elemanı','Depo Personeli',1,NULL,1),(4,'Montaj Elemanı','Montaj Ustası',1,NULL,1),(5,'Genel Kontrol','Genel Kontrol',1,NULL,1),(6,'Muhasebe Departmanı','Muhasebe Departmanı',1,NULL,1),(7,'Planlama','Fabrika Ekibi',1,NULL,1),(8,'İmalat','Fabrika Ekibi',1,NULL,1);

--
-- Table structure for table `users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `FirmaID` int(11) NOT NULL,
  `UserName` varchar(200) NOT NULL,
  `UserMail` varchar(250) NOT NULL,
  `UserPhone` varchar(255) NOT NULL,
  `UserPass` varchar(250) NOT NULL,
  `UserAuthority` tinyint(4) NOT NULL DEFAULT 1,
  `UserGroupID` tinyint(4) DEFAULT NULL,
  `BranchID` tinyint(4) DEFAULT NULL,
  `UserStatus` tinyint(4) DEFAULT 1,
  `UserPicture` varchar(255) DEFAULT 'dist/img/avatar5.png',
  `SessionMail` varchar(500) DEFAULT NULL,
  `IpAdress` varchar(255) DEFAULT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`UserID`) USING BTREE,
  UNIQUE KEY `kul_mail` (`UserMail`) USING BTREE,
  KEY `idx_users_userid_branchid` (`UserID`,`BranchID`),
  KEY `idx_users_FirmaID` (`FirmaID`),
  KEY `idx_users_UserGroupID` (`UserGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sisteme giriş yetkisi olan tüm kullanıcıların (personel) hesapları.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--


--
-- Table structure for table `virmanhareket`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virmanhareket` (
  `VirmanID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `SendBank` int(11) DEFAULT NULL,
  `ReceiveBank` int(11) DEFAULT NULL,
  `Amount` double DEFAULT NULL,
  `VirmanDate` datetime DEFAULT current_timestamp(),
  `del_status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`VirmanID`),
  KEY `idx_virmanhareket_UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kasalar ve bankalar arası yapılan para transferi (virman) işlemleri.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virmanhareket`
--


--
-- Temporary table structure for view `vw_recent_logs`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_recent_logs` AS SELECT
 1 AS `LogID`,
  1 AS `LogType`,
  1 AS `LogTypeName`,
  1 AS `LogDesc`,
  1 AS `LogStatus`,
  1 AS `UserID`,
  1 AS `UserName`,
  1 AS `LogDate`,
  1 AS `IpAddress`,
  1 AS `EntityType`,
  1 AS `EntityID` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `vw_today_summary`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_today_summary` AS SELECT
 1 AS `LogType`,
  1 AS `LogStatus`,
  1 AS `count`,
  1 AS `log_date` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_recent_logs`
--

/*!50001 DROP VIEW IF EXISTS `vw_recent_logs`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_recent_logs` AS select `l`.`LogID` AS `LogID`,`l`.`LogType` AS `LogType`,`lt`.`names` AS `LogTypeName`,`l`.`LogDesc` AS `LogDesc`,`l`.`LogStatus` AS `LogStatus`,`u`.`UserID` AS `UserID`,`u`.`UserName` AS `UserName`,`l`.`LogDate` AS `LogDate`,`l`.`IpAddress` AS `IpAddress`,`l`.`EntityType` AS `EntityType`,`l`.`EntityID` AS `EntityID` from ((`lognew` `l` left join `users` `u` on(`l`.`UserID` = `u`.`UserID`)) left join `logtypesnew` `lt` on(`l`.`LogType` = `lt`.`id`)) order by `l`.`LogDate` desc limit 0,1000 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_today_summary`
--

/*!50001 DROP VIEW IF EXISTS `vw_today_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_today_summary` AS select `lognew`.`LogType` AS `LogType`,`lognew`.`LogStatus` AS `LogStatus`,count(0) AS `count`,cast(`lognew`.`LogDate` as date) AS `log_date` from `lognew` where cast(`lognew`.`LogDate` as date) = curdate() group by `lognew`.`LogType`,`lognew`.`LogStatus` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-30  0:14:23
