-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
-- Anamakine: localhost
-- Üretim Zamanı: 16 Ara 2025, 19:56:00
-- Sunucu sürümü: 10.4.28-MariaDB
-- PHP Sürümü: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `emutfak_default`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admincek`
--

CREATE TABLE `admincek` (
  `CekID` int(11) NOT NULL,
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
  `CreatedDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `adminkasa`
--

CREATE TABLE `adminkasa` (
  `AdminKasaID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `AdminKasaTip` int(11) NOT NULL,
  `AdminKasaDetay` int(11) NOT NULL,
  `AdminDesc` varchar(255) NOT NULL,
  `AdminKasaTutar` double(10,2) NOT NULL,
  `AdminRecordDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ayarlar`
--

CREATE TABLE `ayarlar` (
  `MetaID` int(11) NOT NULL,
  `SiteTitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SiteDesc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SiteAuthor` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SiteSeo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SiteKeyword` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SiteLogo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SiteAvatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `SitePhone` varchar(20) DEFAULT NULL,
  `SiteEmail` varchar(255) DEFAULT NULL,
  `SiteURL` varchar(255) DEFAULT NULL,
  `SiteAddress` text DEFAULT NULL,
  `MetaRobots` varchar(100) DEFAULT NULL,
  `SiteLanguage` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ayarlar`
--

INSERT INTO `ayarlar` (`MetaID`, `SiteTitle`, `SiteDesc`, `SiteAuthor`, `SiteSeo`, `SiteKeyword`, `SiteLogo`, `SiteAvatar`, `CompanyName`, `SitePhone`, `SiteEmail`, `SiteURL`, `SiteAddress`, `MetaRobots`, `SiteLanguage`) VALUES
(1, ' | ONLINE ERP SİSTEMİ', 'Ekolay Mutfak | 0 262 335 0 123 | Hemen Arayın!', 'BİLAL SAMİ ZAHİT ÖZGÜL - bilalozgul@hotmail.com.tr - 05327398000', 'https://www.ekolaymutfak.com', 'EKOLAY, MUTFAK, İSTANBUL MUTFAK ,İSTANBUL GÖVDE ,İSTANBUL MUTFAK GÖVDE ,İSTANBUL KABİN ,İSTANBUL MUTFAK KABİN ,İSTANBUL MODÜL ,İSTANBUL MUTFAK MODÜL ,İSTANBUL MUTFAK KAPAK , İSTANBUL MUTFAK AKSESUAR , İSTANBUL HAZIR MUTFAK ,İSTANBUL HAZIR GÖVDE ,İSTANBUL MENTEŞE ,İSTANBUL FRENLİ MENTEŞE ,İSTANBUL KALKAR KAPAK ,İSTANBUL ALT DOLAP ,\r\nİSTANBUL ÜST DOLAP ,İSTANBUL BOY DOLAP ,İSTANBUL FIRIN DOLABI ,İSTANBUL HETTİCH MENTEŞE ,İSTANBUL SAMET MENTEŞE ,İSTANBUL HETTİCH ÇEKMECE ,İSTANBUL METAL YANAKLI ÇEKMECE ,\r\nİSTANBUL SAMBOX ,İSTANBUL TANDEM ,İSTANBUL TANDEM BOX ,İSTANBUL EBATLAMA ,İSTANBUL SUNTA EBATLAMA ,İSTANBUL MDF EBATLAMA ,İSTANBUL MİNİFİX  ,İSTANBUL RAFİX ,\r\nİSTANBUL KAVELA ,İSTANBUL FRENLİ MENTEŞE ,İSTANBUL SUNTA KESİM ,İSTANBUL MDF KESİM ,İSTANBUL VESTİYER ,İSTANBUL PORTMANTO ,İSTANBUL RAY DOLAP , İSTANBUL BANYO DOLABI ,İSTANBUL GARDOLAP ,İSTANBUL ÖZEL ÖLÇÜ DOLAP ,İSTANBUL KERTİKLİ DOLAP ,İSTANBUL FASON MUTFAK ,İSTANBUL FASON GÖVDE ,İSTANBUL FASON KAPAK , İSTANBUL TOPTAN MUTFAK ,İSTANBUL TOPTAN GÖVDE ,İSTANBUL TOPTAN KABİN ,İSTANBUL TOPTAN MODÜL ,İSTANBUL STOKTAN MUTFAK ,İSTANBUL STOKTAN GÖVDE ,İSTANBUL STOKTAN MODÜL , İSTANBUL MODÜLER MUTFAK ,İSTANBUL DEKOR MUTFAK ,İSTANBUL KA1000 ,İSTANBUL NALBANTOĞLU ,İSTANBUL ERBAZLAR ,İSTANBUL GÖKTAŞLAR ,İSTANBUL UZAY MUTFAK ,İSTANBUL BAKIŞ , İSTANBUL KELEBEK MUTFAK ,İSTANBUL MOPA MUTFAK ,İSTANBUL LİNEADECOR ,İSTANBUL EURODECOR ,İSTANBUL SAĞLAMLAR EBATLAMA ,İSTANBUL AGT ,İSTANBUL YILDIZ ENTEGRE , İSTANBUL KASTAMONU ENTEGRE ,İSTANBUL STARWOOD ,İSTANBUL EGGER ,İSTANBUL ÖZEL ÖLÇÜ MUTFAK ,İSTANBUL MUTFAK AKSESUAR ,İSTANBUL SUNTA SATIŞ ,İSTANBUL MDF SATIŞ , İSTANBUL KAPI ,İSTANBUL PARKE ,İSTANBUL ANKASTRE OCAK ,İSTANBUL ANKASTRE FIRIN ,İSTANBUL DAVLUMBAZ ,İSTANBUL PLASTİK DOĞRAMA ,İSTANBUL PVC DOĞRAMA ,\r\nİSTANBUL DIŞ CEPHE KAPLAMA ,İSTANBUL EKOLAY MUTFAKLARI ,İSTANBUL GÖVDE FİYATLARI ,İSTANBUL KABİN FİYATLARI ,İSTANBUL MODÜL FİYATLARI ,İSTANBUL KOÇTAŞ , İSTANBUL İKEA ,İSTANBUL İKEA MUTFAK ,İSTANBUL ADEKO ,İSTANBUL OPTİMA DECOR ,İSTANBUL MUTFAK ÜRETİM ,İSTANBUL LAKE KAPAK ,İSTANBUL MEMBRAN KAPAK ,İSTANBUL AKRİLİK KAPAK , İSTANBUL HİGHGLOSS KAPAK ,İSTANBUL BALON KAPAK ,İSTANBUL ÇELİK KAPI ,İSTANBUL SU TESİSATI ,İSTANBUL ELEKTRİK TESİSATI ,İSTANBUL OFİS DOLABI ,İSTANBUL OFİS MASA , İSTANBUL EV MOBİLYASI ,İSTANBUL TASARIM MUTFAK ,İSTANBUL ÇİZİM MUTFAK ,İSTANBUL 1+1 MUTFAK ,İSTANBUL 2+1 MUTFAK ,İSTANBUL STÜDYO MUTFAK ,İSTANBUL MOBİLYA MAKİNALARI , İSTANBUL BANTLAMA ,İSTANBUL DELİK DELME ,İSTANBUL KANAL AÇMA ,İSTANBUL ROMA PLASTİK ,İSTANBUL BLUM  ,İSTANBUL HAFALE ,İSTANBUL MUTFAK PROJE , İSTANBUL MUTFAK TASARIM', 'dist/img/243810053_1668400084e_logo.png', 'dist/img/1595236125_1668557452Delacro-Id-IE.ico', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bankalar`
--

CREATE TABLE `bankalar` (
  `BankID` int(11) NOT NULL,
  `BankaID` int(11) NOT NULL COMMENT '1 "Akbank"\r\n2 "Denizbank"\r\n3 "ING Bank"\r\n4 "QNB Finansbank"\r\n5 "Türk Ekonomi Bankası"\r\n6 "Ziraat Bankası"\r\n7 "Garanti Bankası"\r\n8 "Halk Bankası"\r\n9 "İş Bankası"\r\n10 "Vakıflar Bankası"\r\n11 "Yapı ve Kredi Bankası"\r\n12 "Kuveyt Türk"',
  `FirmaID` int(11) NOT NULL,
  `TotalTutar` double(10,2) DEFAULT NULL,
  `IBAN` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) NOT NULL DEFAULT 0,
  `GüncelTarih` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dolapboy`
--

CREATE TABLE `dolapboy` (
  `DolapBoyID` int(11) NOT NULL,
  `DolapBoyName` varchar(255) DEFAULT NULL,
  `bDolapStandart` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dosyalar`
--

CREATE TABLE `dosyalar` (
  `FileID` int(11) NOT NULL,
  `FileAppID` int(11) DEFAULT NULL,
  `FileAuthorID` int(11) DEFAULT NULL,
  `FilePath` mediumtext DEFAULT NULL,
  `FileName` mediumtext DEFAULT NULL,
  `FileType` mediumtext DEFAULT NULL,
  `FileSize` longtext DEFAULT NULL,
  `FileCreated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `duyurular`
--

CREATE TABLE `duyurular` (
  `id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `icerik` text NOT NULL,
  `sayfa` varchar(255) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `edosyalar`
--

CREATE TABLE `edosyalar` (
  `NonFileID` int(11) NOT NULL,
  `NonFileAppID` int(11) DEFAULT NULL,
  `NonFileAuthorID` int(11) DEFAULT NULL,
  `NonFilePath` mediumtext DEFAULT NULL,
  `NonFileName` mediumtext DEFAULT NULL,
  `NonFileType` mediumtext DEFAULT NULL,
  `NonFileSize` longtext DEFAULT NULL,
  `NonFileCreated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `faturafile`
--

CREATE TABLE `faturafile` (
  `FaturaID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `AppUser` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `FaturaNo` varchar(255) DEFAULT NULL,
  `FaturaDosya` varchar(255) DEFAULT NULL,
  `FatBildirim` tinyint(4) NOT NULL DEFAULT 1,
  `UpdateDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `faturalar`
--

CREATE TABLE `faturalar` (
  `PrintID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `CompanyVNo` varchar(255) DEFAULT NULL,
  `CompanyVD` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `firmalar`
--

CREATE TABLE `firmalar` (
  `FirmaID` int(11) NOT NULL,
  `FirmaAdi` varchar(255) DEFAULT NULL,
  `FirmaDetay` varchar(255) DEFAULT NULL,
  `FirmaLogo` varchar(255) NOT NULL,
  `FirmaVergi` varchar(255) NOT NULL,
  `FirmaVNO` char(10) NOT NULL,
  `BayiCity` varchar(255) NOT NULL,
  `BayiTown` varchar(255) NOT NULL,
  `BayiAdres` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `firmalar`
--

INSERT INTO `firmalar` (`FirmaID`, `FirmaAdi`, `FirmaDetay`, `FirmaLogo`, `FirmaVergi`, `FirmaVNO`, `BayiCity`, `BayiTown`, `BayiAdres`) VALUES
(1, 'EKOLAY MUTFAK MERKEZ', 'EKOCABİN MUTFAK MOBİLYA SAN. VE TİC. A.Ş.', 'dist/img/ekolay.png', 'KORGUN MAL MÜDÜRLÜĞÜ', '3300512721', '41', '523', 'SANAYİ MH. ÖZYALI SK. NO:1 İZMİT/KOCAELİ İZMİT');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `govdecinsi`
--

CREATE TABLE `govdecinsi` (
  `GovdeCinsiID` int(11) NOT NULL,
  `GovdeCinsiName` varchar(255) DEFAULT NULL,
  `bGovdeStandart` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `govderenk`
--

CREATE TABLE `govderenk` (
  `GovdeRenkID` int(11) NOT NULL,
  `GovdeRenkName` varchar(255) DEFAULT NULL,
  `bGRenkStandart` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ilceler`
--

CREATE TABLE `ilceler` (
  `ilce_id` int(11) NOT NULL,
  `ilceadi` varchar(255) NOT NULL,
  `iller_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ilceler`
--

INSERT INTO `ilceler` (`ilce_id`, `ilceadi`, `iller_id`) VALUES
(1, 'ALADAĞ', 1),
(2, 'CEYHAN', 1),
(3, 'ÇUKUROVA', 1),
(4, 'FEKE', 1),
(5, 'İMAMOĞLU', 1),
(6, 'KARAİSALI', 1),
(7, 'KARATAŞ', 1),
(8, 'KOZAN', 1),
(9, 'POZANTI', 1),
(10, 'SAİMBEYLİ', 1),
(11, 'SARIÇAM', 1),
(12, 'SEYHAN', 1),
(13, 'TUFANBEYLİ', 1),
(14, 'YUMURTALIK', 1),
(15, 'YÜREĞİR', 1),
(16, 'BESNİ', 2),
(17, 'ÇELİKHAN', 2),
(18, 'GERGER', 2),
(19, 'GÖLBAŞI', 2),
(20, 'KAHTA', 2),
(21, 'MERKEZ', 2),
(22, 'SAMSAT', 2),
(23, 'SİNCİK', 2),
(24, 'TUT', 2),
(25, 'BAŞMAKÇI', 3),
(26, 'BAYAT', 3),
(27, 'BOLVADİN', 3),
(28, 'ÇAY', 3),
(29, 'ÇOBANLAR', 3),
(30, 'DAZKIRI', 3),
(31, 'DİNAR', 3),
(32, 'EMİRDAĞ', 3),
(33, 'EVCİLER', 3),
(34, 'HOCALAR', 3),
(35, 'İHSANİYE', 3),
(36, 'İSCEHİSAR', 3),
(37, 'KIZILÖREN', 3),
(38, 'MERKEZ', 3),
(39, 'SANDIKLI', 3),
(40, 'SİNANPAŞA', 3),
(41, 'SULTANDAĞI', 3),
(42, 'ŞUHUT', 3),
(43, 'DİYADİN', 4),
(44, 'DOĞUBAYAZIT', 4),
(45, 'ELEŞKİRT', 4),
(46, 'HAMUR', 4),
(47, 'MERKEZ', 4),
(48, 'PATNOS', 4),
(49, 'TAŞLIÇAY', 4),
(50, 'TUTAK', 4),
(51, 'AĞAÇÖREN', 68),
(52, 'ESKİL', 68),
(53, 'GÜLAĞAÇ', 68),
(54, 'GÜZELYURT', 68),
(55, 'MERKEZ', 68),
(56, 'ORTAKÖY', 68),
(57, 'SARIYAHŞİ', 68),
(58, 'SULTANHANI', 68),
(59, 'GÖYNÜCEK', 5),
(60, 'GÜMÜŞHACIKÖY', 5),
(61, 'HAMAMÖZÜ', 5),
(62, 'MERKEZ', 5),
(63, 'MERZİFON', 5),
(64, 'SULUOVA', 5),
(65, 'TAŞOVA', 5),
(66, 'AKYURT', 6),
(67, 'ALTINDAĞ', 6),
(68, 'AYAŞ', 6),
(69, 'BALA', 6),
(70, 'BEYPAZARI', 6),
(71, 'ÇAMLIDERE', 6),
(72, 'ÇANKAYA', 6),
(73, 'ÇUBUK', 6),
(74, 'ELMADAĞ', 6),
(75, 'ETİMESGUT', 6),
(76, 'EVREN', 6),
(77, 'GÖLBAŞI', 6),
(78, 'GÜDÜL', 6),
(79, 'HAYMANA', 6),
(80, 'KAHRAMANKAZAN', 6),
(81, 'KALECİK', 6),
(82, 'KEÇİÖREN', 6),
(83, 'KIZILCAHAMAM', 6),
(84, 'MAMAK', 6),
(85, 'NALLIHAN', 6),
(86, 'POLATLI', 6),
(87, 'PURSAKLAR', 6),
(88, 'SİNCAN', 6),
(89, 'ŞEREFLİKOÇHİSAR', 6),
(90, 'YENİMAHALLE', 6),
(91, 'AKSEKİ', 7),
(92, 'AKSU', 7),
(93, 'ALANYA', 7),
(94, 'DEMRE', 7),
(95, 'DÖŞEMEALTI', 7),
(96, 'ELMALI', 7),
(97, 'FİNİKE', 7),
(98, 'GAZİPAŞA', 7),
(99, 'GÜNDOĞMUŞ', 7),
(100, 'İBRADI', 7),
(101, 'KAŞ', 7),
(102, 'KEMER', 7),
(103, 'KEPEZ', 7),
(104, 'KONYAALTI', 7),
(105, 'KORKUTELİ', 7),
(106, 'KUMLUCA', 7),
(107, 'MANAVGAT', 7),
(108, 'MURATPAŞA', 7),
(109, 'SERİK', 7),
(110, 'ÇILDIR', 75),
(111, 'DAMAL', 75),
(112, 'GÖLE', 75),
(113, 'HANAK', 75),
(114, 'MERKEZ', 75),
(115, 'POSOF', 75),
(116, 'ARDANUÇ', 8),
(117, 'ARHAVİ', 8),
(118, 'BORÇKA', 8),
(119, 'HOPA', 8),
(120, 'KEMALPAŞA', 8),
(121, 'MERKEZ', 8),
(122, 'MURGUL', 8),
(123, 'ŞAVŞAT', 8),
(124, 'YUSUFELİ', 8),
(125, 'BOZDOĞAN', 9),
(126, 'BUHARKENT', 9),
(127, 'ÇİNE', 9),
(128, 'DİDİM', 9),
(129, 'EFELER', 9),
(130, 'GERMENCİK', 9),
(131, 'İNCİRLİOVA', 9),
(132, 'KARACASU', 9),
(133, 'KARPUZLU', 9),
(134, 'KOÇARLI', 9),
(135, 'KÖŞK', 9),
(136, 'KUŞADASI', 9),
(137, 'KUYUCAK', 9),
(138, 'NAZİLLİ', 9),
(139, 'SÖKE', 9),
(140, 'SULTANHİSAR', 9),
(141, 'YENİPAZAR', 9),
(142, 'ALTIEYLÜL', 10),
(143, 'AYVALIK', 10),
(144, 'BALYA', 10),
(145, 'BANDIRMA', 10),
(146, 'BİGADİÇ', 10),
(147, 'BURHANİYE', 10),
(148, 'DURSUNBEY', 10),
(149, 'EDREMİT', 10),
(150, 'ERDEK', 10),
(151, 'GÖMEÇ', 10),
(152, 'GÖNEN', 10),
(153, 'HAVRAN', 10),
(154, 'İVRİNDİ', 10),
(155, 'KARESİ', 10),
(156, 'KEPSUT', 10),
(157, 'MANYAS', 10),
(158, 'MARMARA', 10),
(159, 'SAVAŞTEPE', 10),
(160, 'SINDIRGI', 10),
(161, 'SUSURLUK', 10),
(162, 'AMASRA', 74),
(163, 'KURUCAŞİLE', 74),
(164, 'MERKEZ', 74),
(165, 'ULUS', 74),
(166, 'BEŞİRİ', 72),
(167, 'GERCÜŞ', 72),
(168, 'HASANKEYF', 72),
(169, 'KOZLUK', 72),
(170, 'MERKEZ', 72),
(171, 'SASON', 72),
(172, 'AYDINTEPE', 69),
(173, 'DEMİRÖZÜ', 69),
(174, 'MERKEZ', 69),
(175, 'BOZÜYÜK', 11),
(176, 'GÖLPAZARI', 11),
(177, 'İNHİSAR', 11),
(178, 'MERKEZ', 11),
(179, 'OSMANELİ', 11),
(180, 'PAZARYERİ', 11),
(181, 'SÖĞÜT', 11),
(182, 'YENİPAZAR', 11),
(183, 'ADAKLI', 12),
(184, 'GENÇ', 12),
(185, 'KARLIOVA', 12),
(186, 'KİĞI', 12),
(187, 'MERKEZ', 12),
(188, 'SOLHAN', 12),
(189, 'YAYLADERE', 12),
(190, 'YEDİSU', 12),
(191, 'ADİLCEVAZ', 13),
(192, 'AHLAT', 13),
(193, 'GÜROYMAK', 13),
(194, 'HİZAN', 13),
(195, 'MERKEZ', 13),
(196, 'MUTKİ', 13),
(197, 'TATVAN', 13),
(198, 'DÖRTDİVAN', 14),
(199, 'GEREDE', 14),
(200, 'GÖYNÜK', 14),
(201, 'KIBRISCIK', 14),
(202, 'MENGEN', 14),
(203, 'MERKEZ', 14),
(204, 'MUDURNU', 14),
(205, 'SEBEN', 14),
(206, 'YENİÇAĞA', 14),
(207, 'AĞLASUN', 15),
(208, 'ALTINYAYLA', 15),
(209, 'BUCAK', 15),
(210, 'ÇAVDIR', 15),
(211, 'ÇELTİKÇİ', 15),
(212, 'GÖLHİSAR', 15),
(213, 'KARAMANLI', 15),
(214, 'KEMER', 15),
(215, 'MERKEZ', 15),
(216, 'TEFENNİ', 15),
(217, 'YEŞİLOVA', 15),
(218, 'BÜYÜKORHAN', 16),
(219, 'GEMLİK', 16),
(220, 'GÜRSU', 16),
(221, 'HARMANCIK', 16),
(222, 'İNEGÖL', 16),
(223, 'İZNİK', 16),
(224, 'KARACABEY', 16),
(225, 'KELES', 16),
(226, 'KESTEL', 16),
(227, 'MUDANYA', 16),
(228, 'MUSTAFAKEMALPAŞA', 16),
(229, 'NİLÜFER', 16),
(230, 'ORHANELİ', 16),
(231, 'ORHANGAZİ', 16),
(232, 'OSMANGAZİ', 16),
(233, 'YENİŞEHİR', 16),
(234, 'YILDIRIM', 16),
(235, 'AYVACIK', 17),
(236, 'BAYRAMİÇ', 17),
(237, 'BİGA', 17),
(238, 'BOZCAADA', 17),
(239, 'ÇAN', 17),
(240, 'ECEABAT', 17),
(241, 'EZİNE', 17),
(242, 'GELİBOLU', 17),
(243, 'GÖKÇEADA', 17),
(244, 'LAPSEKİ', 17),
(245, 'MERKEZ', 17),
(246, 'YENİCE', 17),
(247, 'ATKARACALAR', 18),
(248, 'BAYRAMÖREN', 18),
(249, 'ÇERKEŞ', 18),
(250, 'ELDİVAN', 18),
(251, 'ILGAZ', 18),
(252, 'KIZILIRMAK', 18),
(253, 'KORGUN', 18),
(254, 'KURŞUNLU', 18),
(255, 'MERKEZ', 18),
(256, 'ORTA', 18),
(257, 'ŞABANÖZÜ', 18),
(258, 'YAPRAKLI', 18),
(259, 'ALACA', 19),
(260, 'BAYAT', 19),
(261, 'BOĞAZKALE', 19),
(262, 'DODURGA', 19),
(263, 'İSKİLİP', 19),
(264, 'KARGI', 19),
(265, 'LAÇİN', 19),
(266, 'MECİTÖZÜ', 19),
(267, 'MERKEZ', 19),
(268, 'OĞUZLAR', 19),
(269, 'ORTAKÖY', 19),
(270, 'OSMANCIK', 19),
(271, 'SUNGURLU', 19),
(272, 'UĞURLUDAĞ', 19),
(273, 'ACIPAYAM', 20),
(274, 'BABADAĞ', 20),
(275, 'BAKLAN', 20),
(276, 'BEKİLLİ', 20),
(277, 'BEYAĞAÇ', 20),
(278, 'BOZKURT', 20),
(279, 'BULDAN', 20),
(280, 'ÇAL', 20),
(281, 'ÇAMELİ', 20),
(282, 'ÇARDAK', 20),
(283, 'ÇİVRİL', 20),
(284, 'GÜNEY', 20),
(285, 'HONAZ', 20),
(286, 'KALE', 20),
(287, 'MERKEZEFENDİ', 20),
(288, 'PAMUKKALE', 20),
(289, 'SARAYKÖY', 20),
(290, 'SERİNHİSAR', 20),
(291, 'TAVAS', 20),
(292, 'BAĞLAR', 21),
(293, 'BİSMİL', 21),
(294, 'ÇERMİK', 21),
(295, 'ÇINAR', 21),
(296, 'ÇÜNGÜŞ', 21),
(297, 'DİCLE', 21),
(298, 'EĞİL', 21),
(299, 'ERGANİ', 21),
(300, 'HANİ', 21),
(301, 'HAZRO', 21),
(302, 'KAYAPINAR', 21),
(303, 'KOCAKÖY', 21),
(304, 'KULP', 21),
(305, 'LİCE', 21),
(306, 'SİLVAN', 21),
(307, 'SUR', 21),
(308, 'YENİŞEHİR', 21),
(309, 'AKÇAKOCA', 81),
(310, 'CUMAYERİ', 81),
(311, 'ÇİLİMLİ', 81),
(312, 'GÖLYAKA', 81),
(313, 'GÜMÜŞOVA', 81),
(314, 'KAYNAŞLI', 81),
(315, 'MERKEZ', 81),
(316, 'YIĞILCA', 81),
(317, 'ENEZ', 22),
(318, 'HAVSA', 22),
(319, 'İPSALA', 22),
(320, 'KEŞAN', 22),
(321, 'LALAPAŞA', 22),
(322, 'MERİÇ', 22),
(323, 'MERKEZ', 22),
(324, 'SÜLOĞLU', 22),
(325, 'UZUNKÖPRÜ', 22),
(326, 'AĞIN', 23),
(327, 'ALACAKAYA', 23),
(328, 'ARICAK', 23),
(329, 'BASKİL', 23),
(330, 'KARAKOÇAN', 23),
(331, 'KEBAN', 23),
(332, 'KOVANCILAR', 23),
(333, 'MADEN', 23),
(334, 'MERKEZ', 23),
(335, 'PALU', 23),
(336, 'SİVRİCE', 23),
(337, 'ÇAYIRLI', 24),
(338, 'İLİÇ', 24),
(339, 'KEMAH', 24),
(340, 'KEMALİYE', 24),
(341, 'MERKEZ', 24),
(342, 'OTLUKBELİ', 24),
(343, 'REFAHİYE', 24),
(344, 'TERCAN', 24),
(345, 'ÜZÜMLÜ', 24),
(346, 'AŞKALE', 25),
(347, 'AZİZİYE', 25),
(348, 'ÇAT', 25),
(349, 'HINIS', 25),
(350, 'HORASAN', 25),
(351, 'İSPİR', 25),
(352, 'KARAÇOBAN', 25),
(353, 'KARAYAZI', 25),
(354, 'KÖPRÜKÖY', 25),
(355, 'NARMAN', 25),
(356, 'OLTU', 25),
(357, 'OLUR', 25),
(358, 'PALANDÖKEN', 25),
(359, 'PASİNLER', 25),
(360, 'PAZARYOLU', 25),
(361, 'ŞENKAYA', 25),
(362, 'TEKMAN', 25),
(363, 'TORTUM', 25),
(364, 'UZUNDERE', 25),
(365, 'YAKUTİYE', 25),
(366, 'ALPU', 26),
(367, 'BEYLİKOVA', 26),
(368, 'ÇİFTELER', 26),
(369, 'GÜNYÜZÜ', 26),
(370, 'HAN', 26),
(371, 'İNÖNÜ', 26),
(372, 'MAHMUDİYE', 26),
(373, 'MİHALGAZİ', 26),
(374, 'MİHALIÇÇIK', 26),
(375, 'ODUNPAZARI', 26),
(376, 'SARICAKAYA', 26),
(377, 'SEYİTGAZİ', 26),
(378, 'SİVRİHİSAR', 26),
(379, 'TEPEBAŞI', 26),
(380, 'ARABAN', 27),
(381, 'İSLAHİYE', 27),
(382, 'KARKAMIŞ', 27),
(383, 'NİZİP', 27),
(384, 'NURDAĞI', 27),
(385, 'OĞUZELİ', 27),
(386, 'ŞAHİNBEY', 27),
(387, 'ŞEHİTKAMİL', 27),
(388, 'YAVUZELİ', 27),
(389, 'ALUCRA', 28),
(390, 'BULANCAK', 28),
(391, 'ÇAMOLUK', 28),
(392, 'ÇANAKÇI', 28),
(393, 'DERELİ', 28),
(394, 'DOĞANKENT', 28),
(395, 'ESPİYE', 28),
(396, 'EYNESİL', 28),
(397, 'GÖRELE', 28),
(398, 'GÜCE', 28),
(399, 'KEŞAP', 28),
(400, 'MERKEZ', 28),
(401, 'PİRAZİZ', 28),
(402, 'ŞEBİNKARAHİSAR', 28),
(403, 'TİREBOLU', 28),
(404, 'YAĞLIDERE', 28),
(405, 'KELKİT', 29),
(406, 'KÖSE', 29),
(407, 'KÜRTÜN', 29),
(408, 'MERKEZ', 29),
(409, 'ŞİRAN', 29),
(410, 'TORUL', 29),
(411, 'ÇUKURCA', 30),
(412, 'DERECİK', 30),
(413, 'MERKEZ', 30),
(414, 'ŞEMDİNLİ', 30),
(415, 'YÜKSEKOVA', 30),
(416, 'ALTINÖZÜ', 31),
(417, 'ANTAKYA', 31),
(418, 'ARSUZ', 31),
(419, 'BELEN', 31),
(420, 'DEFNE', 31),
(421, 'DÖRTYOL', 31),
(422, 'ERZİN', 31),
(423, 'HASSA', 31),
(424, 'İSKENDERUN', 31),
(425, 'KIRIKHAN', 31),
(426, 'KUMLU', 31),
(427, 'PAYAS', 31),
(428, 'REYHANLI', 31),
(429, 'SAMANDAĞ', 31),
(430, 'YAYLADAĞI', 31),
(431, 'ARALIK', 76),
(432, 'KARAKOYUNLU', 76),
(433, 'MERKEZ', 76),
(434, 'TUZLUCA', 76),
(435, 'AKSU', 32),
(436, 'ATABEY', 32),
(437, 'EĞİRDİR', 32),
(438, 'GELENDOST', 32),
(439, 'GÖNEN', 32),
(440, 'KEÇİBORLU', 32),
(441, 'MERKEZ', 32),
(442, 'SENİRKENT', 32),
(443, 'SÜTÇÜLER', 32),
(444, 'ŞARKİKARAAĞAÇ', 32),
(445, 'ULUBORLU', 32),
(446, 'YALVAÇ', 32),
(447, 'YENİŞARBADEMLİ', 32),
(448, 'ADALAR', 34),
(449, 'ARNAVUTKÖY', 34),
(450, 'ATAŞEHİR', 34),
(451, 'AVCILAR', 34),
(452, 'BAĞCILAR', 34),
(453, 'BAHÇELİEVLER', 34),
(454, 'BAKIRKÖY', 34),
(455, 'BAŞAKŞEHİR', 34),
(456, 'BAYRAMPAŞA', 34),
(457, 'BEŞİKTAŞ', 34),
(458, 'BEYKOZ', 34),
(459, 'BEYLİKDÜZÜ', 34),
(460, 'BEYOĞLU', 34),
(461, 'BÜYÜKÇEKMECE', 34),
(462, 'ÇATALCA', 34),
(463, 'ÇEKMEKÖY', 34),
(464, 'ESENLER', 34),
(465, 'ESENYURT', 34),
(466, 'EYÜPSULTAN', 34),
(467, 'FATİH', 34),
(468, 'GAZİOSMANPAŞA', 34),
(469, 'GÜNGÖREN', 34),
(470, 'KADIKÖY', 34),
(471, 'KAĞITHANE', 34),
(472, 'KARTAL', 34),
(473, 'KÜÇÜKÇEKMECE', 34),
(474, 'MALTEPE', 34),
(475, 'PENDİK', 34),
(476, 'SANCAKTEPE', 34),
(477, 'SARIYER', 34),
(478, 'SİLİVRİ', 34),
(479, 'SULTANBEYLİ', 34),
(480, 'SULTANGAZİ', 34),
(481, 'ŞİLE', 34),
(482, 'ŞİŞLİ', 34),
(483, 'TUZLA', 34),
(484, 'ÜMRANİYE', 34),
(485, 'ÜSKÜDAR', 34),
(486, 'ZEYTİNBURNU', 34),
(487, 'ALİAĞA', 35),
(488, 'BALÇOVA', 35),
(489, 'BAYINDIR', 35),
(490, 'BAYRAKLI', 35),
(491, 'BERGAMA', 35),
(492, 'BEYDAĞ', 35),
(493, 'BORNOVA', 35),
(494, 'BUCA', 35),
(495, 'ÇEŞME', 35),
(496, 'ÇİĞLİ', 35),
(497, 'DİKİLİ', 35),
(498, 'FOÇA', 35),
(499, 'GAZİEMİR', 35),
(500, 'GÜZELBAHÇE', 35),
(501, 'KARABAĞLAR', 35),
(502, 'KARABURUN', 35),
(503, 'KARŞIYAKA', 35),
(504, 'KEMALPAŞA', 35),
(505, 'KINIK', 35),
(506, 'KİRAZ', 35),
(507, 'KONAK', 35),
(508, 'MENDERES', 35),
(509, 'MENEMEN', 35),
(510, 'NARLIDERE', 35),
(511, 'ÖDEMİŞ', 35),
(512, 'SEFERİHİSAR', 35),
(513, 'SELÇUK', 35),
(514, 'TİRE', 35),
(515, 'TORBALI', 35),
(516, 'URLA', 35),
(517, 'AFŞİN', 46),
(518, 'ANDIRIN', 46),
(519, 'ÇAĞLAYANCERİT', 46),
(520, 'DULKADİROĞLU', 46),
(521, 'EKİNÖZÜ', 46),
(522, 'ELBİSTAN', 46),
(523, 'GÖKSUN', 46),
(524, 'NURHAK', 46),
(525, 'ONİKİŞUBAT', 46),
(526, 'PAZARCIK', 46),
(527, 'TÜRKOĞLU', 46),
(528, 'EFLANİ', 78),
(529, 'ESKİPAZAR', 78),
(530, 'MERKEZ', 78),
(531, 'OVACIK', 78),
(532, 'SAFRANBOLU', 78),
(533, 'YENİCE', 78),
(534, 'AYRANCI', 70),
(535, 'BAŞYAYLA', 70),
(536, 'ERMENEK', 70),
(537, 'KAZIMKARABEKİR', 70),
(538, 'MERKEZ', 70),
(539, 'SARIVELİLER', 70),
(540, 'AKYAKA', 36),
(541, 'ARPAÇAY', 36),
(542, 'DİGOR', 36),
(543, 'KAĞIZMAN', 36),
(544, 'MERKEZ', 36),
(545, 'SARIKAMIŞ', 36),
(546, 'SELİM', 36),
(547, 'SUSUZ', 36),
(548, 'ABANA', 37),
(549, 'AĞLI', 37),
(550, 'ARAÇ', 37),
(551, 'AZDAVAY', 37),
(552, 'BOZKURT', 37),
(553, 'CİDE', 37),
(554, 'ÇATALZEYTİN', 37),
(555, 'DADAY', 37),
(556, 'DEVREKANİ', 37),
(557, 'DOĞANYURT', 37),
(558, 'HANÖNÜ', 37),
(559, 'İHSANGAZİ', 37),
(560, 'İNEBOLU', 37),
(561, 'KÜRE', 37),
(562, 'MERKEZ', 37),
(563, 'PINARBAŞI', 37),
(564, 'SEYDİLER', 37),
(565, 'ŞENPAZAR', 37),
(566, 'TAŞKÖPRÜ', 37),
(567, 'TOSYA', 37),
(568, 'AKKIŞLA', 38),
(569, 'BÜNYAN', 38),
(570, 'DEVELİ', 38),
(571, 'FELAHİYE', 38),
(572, 'HACILAR', 38),
(573, 'İNCESU', 38),
(574, 'KOCASİNAN', 38),
(575, 'MELİKGAZİ', 38),
(576, 'ÖZVATAN', 38),
(577, 'PINARBAŞI', 38),
(578, 'SARIOĞLAN', 38),
(579, 'SARIZ', 38),
(580, 'TALAS', 38),
(581, 'TOMARZA', 38),
(582, 'YAHYALI', 38),
(583, 'YEŞİLHİSAR', 38),
(584, 'BAHŞILI', 71),
(585, 'BALIŞEYH', 71),
(586, 'ÇELEBİ', 71),
(587, 'DELİCE', 71),
(588, 'KARAKEÇİLİ', 71),
(589, 'KESKİN', 71),
(590, 'MERKEZ', 71),
(591, 'SULAKYURT', 71),
(592, 'YAHŞİHAN', 71),
(593, 'BABAESKİ', 39),
(594, 'DEMİRKÖY', 39),
(595, 'KOFÇAZ', 39),
(596, 'LÜLEBURGAZ', 39),
(597, 'MERKEZ', 39),
(598, 'PEHLİVANKÖY', 39),
(599, 'PINARHİSAR', 39),
(600, 'VİZE', 39),
(601, 'AKÇAKENT', 40),
(602, 'AKPINAR', 40),
(603, 'BOZTEPE', 40),
(604, 'ÇİÇEKDAĞI', 40),
(605, 'KAMAN', 40),
(606, 'MERKEZ', 40),
(607, 'MUCUR', 40),
(608, 'ELBEYLİ', 79),
(609, 'MERKEZ', 79),
(610, 'MUSABEYLİ', 79),
(611, 'POLATELİ', 79),
(612, 'BAŞİSKELE', 41),
(613, 'ÇAYIROVA', 41),
(614, 'DARICA', 41),
(615, 'DERİNCE', 41),
(616, 'DİLOVASI', 41),
(617, 'GEBZE', 41),
(618, 'GÖLCÜK', 41),
(619, 'İZMİT', 41),
(620, 'KANDIRA', 41),
(621, 'KARAMÜRSEL', 41),
(622, 'KARTEPE', 41),
(623, 'KÖRFEZ', 41),
(624, 'AHIRLI', 42),
(625, 'AKÖREN', 42),
(626, 'AKŞEHİR', 42),
(627, 'ALTINEKİN', 42),
(628, 'BEYŞEHİR', 42),
(629, 'BOZKIR', 42),
(630, 'CİHANBEYLİ', 42),
(631, 'ÇELTİK', 42),
(632, 'ÇUMRA', 42),
(633, 'DERBENT', 42),
(634, 'DEREBUCAK', 42),
(635, 'DOĞANHİSAR', 42),
(636, 'EMİRGAZİ', 42),
(637, 'EREĞLİ', 42),
(638, 'GÜNEYSINIR', 42),
(639, 'HADİM', 42),
(640, 'HALKAPINAR', 42),
(641, 'HÜYÜK', 42),
(642, 'ILGIN', 42),
(643, 'KADINHANI', 42),
(644, 'KARAPINAR', 42),
(645, 'KARATAY', 42),
(646, 'KULU', 42),
(647, 'MERAM', 42),
(648, 'SARAYÖNÜ', 42),
(649, 'SELÇUKLU', 42),
(650, 'SEYDİŞEHİR', 42),
(651, 'TAŞKENT', 42),
(652, 'TUZLUKÇU', 42),
(653, 'YALIHÜYÜK', 42),
(654, 'YUNAK', 42),
(655, 'ALTINTAŞ', 43),
(656, 'ASLANAPA', 43),
(657, 'ÇAVDARHİSAR', 43),
(658, 'DOMANİÇ', 43),
(659, 'DUMLUPINAR', 43),
(660, 'EMET', 43),
(661, 'GEDİZ', 43),
(662, 'HİSARCIK', 43),
(663, 'MERKEZ', 43),
(664, 'PAZARLAR', 43),
(665, 'SİMAV', 43),
(666, 'ŞAPHANE', 43),
(667, 'TAVŞANLI', 43),
(668, 'AKÇADAĞ', 44),
(669, 'ARAPGİR', 44),
(670, 'ARGUVAN', 44),
(671, 'BATTALGAZİ', 44),
(672, 'DARENDE', 44),
(673, 'DOĞANŞEHİR', 44),
(674, 'DOĞANYOL', 44),
(675, 'HEKİMHAN', 44),
(676, 'KALE', 44),
(677, 'KULUNCAK', 44),
(678, 'PÜTÜRGE', 44),
(679, 'YAZIHAN', 44),
(680, 'YEŞİLYURT', 44),
(681, 'AHMETLİ', 45),
(682, 'AKHİSAR', 45),
(683, 'ALAŞEHİR', 45),
(684, 'DEMİRCİ', 45),
(685, 'GÖLMARMARA', 45),
(686, 'GÖRDES', 45),
(687, 'KIRKAĞAÇ', 45),
(688, 'KÖPRÜBAŞI', 45),
(689, 'KULA', 45),
(690, 'SALİHLİ', 45),
(691, 'SARIGÖL', 45),
(692, 'SARUHANLI', 45),
(693, 'SELENDİ', 45),
(694, 'SOMA', 45),
(695, 'ŞEHZADELER', 45),
(696, 'TURGUTLU', 45),
(697, 'YUNUSEMRE', 45),
(698, 'ARTUKLU', 47),
(699, 'DARGEÇİT', 47),
(700, 'DERİK', 47),
(701, 'KIZILTEPE', 47),
(702, 'MAZIDAĞI', 47),
(703, 'MİDYAT', 47),
(704, 'NUSAYBİN', 47),
(705, 'ÖMERLİ', 47),
(706, 'SAVUR', 47),
(707, 'YEŞİLLİ', 47),
(708, 'AKDENİZ', 33),
(709, 'ANAMUR', 33),
(710, 'AYDINCIK', 33),
(711, 'BOZYAZI', 33),
(712, 'ÇAMLIYAYLA', 33),
(713, 'ERDEMLİ', 33),
(714, 'GÜLNAR', 33),
(715, 'MEZİTLİ', 33),
(716, 'MUT', 33),
(717, 'SİLİFKE', 33),
(718, 'TARSUS', 33),
(719, 'TOROSLAR', 33),
(720, 'YENİŞEHİR', 33),
(721, 'BODRUM', 48),
(722, 'DALAMAN', 48),
(723, 'DATÇA', 48),
(724, 'FETHİYE', 48),
(725, 'KAVAKLIDERE', 48),
(726, 'KÖYCEĞİZ', 48),
(727, 'MARMARİS', 48),
(728, 'MENTEŞE', 48),
(729, 'MİLAS', 48),
(730, 'ORTACA', 48),
(731, 'SEYDİKEMER', 48),
(732, 'ULA', 48),
(733, 'YATAĞAN', 48),
(734, 'BULANIK', 49),
(735, 'HASKÖY', 49),
(736, 'KORKUT', 49),
(737, 'MALAZGİRT', 49),
(738, 'MERKEZ', 49),
(739, 'VARTO', 49),
(740, 'ACIGÖL', 50),
(741, 'AVANOS', 50),
(742, 'DERİNKUYU', 50),
(743, 'GÜLŞEHİR', 50),
(744, 'HACIBEKTAŞ', 50),
(745, 'KOZAKLI', 50),
(746, 'MERKEZ', 50),
(747, 'ÜRGÜP', 50),
(748, 'ALTUNHİSAR', 51),
(749, 'BOR', 51),
(750, 'ÇAMARDI', 51),
(751, 'ÇİFTLİK', 51),
(752, 'MERKEZ', 51),
(753, 'ULUKIŞLA', 51),
(754, 'AKKUŞ', 52),
(755, 'ALTINORDU', 52),
(756, 'AYBASTI', 52),
(757, 'ÇAMAŞ', 52),
(758, 'ÇATALPINAR', 52),
(759, 'ÇAYBAŞI', 52),
(760, 'FATSA', 52),
(761, 'GÖLKÖY', 52),
(762, 'GÜLYALI', 52),
(763, 'GÜRGENTEPE', 52),
(764, 'İKİZCE', 52),
(765, 'KABADÜZ', 52),
(766, 'KABATAŞ', 52),
(767, 'KORGAN', 52),
(768, 'KUMRU', 52),
(769, 'MESUDİYE', 52),
(770, 'PERŞEMBE', 52),
(771, 'ULUBEY', 52),
(772, 'ÜNYE', 52),
(773, 'BAHÇE', 80),
(774, 'DÜZİÇİ', 80),
(775, 'HASANBEYLİ', 80),
(776, 'KADİRLİ', 80),
(777, 'MERKEZ', 80),
(778, 'SUMBAS', 80),
(779, 'TOPRAKKALE', 80),
(780, 'ARDEŞEN', 53),
(781, 'ÇAMLIHEMŞİN', 53),
(782, 'ÇAYELİ', 53),
(783, 'DEREPAZARI', 53),
(784, 'FINDIKLI', 53),
(785, 'GÜNEYSU', 53),
(786, 'HEMŞİN', 53),
(787, 'İKİZDERE', 53),
(788, 'İYİDERE', 53),
(789, 'KALKANDERE', 53),
(790, 'MERKEZ', 53),
(791, 'PAZAR', 53),
(792, 'ADAPAZARI', 54),
(793, 'AKYAZI', 54),
(794, 'ARİFİYE', 54),
(795, 'ERENLER', 54),
(796, 'FERİZLİ', 54),
(797, 'GEYVE', 54),
(798, 'HENDEK', 54),
(799, 'KARAPÜRÇEK', 54),
(800, 'KARASU', 54),
(801, 'KAYNARCA', 54),
(802, 'KOCAALİ', 54),
(803, 'PAMUKOVA', 54),
(804, 'SAPANCA', 54),
(805, 'SERDİVAN', 54),
(806, 'SÖĞÜTLÜ', 54),
(807, 'TARAKLI', 54),
(808, '19 MAYIS', 55),
(809, 'ALAÇAM', 55),
(810, 'ASARCIK', 55),
(811, 'ATAKUM', 55),
(812, 'AYVACIK', 55),
(813, 'BAFRA', 55),
(814, 'CANİK', 55),
(815, 'ÇARŞAMBA', 55),
(816, 'HAVZA', 55),
(817, 'İLKADIM', 55),
(818, 'KAVAK', 55),
(819, 'LADİK', 55),
(820, 'SALIPAZARI', 55),
(821, 'TEKKEKÖY', 55),
(822, 'TERME', 55),
(823, 'VEZİRKÖPRÜ', 55),
(824, 'YAKAKENT', 55),
(825, 'BAYKAN', 56),
(826, 'ERUH', 56),
(827, 'KURTALAN', 56),
(828, 'MERKEZ', 56),
(829, 'PERVARİ', 56),
(830, 'ŞİRVAN', 56),
(831, 'TİLLO', 56),
(832, 'AYANCIK', 57),
(833, 'BOYABAT', 57),
(834, 'DİKMEN', 57),
(835, 'DURAĞAN', 57),
(836, 'ERFELEK', 57),
(837, 'GERZE', 57),
(838, 'MERKEZ', 57),
(839, 'SARAYDÜZÜ', 57),
(840, 'TÜRKELİ', 57),
(841, 'AKINCILAR', 58),
(842, 'ALTINYAYLA', 58),
(843, 'DİVRİĞİ', 58),
(844, 'DOĞANŞAR', 58),
(845, 'GEMEREK', 58),
(846, 'GÖLOVA', 58),
(847, 'GÜRÜN', 58),
(848, 'HAFİK', 58),
(849, 'İMRANLI', 58),
(850, 'KANGAL', 58),
(851, 'KOYULHİSAR', 58),
(852, 'MERKEZ', 58),
(853, 'SUŞEHRİ', 58),
(854, 'ŞARKIŞLA', 58),
(855, 'ULAŞ', 58),
(856, 'YILDIZELİ', 58),
(857, 'ZARA', 58),
(858, 'AKÇAKALE', 63),
(859, 'BİRECİK', 63),
(860, 'BOZOVA', 63),
(861, 'CEYLANPINAR', 63),
(862, 'EYYÜBİYE', 63),
(863, 'HALFETİ', 63),
(864, 'HALİLİYE', 63),
(865, 'HARRAN', 63),
(866, 'HİLVAN', 63),
(867, 'KARAKÖPRÜ', 63),
(868, 'SİVEREK', 63),
(869, 'SURUÇ', 63),
(870, 'VİRANŞEHİR', 63),
(871, 'BEYTÜŞŞEBAP', 73),
(872, 'CİZRE', 73),
(873, 'GÜÇLÜKONAK', 73),
(874, 'İDİL', 73),
(875, 'MERKEZ', 73),
(876, 'SİLOPİ', 73),
(877, 'ULUDERE', 73),
(878, 'ÇERKEZKÖY', 59),
(879, 'ÇORLU', 59),
(880, 'ERGENE', 59),
(881, 'HAYRABOLU', 59),
(882, 'KAPAKLI', 59),
(883, 'MALKARA', 59),
(884, 'MARMARAEREĞLİSİ', 59),
(885, 'MURATLI', 59),
(886, 'SARAY', 59),
(887, 'SÜLEYMANPAŞA', 59),
(888, 'ŞARKÖY', 59),
(889, 'ALMUS', 60),
(890, 'ARTOVA', 60),
(891, 'BAŞÇİFTLİK', 60),
(892, 'ERBAA', 60),
(893, 'MERKEZ', 60),
(894, 'NİKSAR', 60),
(895, 'PAZAR', 60),
(896, 'REŞADİYE', 60),
(897, 'SULUSARAY', 60),
(898, 'TURHAL', 60),
(899, 'YEŞİLYURT', 60),
(900, 'ZİLE', 60),
(901, 'AKÇAABAT', 61),
(902, 'ARAKLI', 61),
(903, 'ARSİN', 61),
(904, 'BEŞİKDÜZÜ', 61),
(905, 'ÇARŞIBAŞI', 61),
(906, 'ÇAYKARA', 61),
(907, 'DERNEKPAZARI', 61),
(908, 'DÜZKÖY', 61),
(909, 'HAYRAT', 61),
(910, 'KÖPRÜBAŞI', 61),
(911, 'MAÇKA', 61),
(912, 'OF', 61),
(913, 'ORTAHİSAR', 61),
(914, 'SÜRMENE', 61),
(915, 'ŞALPAZARI', 61),
(916, 'TONYA', 61),
(917, 'VAKFIKEBİR', 61),
(918, 'YOMRA', 61),
(919, 'ÇEMİŞGEZEK', 62),
(920, 'HOZAT', 62),
(921, 'MAZGİRT', 62),
(922, 'MERKEZ', 62),
(923, 'NAZIMİYE', 62),
(924, 'OVACIK', 62),
(925, 'PERTEK', 62),
(926, 'PÜLÜMÜR', 62),
(927, 'BANAZ', 64),
(928, 'EŞME', 64),
(929, 'KARAHALLI', 64),
(930, 'MERKEZ', 64),
(931, 'SİVASLI', 64),
(932, 'ULUBEY', 64),
(933, 'BAHÇESARAY', 65),
(934, 'BAŞKALE', 65),
(935, 'ÇALDIRAN', 65),
(936, 'ÇATAK', 65),
(937, 'EDREMİT', 65),
(938, 'ERCİŞ', 65),
(939, 'GEVAŞ', 65),
(940, 'GÜRPINAR', 65),
(941, 'İPEKYOLU', 65),
(942, 'MURADİYE', 65),
(943, 'ÖZALP', 65),
(944, 'SARAY', 65),
(945, 'TUŞBA', 65),
(946, 'ALTINOVA', 77),
(947, 'ARMUTLU', 77),
(948, 'ÇINARCIK', 77),
(949, 'ÇİFTLİKKÖY', 77),
(950, 'MERKEZ', 77),
(951, 'TERMAL', 77),
(952, 'AKDAĞMADENİ', 66),
(953, 'AYDINCIK', 66),
(954, 'BOĞAZLIYAN', 66),
(955, 'ÇANDIR', 66),
(956, 'ÇAYIRALAN', 66),
(957, 'ÇEKEREK', 66),
(958, 'KADIŞEHRİ', 66),
(959, 'MERKEZ', 66),
(960, 'SARAYKENT', 66),
(961, 'SARIKAYA', 66),
(962, 'SORGUN', 66),
(963, 'ŞEFAATLİ', 66),
(964, 'YENİFAKILI', 66),
(965, 'YERKÖY', 66),
(966, 'ALAPLI', 67),
(967, 'ÇAYCUMA', 67),
(968, 'DEVREK', 67),
(969, 'EREĞLİ', 67),
(970, 'GÖKÇEBEY', 67),
(971, 'KİLİMLİ', 67),
(972, 'KOZLU', 67),
(973, 'MERKEZ', 67);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `iller`
--

CREATE TABLE `iller` (
  `iller_id` int(11) NOT NULL,
  `sehiradi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `iller`
--

INSERT INTO `iller` (`iller_id`, `sehiradi`) VALUES
(1, 'ADANA'),
(2, 'ADIYAMAN'),
(3, 'AFYON'),
(4, 'AĞRI'),
(5, 'AMASYA'),
(6, 'ANKARA'),
(7, 'ANTALYA'),
(8, 'ARTVİN'),
(9, 'AYDIN'),
(10, 'BALIKESİR'),
(11, 'BİLECİK'),
(12, 'BİNGÖL'),
(13, 'BİTLİS'),
(14, 'BOLU'),
(15, 'BURDUR'),
(16, 'BURSA'),
(17, 'ÇANAKKALE'),
(18, 'ÇANKIRI'),
(19, 'ÇORUM'),
(20, 'DENİZLİ'),
(21, 'DİYARBAKIR'),
(22, 'EDİRNE'),
(23, 'ELAZIĞ'),
(24, 'ERZİNCAN'),
(25, 'ERZURUM'),
(26, 'ESKİŞEHİR'),
(27, 'GAZİANTEP'),
(28, 'GİRESUN'),
(29, 'GÜMÜŞHANE'),
(30, 'HAKKARİ'),
(31, 'HATAY'),
(32, 'ISPARTA'),
(33, 'İÇEL'),
(34, 'İSTANBUL'),
(35, 'İZMİR'),
(36, 'KARS'),
(37, 'KASTAMONU'),
(38, 'KAYSERİ'),
(39, 'KIRKLARELİ'),
(40, 'KIRŞEHİR'),
(41, 'KOCAELİ'),
(42, 'KONYA'),
(43, 'KÜTAHYA'),
(44, 'MALATYA'),
(45, 'MANİSA'),
(46, 'KAHRAMANMARAŞ'),
(47, 'MARDİN'),
(48, 'MUĞLA'),
(49, 'MUŞ'),
(50, 'NEVŞEHİR'),
(51, 'NİĞDE'),
(52, 'ORDU'),
(53, 'RİZE'),
(54, 'SAKARYA'),
(55, 'SAMSUN'),
(56, 'SİİRT'),
(57, 'SİNOP'),
(58, 'SİVAS'),
(59, 'TEKİRDAĞ'),
(60, 'TOKAT'),
(61, 'TRABZON'),
(62, 'TUNCELİ'),
(63, 'ŞANLIURFA'),
(64, 'UŞAK'),
(65, 'VAN'),
(66, 'YOZGAT'),
(67, 'ZONGULDAK'),
(68, 'AKSARAY'),
(69, 'BAYBURT'),
(70, 'KARAMAN'),
(71, 'KIRIKKALE'),
(72, 'BATMAN'),
(73, 'ŞIRNAK'),
(74, 'BARTIN'),
(75, 'ARDAHAN'),
(76, 'IĞDIR'),
(77, 'YALOVA'),
(78, 'KARABÜK'),
(79, 'KİLİS'),
(80, 'OSMANİYE'),
(81, 'DÜZCE');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kapakcinsi`
--

CREATE TABLE `kapakcinsi` (
  `KapakCinsiID` int(11) NOT NULL,
  `KapakCinsiName` varchar(255) DEFAULT NULL,
  `bKapakStandart` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kasahareketleri`
--

CREATE TABLE `kasahareketleri` (
  `KasaHareketID` int(11) NOT NULL,
  `AdminStatus` tinyint(11) NOT NULL DEFAULT 0,
  `UserID` int(11) DEFAULT NULL,
  `TahsilatID` int(11) DEFAULT 0,
  `KasaTip` tinyint(4) DEFAULT NULL,
  `IslemTipi` tinyint(4) DEFAULT NULL,
  `Detay` varchar(255) DEFAULT NULL,
  `KasaTutar` double(10,2) DEFAULT NULL,
  `KasaDesc` varchar(255) DEFAULT NULL,
  `TransferTarih` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kasatips`
--

CREATE TABLE `kasatips` (
  `KasaTipsID` int(11) NOT NULL,
  `KasaTip` tinyint(4) DEFAULT NULL,
  `KasaDetay` varchar(255) DEFAULT NULL,
  `CreadetDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kasatips`
--

INSERT INTO `kasatips` (`KasaTipsID`, `KasaTip`, `KasaDetay`, `CreadetDate`) VALUES
(1, 1, 'Nakit', '2023-11-03 01:48:49'),
(2, 1, 'Kredi Kartı', '2023-11-03 01:49:11'),
(3, 1, 'Banka Havalesi', '2023-11-03 01:49:26'),
(4, 1, 'Çek', '2023-11-03 01:49:39'),
(5, 1, 'Diğer', '2023-11-03 01:49:46'),
(6, 2, 'Kira', '2023-11-03 01:49:57'),
(7, 2, 'Elektrik', '2023-11-03 01:50:16'),
(8, 2, 'Telefon', '2023-11-03 01:53:13'),
(9, 2, 'Araç Yakıt Giderleri', '2023-11-03 01:53:25'),
(10, 2, 'Şube Yakıt (Aidat) Giderleri', '2023-11-03 01:53:44'),
(11, 2, 'Otoban Ücretleri', '2023-11-03 01:55:23'),
(12, 2, 'Montaj Giderleri', '2023-11-03 01:55:34'),
(13, 2, 'Mağaza Giderleri', '2023-11-03 01:55:43'),
(14, 2, 'Yemek Parası', '2023-11-03 01:55:58'),
(15, 2, 'Yol Parası', '2023-11-03 01:56:07'),
(16, 2, 'Prim', '2023-11-03 01:56:18'),
(17, 2, 'Maaş', '2023-11-03 01:57:42'),
(18, 2, 'Avans', '2023-11-03 01:57:48'),
(19, 2, 'Merkeze Gönderilen Para', '2023-11-03 01:58:20'),
(20, 2, 'Diğer Yerlere Giden', '2023-11-03 01:58:40'),
(21, 2, 'Avukata Verilen Senetler', '2023-11-03 01:58:51'),
(22, 2, 'İskonto', '2023-11-03 01:58:59'),
(23, 2, 'Kırtasiye', '2023-11-03 01:59:08'),
(24, 1, 'Sözleşme', '2023-11-03 02:11:20'),
(27, 2, 'Mal Alış', '2024-04-01 12:42:38'),
(28, 2, 'Banka Komisyon', '2024-04-01 17:06:30'),
(29, 2, 'Kasaya Nakit Girdi', '2024-05-10 03:06:29'),
(30, 2, 'Araç  Tamir', '2024-11-18 10:53:05'),
(31, 2, 'Su', '2024-11-18 12:12:47'),
(32, 2, 'Mutfak  ve Yemek Giderleri', '2024-11-18 13:09:49'),
(33, 2, 'Sosyal Medya ,Katolog,Reklam v.s.', '2024-11-18 15:26:24'),
(34, 2, 'Araç Sigortalar', '2024-11-18 15:35:11'),
(35, 2, 'Nakliye, Kargo v.s. giderleri', '2024-11-18 15:40:33'),
(36, 2, 'Ssk Ödemeleri', '2024-11-18 21:29:13'),
(37, 2, 'Stopaj Ödemeleri', '2024-11-18 21:29:42'),
(38, 2, 'Sair  Giderler', '2024-11-26 14:50:28'),
(39, 2, 'Kdv ödemeleri', '2024-11-26 15:04:25'),
(40, 2, 'Personel  Servıs', '2025-01-13 15:05:56'),
(41, 2, 'Banka  Krediler', '2025-11-19 12:52:19');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `log`
--

CREATE TABLE `log` (
  `LogID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `LogType` text DEFAULT NULL,
  `LogDesc` mediumtext DEFAULT NULL,
  `LogDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lognew`
--

CREATE TABLE `lognew` (
  `LogID` bigint(20) UNSIGNED NOT NULL,
  `LogType` tinyint(3) UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Log Tipi: 1=Hata, 2=Uyarı, 3=Admin İşlemi, 4=Bilgi, 5=Hata Ayıklama',
  `LogDesc` varchar(500) NOT NULL COMMENT 'İşlem Açıklaması',
  `LogStatus` enum('success','error','warning','pending','failed','info') DEFAULT 'info' COMMENT 'İşlem Durumu',
  `UserID` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'İşlemi yapan kullanıcı ID''si',
  `IpAddress` varchar(45) DEFAULT '0.0.0.0' COMMENT 'İstek yapılan IP adresi (IPv4 veya IPv6)',
  `UserAgent` varchar(255) DEFAULT NULL COMMENT 'Tarayıcı / İstemci bilgisi',
  `RequestMethod` varchar(10) DEFAULT 'GET' COMMENT 'HTTP Method: GET, POST, PUT, DELETE, PATCH',
  `RequestPath` varchar(255) DEFAULT NULL COMMENT 'İstek yapılan sayfa yolu (URI)',
  `OldValue` mediumtext DEFAULT NULL COMMENT 'Eski değer (JSON formatında)',
  `NewValue` mediumtext DEFAULT NULL COMMENT 'Yeni değer (JSON formatında)',
  `EntityType` varchar(100) DEFAULT NULL COMMENT 'İşlem yapılan tablo/varlık tipi: users, musteriler, sozlesmeler, vb.',
  `EntityID` int(10) UNSIGNED DEFAULT NULL COMMENT 'İşlem yapılan kaydın ID''si',
  `AdditionalData` mediumtext DEFAULT NULL COMMENT 'İlave veri (JSON): changed_fields, metadata, vb.',
  `LogDate` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Log oluşturulma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logtypes`
--

CREATE TABLE `logtypes` (
  `id` int(11) NOT NULL,
  `names` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `logtypes`
--

INSERT INTO `logtypes` (`id`, `names`) VALUES
(1, 'Sistem Girişi'),
(2, 'Kullanıcı İşlemleri'),
(3, 'Randevu İşlemleri'),
(4, 'Ürün İşlemleri'),
(5, 'Şube İşlemleri'),
(6, 'Raporlama İşlemleri'),
(7, 'Kullanıcı Grup İşlemleri'),
(8, 'Montaj Usta İşlemleri');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logtypesnew`
--

CREATE TABLE `logtypesnew` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `names` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT 'primary',
  `icon` varchar(50) DEFAULT 'fas fa-info-circle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `logtypesnew`
--

INSERT INTO `logtypesnew` (`id`, `names`, `description`, `color`, `icon`) VALUES
(1, 'Hata', 'Sistem hataları ve istisnalar', 'danger', 'fas fa-exclamation-circle'),
(2, 'Uyarı', 'Uyarı ve güvenlik olayları', 'warning', 'fas fa-exclamation-triangle'),
(3, 'İşlem', 'Admin ve sistem işlemleri', 'info', 'fas fa-check-circle'),
(4, 'Bilgi', 'Bilgilendirme ve raporlama', 'secondary', 'fas fa-info-circle'),
(5, 'Hata Ayıklama', 'Geliştirme ve hata ayıklama', 'dark', 'fas fa-bug');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `musteriler`
--

CREATE TABLE `musteriler` (
  `CustomerID` int(11) NOT NULL,
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
  `CreatedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `nakitkasa`
--

CREATE TABLE `nakitkasa` (
  `NakitKasaID` int(11) NOT NULL,
  `FirmaID` int(11) NOT NULL,
  `NakitKasaTutar` double(10,2) NOT NULL,
  `UpdateDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odemeler`
--

CREATE TABLE `odemeler` (
  `PayID` int(11) NOT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `OdemeAdet` int(11) DEFAULT NULL,
  `OdemeTutar` varchar(255) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `randevular`
--

CREATE TABLE `randevular` (
  `AppID` int(11) NOT NULL,
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
  `AppStatusUpdateDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tetikleyiciler `randevular`
--
DELIMITER $$
CREATE TRIGGER `update_appstatus_date` BEFORE UPDATE ON `randevular` FOR EACH ROW BEGIN
    IF NEW.AppStatus <> OLD.AppStatus THEN
        SET NEW.AppStatusUpdateDate = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `silinenrandevular`
--

CREATE TABLE `silinenrandevular` (
  `AppID` int(11) NOT NULL,
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
  `AppDesc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sozlesmeler`
--

CREATE TABLE `sozlesmeler` (
  `ContractID` int(11) NOT NULL,
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
  `CreatedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sozlesme_sablonlari`
--

CREATE TABLE `sozlesme_sablonlari` (
  `SozlesmeID` int(11) NOT NULL,
  `SozlesmeName` varchar(255) DEFAULT NULL,
  `SozlesmeIcerik` longtext DEFAULT NULL,
  `CreatedUserID` int(11) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UpdatedUserID` int(11) DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `subeler`
--

CREATE TABLE `subeler` (
  `BranchID` int(11) NOT NULL,
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
  `BranchLogo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tahsilat`
--

CREATE TABLE `tahsilat` (
  `TahsilatID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `BankaID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) NOT NULL DEFAULT 1,
  `OdemeTarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tahsilatcek`
--

CREATE TABLE `tahsilatcek` (
  `CekTahsilatID` int(11) NOT NULL,
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
  `OdemeTarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tahsilathavale`
--

CREATE TABLE `tahsilathavale` (
  `HavaleTahsilatID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `BankaID` int(11) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tahsilatkart`
--

CREATE TABLE `tahsilatkart` (
  `KartTahsilatID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `BankaID` int(11) DEFAULT NULL,
  `OdemeTarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tahsilatnakit`
--

CREATE TABLE `tahsilatnakit` (
  `NakitTahsilatID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `InstallmentID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `AlinanTutar` double(10,2) DEFAULT NULL,
  `OdemeTip` varchar(255) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `OdemeTarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `taksitler`
--

CREATE TABLE `taksitler` (
  `InstallmentID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `UserID` int(11) NOT NULL,
  `ContractID` int(11) DEFAULT NULL,
  `TaksitSayisi` int(11) DEFAULT NULL,
  `TaksitAdet` int(11) DEFAULT NULL,
  `TaksitTutar` double(10,2) DEFAULT NULL,
  `Durum` tinyint(4) DEFAULT 1,
  `TaksitTarih` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ticarifatura`
--

CREATE TABLE `ticarifatura` (
  `TicariFaturaID` int(11) NOT NULL,
  `FaturaNo` varchar(255) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `FirmaID` int(11) DEFAULT NULL,
  `FaturaTip` int(11) DEFAULT NULL,
  `FaturaTutar` double(10,2) DEFAULT NULL,
  `FaturaTarih` date DEFAULT NULL,
  `FaturaDosya` varchar(255) DEFAULT NULL,
  `FaturaDesc` varchar(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urungrup`
--

CREATE TABLE `urungrup` (
  `UrunGrupID` int(11) NOT NULL,
  `UrunGrupName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `ProductDesc` varchar(255) DEFAULT NULL,
  `ProductStatus` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `usergrup`
--

CREATE TABLE `usergrup` (
  `UserGrupID` int(11) NOT NULL,
  `UserGrupName` varchar(255) DEFAULT NULL,
  `UserGrupDesc` varchar(255) DEFAULT NULL,
  `UserGrupStatus` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `usergrup`
--

INSERT INTO `usergrup` (`UserGrupID`, `UserGrupName`, `UserGrupDesc`, `UserGrupStatus`) VALUES
(1, 'Admin', 'Yetkili Personel', 1),
(2, 'Satış Elemanı', 'Satış Personeli', 1),
(3, 'Depo Elemanı', 'Depo Personeli', 1),
(4, 'Montaj Elemanı', 'Montaj Ustası', 1),
(5, 'Genel Kontrol', 'Genel Kontrol', 1),
(6, 'Muhasebe Departmanı', 'Muhasebe Departmanı', 1),
(7, 'Planlama', 'Fabrika Ekibi', 1),
(8, 'İmalat', 'Fabrika Ekibi', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `UserName` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `UserMail` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `UserPhone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `UserPass` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `UserAuthority` tinyint(4) NOT NULL DEFAULT 1,
  `UserGroupID` tinyint(4) DEFAULT NULL,
  `BranchID` tinyint(4) DEFAULT NULL,
  `UserStatus` tinyint(4) DEFAULT 1,
  `UserPicture` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'dist/img/avatar5.png',
  `SessionMail` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `IpAdress` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`UserID`, `UserName`, `UserMail`, `UserPhone`, `UserPass`, `UserAuthority`, `UserGroupID`, `BranchID`, `UserStatus`, `UserPicture`, `SessionMail`, `IpAdress`) VALUES
(1, 'BİLAL ÖZGÜL', 'bilal@bilal.com', '02623350123', '$2y$12$AT6AxMA1mMgSWWRmm25p9Op1tFWZaVDQOVZ2ug/kCHIrZg8CJIxpS', 1, 1, 3, 1, 'dist/img/avatar5.png', '95567fc08b16a4c93b8931f86125d361', '::1');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `virmanhareket`
--

CREATE TABLE `virmanhareket` (
  `VirmanID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `SendBank` int(11) DEFAULT NULL,
  `ReceiveBank` int(11) DEFAULT NULL,
  `Amount` double DEFAULT NULL,
  `VirmanDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `vw_recent_logs`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `vw_recent_logs` (
`LogID` bigint(20) unsigned
,`LogType` tinyint(3) unsigned
,`LogTypeName` varchar(100)
,`LogDesc` varchar(500)
,`LogStatus` enum('success','error','warning','pending','failed','info')
,`UserID` int(11)
,`UserName` varchar(200)
,`LogDate` timestamp
,`IpAddress` varchar(45)
,`EntityType` varchar(100)
,`EntityID` int(10) unsigned
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `vw_today_summary`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `vw_today_summary` (
`LogType` tinyint(3) unsigned
,`LogStatus` enum('success','error','warning','pending','failed','info')
,`count` bigint(21)
,`log_date` date
);

-- --------------------------------------------------------

--
-- Görünüm yapısı `vw_recent_logs`
--
DROP TABLE IF EXISTS `vw_recent_logs`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_recent_logs`  AS SELECT `l`.`LogID` AS `LogID`, `l`.`LogType` AS `LogType`, `lt`.`names` AS `LogTypeName`, `l`.`LogDesc` AS `LogDesc`, `l`.`LogStatus` AS `LogStatus`, `u`.`UserID` AS `UserID`, `u`.`UserName` AS `UserName`, `l`.`LogDate` AS `LogDate`, `l`.`IpAddress` AS `IpAddress`, `l`.`EntityType` AS `EntityType`, `l`.`EntityID` AS `EntityID` FROM ((`lognew` `l` left join `users` `u` on(`l`.`UserID` = `u`.`UserID`)) left join `logtypesnew` `lt` on(`l`.`LogType` = `lt`.`id`)) ORDER BY `l`.`LogDate` DESC LIMIT 0, 1000 ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `vw_today_summary`
--
DROP TABLE IF EXISTS `vw_today_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_today_summary`  AS SELECT `lognew`.`LogType` AS `LogType`, `lognew`.`LogStatus` AS `LogStatus`, count(0) AS `count`, cast(`lognew`.`LogDate` as date) AS `log_date` FROM `lognew` WHERE cast(`lognew`.`LogDate` as date) = curdate() GROUP BY `lognew`.`LogType`, `lognew`.`LogStatus` ;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admincek`
--
ALTER TABLE `admincek`
  ADD PRIMARY KEY (`CekID`) USING BTREE;

--
-- Tablo için indeksler `adminkasa`
--
ALTER TABLE `adminkasa`
  ADD PRIMARY KEY (`AdminKasaID`) USING BTREE;

--
-- Tablo için indeksler `ayarlar`
--
ALTER TABLE `ayarlar`
  ADD PRIMARY KEY (`MetaID`) USING BTREE;

--
-- Tablo için indeksler `bankalar`
--
ALTER TABLE `bankalar`
  ADD PRIMARY KEY (`BankID`);

--
-- Tablo için indeksler `dolapboy`
--
ALTER TABLE `dolapboy`
  ADD PRIMARY KEY (`DolapBoyID`) USING BTREE;

--
-- Tablo için indeksler `dosyalar`
--
ALTER TABLE `dosyalar`
  ADD PRIMARY KEY (`FileID`);

--
-- Tablo için indeksler `duyurular`
--
ALTER TABLE `duyurular`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `edosyalar`
--
ALTER TABLE `edosyalar`
  ADD PRIMARY KEY (`NonFileID`) USING BTREE;

--
-- Tablo için indeksler `faturafile`
--
ALTER TABLE `faturafile`
  ADD PRIMARY KEY (`FaturaID`);

--
-- Tablo için indeksler `faturalar`
--
ALTER TABLE `faturalar`
  ADD PRIMARY KEY (`PrintID`) USING BTREE;

--
-- Tablo için indeksler `firmalar`
--
ALTER TABLE `firmalar`
  ADD PRIMARY KEY (`FirmaID`),
  ADD KEY `idx_firmalar_firmaid` (`FirmaID`);

--
-- Tablo için indeksler `govdecinsi`
--
ALTER TABLE `govdecinsi`
  ADD PRIMARY KEY (`GovdeCinsiID`) USING BTREE;

--
-- Tablo için indeksler `govderenk`
--
ALTER TABLE `govderenk`
  ADD PRIMARY KEY (`GovdeRenkID`) USING BTREE;

--
-- Tablo için indeksler `ilceler`
--
ALTER TABLE `ilceler`
  ADD PRIMARY KEY (`ilce_id`) USING BTREE;

--
-- Tablo için indeksler `iller`
--
ALTER TABLE `iller`
  ADD PRIMARY KEY (`iller_id`) USING BTREE;

--
-- Tablo için indeksler `kapakcinsi`
--
ALTER TABLE `kapakcinsi`
  ADD PRIMARY KEY (`KapakCinsiID`) USING BTREE;

--
-- Tablo için indeksler `kasahareketleri`
--
ALTER TABLE `kasahareketleri`
  ADD PRIMARY KEY (`KasaHareketID`),
  ADD KEY `idx_kasahareketleri_islemtipi` (`IslemTipi`),
  ADD KEY `idx_kasahareketleri_firma_kasatip_islemtipi` (`KasaTip`,`IslemTipi`,`Detay`,`TransferTarih`);

--
-- Tablo için indeksler `kasatips`
--
ALTER TABLE `kasatips`
  ADD PRIMARY KEY (`KasaTipsID`) USING BTREE,
  ADD KEY `idx_kasatips_kasatipsid` (`KasaTipsID`);

--
-- Tablo için indeksler `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`LogID`) USING BTREE;

--
-- Tablo için indeksler `lognew`
--
ALTER TABLE `lognew`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `idx_user_id` (`UserID`),
  ADD KEY `idx_log_type` (`LogType`),
  ADD KEY `idx_log_status` (`LogStatus`),
  ADD KEY `idx_entity` (`EntityType`,`EntityID`),
  ADD KEY `idx_log_date` (`LogDate`),
  ADD KEY `idx_ip_address` (`IpAddress`),
  ADD KEY `idx_log_type_date` (`LogType`,`LogDate`),
  ADD KEY `idx_user_date` (`UserID`,`LogDate`);
ALTER TABLE `lognew` ADD FULLTEXT KEY `ft_log_desc` (`LogDesc`);

--
-- Tablo için indeksler `logtypes`
--
ALTER TABLE `logtypes`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `logtypesnew`
--
ALTER TABLE `logtypesnew`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `musteriler`
--
ALTER TABLE `musteriler`
  ADD PRIMARY KEY (`CustomerID`) USING BTREE;

--
-- Tablo için indeksler `nakitkasa`
--
ALTER TABLE `nakitkasa`
  ADD PRIMARY KEY (`NakitKasaID`);

--
-- Tablo için indeksler `odemeler`
--
ALTER TABLE `odemeler`
  ADD PRIMARY KEY (`PayID`) USING BTREE;

--
-- Tablo için indeksler `randevular`
--
ALTER TABLE `randevular`
  ADD PRIMARY KEY (`AppID`) USING BTREE;

--
-- Tablo için indeksler `silinenrandevular`
--
ALTER TABLE `silinenrandevular`
  ADD PRIMARY KEY (`AppID`) USING BTREE;

--
-- Tablo için indeksler `sozlesmeler`
--
ALTER TABLE `sozlesmeler`
  ADD PRIMARY KEY (`ContractID`) USING BTREE;

--
-- Tablo için indeksler `sozlesme_sablonlari`
--
ALTER TABLE `sozlesme_sablonlari`
  ADD PRIMARY KEY (`SozlesmeID`) USING BTREE;

--
-- Tablo için indeksler `subeler`
--
ALTER TABLE `subeler`
  ADD PRIMARY KEY (`BranchID`) USING BTREE,
  ADD KEY `idx_subeler_branchid_firmaid` (`BranchID`,`FirmaID`);

--
-- Tablo için indeksler `tahsilat`
--
ALTER TABLE `tahsilat`
  ADD PRIMARY KEY (`TahsilatID`),
  ADD KEY `idx_tahsilat_installmentid` (`InstallmentID`),
  ADD KEY `idx_tahsilat_tahsilatid_installmentid` (`TahsilatID`,`InstallmentID`,`CustomerID`,`AlinanTutar`);

--
-- Tablo için indeksler `tahsilatcek`
--
ALTER TABLE `tahsilatcek`
  ADD PRIMARY KEY (`CekTahsilatID`);

--
-- Tablo için indeksler `tahsilathavale`
--
ALTER TABLE `tahsilathavale`
  ADD PRIMARY KEY (`HavaleTahsilatID`),
  ADD KEY `idx_tahsilathavale_installmentid_tutar` (`InstallmentID`,`AlinanTutar`);

--
-- Tablo için indeksler `tahsilatkart`
--
ALTER TABLE `tahsilatkart`
  ADD PRIMARY KEY (`KartTahsilatID`),
  ADD KEY `idx_tahsilatkart_installmentid_tutar` (`InstallmentID`,`AlinanTutar`);

--
-- Tablo için indeksler `tahsilatnakit`
--
ALTER TABLE `tahsilatnakit`
  ADD PRIMARY KEY (`NakitTahsilatID`);

--
-- Tablo için indeksler `taksitler`
--
ALTER TABLE `taksitler`
  ADD PRIMARY KEY (`InstallmentID`);

--
-- Tablo için indeksler `ticarifatura`
--
ALTER TABLE `ticarifatura`
  ADD PRIMARY KEY (`TicariFaturaID`);

--
-- Tablo için indeksler `urungrup`
--
ALTER TABLE `urungrup`
  ADD PRIMARY KEY (`UrunGrupID`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`ProductID`) USING BTREE;

--
-- Tablo için indeksler `usergrup`
--
ALTER TABLE `usergrup`
  ADD PRIMARY KEY (`UserGrupID`) USING BTREE;

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`) USING BTREE,
  ADD UNIQUE KEY `kul_mail` (`UserMail`) USING BTREE,
  ADD KEY `idx_users_userid_branchid` (`UserID`,`BranchID`);

--
-- Tablo için indeksler `virmanhareket`
--
ALTER TABLE `virmanhareket`
  ADD PRIMARY KEY (`VirmanID`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admincek`
--
ALTER TABLE `admincek`
  MODIFY `CekID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `adminkasa`
--
ALTER TABLE `adminkasa`
  MODIFY `AdminKasaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `ayarlar`
--
ALTER TABLE `ayarlar`
  MODIFY `MetaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `bankalar`
--
ALTER TABLE `bankalar`
  MODIFY `BankID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dolapboy`
--
ALTER TABLE `dolapboy`
  MODIFY `DolapBoyID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dosyalar`
--
ALTER TABLE `dosyalar`
  MODIFY `FileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `duyurular`
--
ALTER TABLE `duyurular`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `edosyalar`
--
ALTER TABLE `edosyalar`
  MODIFY `NonFileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `faturafile`
--
ALTER TABLE `faturafile`
  MODIFY `FaturaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `faturalar`
--
ALTER TABLE `faturalar`
  MODIFY `PrintID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `firmalar`
--
ALTER TABLE `firmalar`
  MODIFY `FirmaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Tablo için AUTO_INCREMENT değeri `govdecinsi`
--
ALTER TABLE `govdecinsi`
  MODIFY `GovdeCinsiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `govderenk`
--
ALTER TABLE `govderenk`
  MODIFY `GovdeRenkID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `ilceler`
--
ALTER TABLE `ilceler`
  MODIFY `ilce_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=974;

--
-- Tablo için AUTO_INCREMENT değeri `iller`
--
ALTER TABLE `iller`
  MODIFY `iller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Tablo için AUTO_INCREMENT değeri `kapakcinsi`
--
ALTER TABLE `kapakcinsi`
  MODIFY `KapakCinsiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kasahareketleri`
--
ALTER TABLE `kasahareketleri`
  MODIFY `KasaHareketID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kasatips`
--
ALTER TABLE `kasatips`
  MODIFY `KasaTipsID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Tablo için AUTO_INCREMENT değeri `log`
--
ALTER TABLE `log`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `lognew`
--
ALTER TABLE `lognew`
  MODIFY `LogID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `logtypes`
--
ALTER TABLE `logtypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `musteriler`
--
ALTER TABLE `musteriler`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `nakitkasa`
--
ALTER TABLE `nakitkasa`
  MODIFY `NakitKasaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `odemeler`
--
ALTER TABLE `odemeler`
  MODIFY `PayID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `randevular`
--
ALTER TABLE `randevular`
  MODIFY `AppID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `silinenrandevular`
--
ALTER TABLE `silinenrandevular`
  MODIFY `AppID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sozlesmeler`
--
ALTER TABLE `sozlesmeler`
  MODIFY `ContractID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sozlesme_sablonlari`
--
ALTER TABLE `sozlesme_sablonlari`
  MODIFY `SozlesmeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `subeler`
--
ALTER TABLE `subeler`
  MODIFY `BranchID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tahsilat`
--
ALTER TABLE `tahsilat`
  MODIFY `TahsilatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tahsilatcek`
--
ALTER TABLE `tahsilatcek`
  MODIFY `CekTahsilatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tahsilathavale`
--
ALTER TABLE `tahsilathavale`
  MODIFY `HavaleTahsilatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tahsilatkart`
--
ALTER TABLE `tahsilatkart`
  MODIFY `KartTahsilatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tahsilatnakit`
--
ALTER TABLE `tahsilatnakit`
  MODIFY `NakitTahsilatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `taksitler`
--
ALTER TABLE `taksitler`
  MODIFY `InstallmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `ticarifatura`
--
ALTER TABLE `ticarifatura`
  MODIFY `TicariFaturaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `urungrup`
--
ALTER TABLE `urungrup`
  MODIFY `UrunGrupID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `usergrup`
--
ALTER TABLE `usergrup`
  MODIFY `UserGrupID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `virmanhareket`
--
ALTER TABLE `virmanhareket`
  MODIFY `VirmanID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
