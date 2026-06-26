-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: prolyn_k2
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admincek`
--

DROP TABLE IF EXISTS `admincek`;
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
  PRIMARY KEY (`CekID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admincek`
--

LOCK TABLES `admincek` WRITE;
/*!40000 ALTER TABLE `admincek` DISABLE KEYS */;
/*!40000 ALTER TABLE `admincek` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adminkasa`
--

DROP TABLE IF EXISTS `adminkasa`;
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
  PRIMARY KEY (`AdminKasaID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adminkasa`
--

LOCK TABLES `adminkasa` WRITE;
/*!40000 ALTER TABLE `adminkasa` DISABLE KEYS */;
/*!40000 ALTER TABLE `adminkasa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ayarlar`
--

DROP TABLE IF EXISTS `ayarlar`;
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
  PRIMARY KEY (`MetaID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ayarlar`
--

LOCK TABLES `ayarlar` WRITE;
/*!40000 ALTER TABLE `ayarlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `ayarlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bankalar`
--

DROP TABLE IF EXISTS `bankalar`;
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
  PRIMARY KEY (`BankID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bankalar`
--

LOCK TABLES `bankalar` WRITE;
/*!40000 ALTER TABLE `bankalar` DISABLE KEYS */;
/*!40000 ALTER TABLE `bankalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cek_birlestirme`
--

DROP TABLE IF EXISTS `cek_birlestirme`;
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
  PRIMARY KEY (`BirlestirmeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cek_birlestirme`
--

LOCK TABLES `cek_birlestirme` WRITE;
/*!40000 ALTER TABLE `cek_birlestirme` DISABLE KEYS */;
/*!40000 ALTER TABLE `cek_birlestirme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dolapboy`
--

DROP TABLE IF EXISTS `dolapboy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dolapboy` (
  `DolapBoyID` int(11) NOT NULL AUTO_INCREMENT,
  `DolapBoyName` varchar(255) DEFAULT NULL,
  `bDolapStandart` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`DolapBoyID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dolapboy`
--

LOCK TABLES `dolapboy` WRITE;
/*!40000 ALTER TABLE `dolapboy` DISABLE KEYS */;
/*!40000 ALTER TABLE `dolapboy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dosyalar`
--

DROP TABLE IF EXISTS `dosyalar`;
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
  PRIMARY KEY (`FileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dosyalar`
--

LOCK TABLES `dosyalar` WRITE;
/*!40000 ALTER TABLE `dosyalar` DISABLE KEYS */;
/*!40000 ALTER TABLE `dosyalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `duyurular`
--

DROP TABLE IF EXISTS `duyurular`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `duyurular` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baslik` varchar(255) NOT NULL,
  `icerik` text NOT NULL,
  `sayfa` varchar(255) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `duyurular`
--

LOCK TABLES `duyurular` WRITE;
/*!40000 ALTER TABLE `duyurular` DISABLE KEYS */;
/*!40000 ALTER TABLE `duyurular` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edosyalar`
--

DROP TABLE IF EXISTS `edosyalar`;
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
  PRIMARY KEY (`NonFileID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edosyalar`
--

LOCK TABLES `edosyalar` WRITE;
/*!40000 ALTER TABLE `edosyalar` DISABLE KEYS */;
/*!40000 ALTER TABLE `edosyalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faturafile`
--

DROP TABLE IF EXISTS `faturafile`;
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
  PRIMARY KEY (`FaturaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faturafile`
--

LOCK TABLES `faturafile` WRITE;
/*!40000 ALTER TABLE `faturafile` DISABLE KEYS */;
/*!40000 ALTER TABLE `faturafile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faturalar`
--

DROP TABLE IF EXISTS `faturalar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faturalar` (
  `PrintID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `CompanyVNo` varchar(255) DEFAULT NULL,
  `CompanyVD` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`PrintID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faturalar`
--

LOCK TABLES `faturalar` WRITE;
/*!40000 ALTER TABLE `faturalar` DISABLE KEYS */;
/*!40000 ALTER TABLE `faturalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firmalar`
--

DROP TABLE IF EXISTS `firmalar`;
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
  PRIMARY KEY (`FirmaID`),
  KEY `idx_firmalar_firmaid` (`FirmaID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firmalar`
--

LOCK TABLES `firmalar` WRITE;
/*!40000 ALTER TABLE `firmalar` DISABLE KEYS */;
INSERT INTO `firmalar` VALUES (1,9,'Başlangıç A.Ş.','Ana Firma',NULL,'Belirtilmedi','0000000000','İstanbul','Şişli','Adres');
/*!40000 ALTER TABLE `firmalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `govdecinsi`
--

DROP TABLE IF EXISTS `govdecinsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `govdecinsi` (
  `GovdeCinsiID` int(11) NOT NULL AUTO_INCREMENT,
  `GovdeCinsiName` varchar(255) DEFAULT NULL,
  `bGovdeStandart` tinyint(4) NOT NULL,
  PRIMARY KEY (`GovdeCinsiID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `govdecinsi`
--

LOCK TABLES `govdecinsi` WRITE;
/*!40000 ALTER TABLE `govdecinsi` DISABLE KEYS */;
/*!40000 ALTER TABLE `govdecinsi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `govderenk`
--

DROP TABLE IF EXISTS `govderenk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `govderenk` (
  `GovdeRenkID` int(11) NOT NULL AUTO_INCREMENT,
  `GovdeRenkName` varchar(255) DEFAULT NULL,
  `bGRenkStandart` tinyint(4) NOT NULL,
  PRIMARY KEY (`GovdeRenkID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `govderenk`
--

LOCK TABLES `govderenk` WRITE;
/*!40000 ALTER TABLE `govderenk` DISABLE KEYS */;
/*!40000 ALTER TABLE `govderenk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ilceler`
--

DROP TABLE IF EXISTS `ilceler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ilceler` (
  `ilce_id` int(11) NOT NULL AUTO_INCREMENT,
  `ilceadi` varchar(255) NOT NULL,
  `iller_id` int(11) NOT NULL,
  PRIMARY KEY (`ilce_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ilceler`
--

LOCK TABLES `ilceler` WRITE;
/*!40000 ALTER TABLE `ilceler` DISABLE KEYS */;
/*!40000 ALTER TABLE `ilceler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iller`
--

DROP TABLE IF EXISTS `iller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iller` (
  `iller_id` int(11) NOT NULL AUTO_INCREMENT,
  `sehiradi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`iller_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iller`
--

LOCK TABLES `iller` WRITE;
/*!40000 ALTER TABLE `iller` DISABLE KEYS */;
/*!40000 ALTER TABLE `iller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kapakcinsi`
--

DROP TABLE IF EXISTS `kapakcinsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kapakcinsi` (
  `KapakCinsiID` int(11) NOT NULL AUTO_INCREMENT,
  `KapakCinsiName` varchar(255) DEFAULT NULL,
  `bKapakStandart` tinyint(4) NOT NULL,
  PRIMARY KEY (`KapakCinsiID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kapakcinsi`
--

LOCK TABLES `kapakcinsi` WRITE;
/*!40000 ALTER TABLE `kapakcinsi` DISABLE KEYS */;
/*!40000 ALTER TABLE `kapakcinsi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kasahareketleri`
--

DROP TABLE IF EXISTS `kasahareketleri`;
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
  PRIMARY KEY (`KasaHareketID`),
  KEY `idx_kasahareketleri_islemtipi` (`IslemTipi`),
  KEY `idx_kasahareketleri_firma_kasatip_islemtipi` (`KasaTip`,`IslemTipi`,`Detay`,`TransferTarih`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasahareketleri`
--

LOCK TABLES `kasahareketleri` WRITE;
/*!40000 ALTER TABLE `kasahareketleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `kasahareketleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kasatips`
--

DROP TABLE IF EXISTS `kasatips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kasatips` (
  `KasaTipsID` int(11) NOT NULL AUTO_INCREMENT,
  `KasaTip` tinyint(4) DEFAULT NULL,
  `KasaDetay` varchar(255) DEFAULT NULL,
  `CreadetDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`KasaTipsID`) USING BTREE,
  KEY `idx_kasatips_kasatipsid` (`KasaTipsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kasatips`
--

LOCK TABLES `kasatips` WRITE;
/*!40000 ALTER TABLE `kasatips` DISABLE KEYS */;
/*!40000 ALTER TABLE `kasatips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `LogID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `LogType` text DEFAULT NULL,
  `LogDesc` mediumtext DEFAULT NULL,
  `LogDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`LogID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lognew`
--

DROP TABLE IF EXISTS `lognew`;
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
  PRIMARY KEY (`LogID`),
  KEY `idx_user_id` (`UserID`),
  KEY `idx_log_type` (`LogType`),
  KEY `idx_log_status` (`LogStatus`),
  KEY `idx_entity` (`EntityType`,`EntityID`),
  KEY `idx_log_date` (`LogDate`),
  KEY `idx_ip_address` (`IpAddress`),
  KEY `idx_log_type_date` (`LogType`,`LogDate`),
  KEY `idx_user_date` (`UserID`,`LogDate`),
  FULLTEXT KEY `ft_log_desc` (`LogDesc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detaylı Audit Trail ve Logging Tablosu';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lognew`
--

LOCK TABLES `lognew` WRITE;
/*!40000 ALTER TABLE `lognew` DISABLE KEYS */;
INSERT INTO `lognew` VALUES (1,1,'Sisteme giriş yapıldı','success',1,'::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0','POST','/prolyn/islem.php',NULL,NULL,'users',1,NULL,'2026-06-28 17:01:17');
/*!40000 ALTER TABLE `lognew` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logtypes`
--

DROP TABLE IF EXISTS `logtypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `names` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logtypes`
--

LOCK TABLES `logtypes` WRITE;
/*!40000 ALTER TABLE `logtypes` DISABLE KEYS */;
/*!40000 ALTER TABLE `logtypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logtypesnew`
--

DROP TABLE IF EXISTS `logtypesnew`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logtypesnew` (
  `id` tinyint(3) unsigned NOT NULL,
  `names` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT 'primary',
  `icon` varchar(50) DEFAULT 'fas fa-info-circle',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log Tipi Kodları ve Tanımları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logtypesnew`
--

LOCK TABLES `logtypesnew` WRITE;
/*!40000 ALTER TABLE `logtypesnew` DISABLE KEYS */;
/*!40000 ALTER TABLE `logtypesnew` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `montaj_problemler`
--

DROP TABLE IF EXISTS `montaj_problemler`;
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
  PRIMARY KEY (`ProblemID`),
  KEY `idx_app_id` (`AppID`),
  KEY `idx_customer_id` (`CustomerID`),
  KEY `idx_problem_status` (`ProblemStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `montaj_problemler`
--

LOCK TABLES `montaj_problemler` WRITE;
/*!40000 ALTER TABLE `montaj_problemler` DISABLE KEYS */;
/*!40000 ALTER TABLE `montaj_problemler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `musteri_ziyaret`
--

DROP TABLE IF EXISTS `musteri_ziyaret`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `musteri_ziyaret` (
  `ZiyaretID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `AppID` int(11) NOT NULL,
  `ZiyaretCount` int(11) DEFAULT 1 COMMENT 'Bu randevuya kaç kez gidildi',
  `LastVisit` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ZiyaretID`),
  UNIQUE KEY `unique_customer_app` (`CustomerID`,`AppID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteri_ziyaret`
--

LOCK TABLES `musteri_ziyaret` WRITE;
/*!40000 ALTER TABLE `musteri_ziyaret` DISABLE KEYS */;
/*!40000 ALTER TABLE `musteri_ziyaret` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `musteriler`
--

DROP TABLE IF EXISTS `musteriler`;
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
  PRIMARY KEY (`CustomerID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musteriler`
--

LOCK TABLES `musteriler` WRITE;
/*!40000 ALTER TABLE `musteriler` DISABLE KEYS */;
/*!40000 ALTER TABLE `musteriler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nakitkasa`
--

DROP TABLE IF EXISTS `nakitkasa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nakitkasa` (
  `NakitKasaID` int(11) NOT NULL AUTO_INCREMENT,
  `FirmaID` int(11) NOT NULL,
  `NakitKasaTutar` double(10,2) NOT NULL,
  `UpdateDate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`NakitKasaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nakitkasa`
--

LOCK TABLES `nakitkasa` WRITE;
/*!40000 ALTER TABLE `nakitkasa` DISABLE KEYS */;
/*!40000 ALTER TABLE `nakitkasa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `odemeler`
--

DROP TABLE IF EXISTS `odemeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `odemeler` (
  `PayID` int(11) NOT NULL AUTO_INCREMENT,
  `ContractID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `OdemeAdet` int(11) DEFAULT NULL,
  `OdemeTutar` varchar(255) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL,
  PRIMARY KEY (`PayID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `odemeler`
--

LOCK TABLES `odemeler` WRITE;
/*!40000 ALTER TABLE `odemeler` DISABLE KEYS */;
/*!40000 ALTER TABLE `odemeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `randevular`
--

DROP TABLE IF EXISTS `randevular`;
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
  PRIMARY KEY (`AppID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `randevular`
--

LOCK TABLES `randevular` WRITE;
/*!40000 ALTER TABLE `randevular` DISABLE KEYS */;
/*!40000 ALTER TABLE `randevular` ENABLE KEYS */;
UNLOCK TABLES;
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

DROP TABLE IF EXISTS `silinenrandevular`;
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
  PRIMARY KEY (`AppID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `silinenrandevular`
--

LOCK TABLES `silinenrandevular` WRITE;
/*!40000 ALTER TABLE `silinenrandevular` DISABLE KEYS */;
/*!40000 ALTER TABLE `silinenrandevular` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sozlesme_sablonlari`
--

DROP TABLE IF EXISTS `sozlesme_sablonlari`;
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
  PRIMARY KEY (`SozlesmeID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sozlesme_sablonlari`
--

LOCK TABLES `sozlesme_sablonlari` WRITE;
/*!40000 ALTER TABLE `sozlesme_sablonlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `sozlesme_sablonlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sozlesmeler`
--

DROP TABLE IF EXISTS `sozlesmeler`;
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
  PRIMARY KEY (`ContractID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sozlesmeler`
--

LOCK TABLES `sozlesmeler` WRITE;
/*!40000 ALTER TABLE `sozlesmeler` DISABLE KEYS */;
/*!40000 ALTER TABLE `sozlesmeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_hareketleri`
--

DROP TABLE IF EXISTS `stok_hareketleri`;
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
  PRIMARY KEY (`HareketID`),
  KEY `idx_firma_stok_tarih` (`FirmaID`,`StokID`,`HareketTarih`),
  KEY `idx_firma_tarih` (`FirmaID`,`HareketTarih`),
  KEY `fk_stok_hareket_stok` (`StokID`),
  KEY `idx_hareket_customer` (`CustomerID`),
  KEY `idx_hareket_contract` (`ContractID`),
  CONSTRAINT `fk_stok_hareket_stok` FOREIGN KEY (`StokID`) REFERENCES `stok_kalemleri` (`StokID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_hareketleri`
--

LOCK TABLES `stok_hareketleri` WRITE;
/*!40000 ALTER TABLE `stok_hareketleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `stok_hareketleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_kalemleri`
--

DROP TABLE IF EXISTS `stok_kalemleri`;
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
  PRIMARY KEY (`StokID`),
  UNIQUE KEY `uk_firma_stokkod` (`FirmaID`,`StokKod`),
  KEY `idx_firma_durum` (`FirmaID`,`StokDurum`)
) ENGINE=InnoDB AUTO_INCREMENT=2295 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_kalemleri`
--

LOCK TABLES `stok_kalemleri` WRITE;
/*!40000 ALTER TABLE `stok_kalemleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `stok_kalemleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subeler`
--

DROP TABLE IF EXISTS `subeler`;
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
  PRIMARY KEY (`BranchID`) USING BTREE,
  KEY `idx_subeler_branchid_firmaid` (`BranchID`,`FirmaID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subeler`
--

LOCK TABLES `subeler` WRITE;
/*!40000 ALTER TABLE `subeler` DISABLE KEYS */;
INSERT INTO `subeler` VALUES (1,1,9,'Merkez Şube',NULL,'MRK',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `subeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahsilat`
--

DROP TABLE IF EXISTS `tahsilat`;
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
  PRIMARY KEY (`TahsilatID`),
  KEY `idx_tahsilat_installmentid` (`InstallmentID`),
  KEY `idx_tahsilat_tahsilatid_installmentid` (`TahsilatID`,`InstallmentID`,`CustomerID`,`AlinanTutar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilat`
--

LOCK TABLES `tahsilat` WRITE;
/*!40000 ALTER TABLE `tahsilat` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahsilat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahsilatcek`
--

DROP TABLE IF EXISTS `tahsilatcek`;
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
  PRIMARY KEY (`CekTahsilatID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilatcek`
--

LOCK TABLES `tahsilatcek` WRITE;
/*!40000 ALTER TABLE `tahsilatcek` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahsilatcek` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahsilathavale`
--

DROP TABLE IF EXISTS `tahsilathavale`;
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
  PRIMARY KEY (`HavaleTahsilatID`),
  KEY `idx_tahsilathavale_installmentid_tutar` (`InstallmentID`,`AlinanTutar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilathavale`
--

LOCK TABLES `tahsilathavale` WRITE;
/*!40000 ALTER TABLE `tahsilathavale` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahsilathavale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahsilatkart`
--

DROP TABLE IF EXISTS `tahsilatkart`;
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
  PRIMARY KEY (`KartTahsilatID`),
  KEY `idx_tahsilatkart_installmentid_tutar` (`InstallmentID`,`AlinanTutar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilatkart`
--

LOCK TABLES `tahsilatkart` WRITE;
/*!40000 ALTER TABLE `tahsilatkart` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahsilatkart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahsilatnakit`
--

DROP TABLE IF EXISTS `tahsilatnakit`;
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
  PRIMARY KEY (`NakitTahsilatID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahsilatnakit`
--

LOCK TABLES `tahsilatnakit` WRITE;
/*!40000 ALTER TABLE `tahsilatnakit` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahsilatnakit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taksitler`
--

DROP TABLE IF EXISTS `taksitler`;
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
  PRIMARY KEY (`InstallmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taksitler`
--

LOCK TABLES `taksitler` WRITE;
/*!40000 ALTER TABLE `taksitler` DISABLE KEYS */;
/*!40000 ALTER TABLE `taksitler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticarifatura`
--

DROP TABLE IF EXISTS `ticarifatura`;
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
  PRIMARY KEY (`TicariFaturaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticarifatura`
--

LOCK TABLES `ticarifatura` WRITE;
/*!40000 ALTER TABLE `ticarifatura` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticarifatura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urungrup`
--

DROP TABLE IF EXISTS `urungrup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urungrup` (
  `UrunGrupID` int(11) NOT NULL AUTO_INCREMENT,
  `UrunGrupName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`UrunGrupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urungrup`
--

LOCK TABLES `urungrup` WRITE;
/*!40000 ALTER TABLE `urungrup` DISABLE KEYS */;
/*!40000 ALTER TABLE `urungrup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urunler`
--

DROP TABLE IF EXISTS `urunler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `urunler` (
  `ProductID` int(11) NOT NULL AUTO_INCREMENT,
  `ProductName` varchar(255) DEFAULT NULL,
  `ProductDesc` varchar(255) DEFAULT NULL,
  `ProductStatus` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`ProductID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urunler`
--

LOCK TABLES `urunler` WRITE;
/*!40000 ALTER TABLE `urunler` DISABLE KEYS */;
/*!40000 ALTER TABLE `urunler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usergrup`
--

DROP TABLE IF EXISTS `usergrup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usergrup` (
  `UserGrupID` int(11) NOT NULL AUTO_INCREMENT,
  `UserGrupName` varchar(255) DEFAULT NULL,
  `UserGrupDesc` varchar(255) DEFAULT NULL,
  `UserGrupStatus` tinyint(4) NOT NULL DEFAULT 1,
  `permissions` longtext DEFAULT NULL,
  PRIMARY KEY (`UserGrupID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usergrup`
--

LOCK TABLES `usergrup` WRITE;
/*!40000 ALTER TABLE `usergrup` DISABLE KEYS */;
INSERT INTO `usergrup` VALUES (1,'Yönetici','Sistem Yöneticisi',1,'{\"all\": true}');
/*!40000 ALTER TABLE `usergrup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
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
  PRIMARY KEY (`UserID`) USING BTREE,
  UNIQUE KEY `kul_mail` (`UserMail`) USING BTREE,
  KEY `idx_users_userid_branchid` (`UserID`,`BranchID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Admin K2','admin@k2.com','5551112233','$2y$12$PKSVTRHx1dfM6IkEO2o33.4xHGhW0TZfyO4bejEtm.mjyhE6n4BhG',1,1,1,1,'dist/img/avatar5.png','acc413109771e602d7378d75abea34b4','::1');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `virmanhareket`
--

DROP TABLE IF EXISTS `virmanhareket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virmanhareket` (
  `VirmanID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `SendBank` int(11) DEFAULT NULL,
  `ReceiveBank` int(11) DEFAULT NULL,
  `Amount` double DEFAULT NULL,
  `VirmanDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`VirmanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virmanhareket`
--

LOCK TABLES `virmanhareket` WRITE;
/*!40000 ALTER TABLE `virmanhareket` DISABLE KEYS */;
/*!40000 ALTER TABLE `virmanhareket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vw_recent_logs`
--

DROP TABLE IF EXISTS `vw_recent_logs`;
/*!50001 DROP VIEW IF EXISTS `vw_recent_logs`*/;
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

DROP TABLE IF EXISTS `vw_today_summary`;
/*!50001 DROP VIEW IF EXISTS `vw_today_summary`*/;
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
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-28 20:27:28
