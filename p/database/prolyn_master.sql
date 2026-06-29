-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: prolyn_master
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
-- Table structure for table `abonelik_gecmisi`
--

DROP TABLE IF EXISTS `abonelik_gecmisi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abonelik_gecmisi` (
  `history_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Geçmiş kayıt benzersiz kimliği',
  `subscription_id` int(11) NOT NULL COMMENT 'İlgili abonelik ID',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `old_plan_name` varchar(100) DEFAULT NULL COMMENT 'Önceki paket adı',
  `new_plan_name` varchar(100) DEFAULT NULL COMMENT 'Geçiş yapılan yeni paket adı',
  `change_type` enum('upgrade','downgrade','renewal','cancellation','trial_start') NOT NULL COMMENT 'Değişim türü: Yükseltme, Düşürme, Yenileme, İptal, Deneme Başlatma',
  `reason` text DEFAULT NULL COMMENT 'Değişiklik nedeni veya sistem notu',
  `changed_by` bigint(20) unsigned DEFAULT NULL COMMENT 'İşlemi gerçekleştiren yönetici ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'İşlem tarihi',
  PRIMARY KEY (`history_id`),
  KEY `idx_subscription_id` (`subscription_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_changed_by` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilerin abonelik değişim ve geçmiş kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `abonelik_gecmisi`
--

LOCK TABLES `abonelik_gecmisi` WRITE;
/*!40000 ALTER TABLE `abonelik_gecmisi` DISABLE KEYS */;
/*!40000 ALTER TABLE `abonelik_gecmisi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `abonelikler`
--

DROP TABLE IF EXISTS `abonelikler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abonelikler` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Subscription id Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `plan_name` enum('basic','pro','enterprise') NOT NULL COMMENT 'Plan name Bilgisi',
  `num_branches` int(11) NOT NULL COMMENT 'Num branches Bilgisi',
  `price_per_month` decimal(10,2) NOT NULL COMMENT 'Price per month Bilgisi',
  `total_price` decimal(10,2) NOT NULL COMMENT 'Total price Bilgisi',
  `billing_cycle` int(11) NOT NULL COMMENT 'Billing cycle Bilgisi',
  `start_date` date NOT NULL COMMENT 'Başlangıç Tarihi',
  `end_date` date NOT NULL COMMENT 'Bitiş Tarihi',
  `next_renewal_date` date DEFAULT NULL COMMENT 'Next renewal date Bilgisi',
  `auto_renew` tinyint(1) DEFAULT 1 COMMENT 'Auto renew Bilgisi',
  `renewal_notified` tinyint(1) DEFAULT 0 COMMENT 'Renewal notified Bilgisi',
  `final_reminder_sent` tinyint(1) DEFAULT 0 COMMENT 'Final reminder sent Bilgisi',
  `status` varchar(50) DEFAULT NULL COMMENT 'Abonelik durumu (active, cancelled, expired vb.)',
  `cancellation_reason` text DEFAULT NULL COMMENT 'Cancellation reason Bilgisi',
  `cancelled_date` timestamp NULL DEFAULT NULL COMMENT 'Cancelled date Bilgisi',
  `last_payment_date` timestamp NULL DEFAULT NULL COMMENT 'Last payment date Bilgisi',
  `last_payment_id` int(11) DEFAULT NULL COMMENT 'Last payment id Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`subscription_id`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_status` (`status`),
  KEY `idx_auto_renew` (`auto_renew`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_plan_name` (`plan_name`),
  KEY `idx_status_end_date` (`status`,`end_date`),
  KEY `idx_auto_renew_date` (`auto_renew`,`next_renewal_date`),
  KEY `idx_sub_status_end` (`status`,`end_date`),
  KEY `idx_sub_auto_renew` (`auto_renew`,`next_renewal_date`),
  KEY `idx_customer_status` (`customer_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri abonelik planları ve süre bilgilerinin tutulduğu tablo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `abonelikler`
--

LOCK TABLES `abonelikler` WRITE;
/*!40000 ALTER TABLE `abonelikler` DISABLE KEYS */;
INSERT INTO `abonelikler` VALUES (4,9,'basic',1,100.00,1200.00,12,'2026-04-28','2027-04-28',NULL,1,0,0,'active',NULL,NULL,NULL,NULL,'2026-06-28 16:50:35','2026-06-28 16:50:35'),(5,10,'pro',2,250.00,3000.00,12,'2025-12-28','2026-12-28',NULL,1,0,0,'active',NULL,NULL,NULL,NULL,'2026-06-28 16:50:35','2026-06-28 16:50:35'),(6,11,'enterprise',5,1000.00,12000.00,12,'2026-05-28','2027-05-28',NULL,1,0,0,'active',NULL,NULL,NULL,NULL,'2026-06-28 16:50:35','2026-06-28 16:50:35');
/*!40000 ALTER TABLE `abonelikler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aktivite_loglari`
--

DROP TABLE IF EXISTS `aktivite_loglari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aktivite_loglari` (
  `activity_id` bigint(20) NOT NULL COMMENT 'Aktivite kayıt ID',
  `customer_id` int(11) DEFAULT NULL COMMENT 'İlgili müşteri ID',
  `user_id` int(11) DEFAULT NULL COMMENT 'İşlemi yapan kullanıcı ID',
  `action` varchar(100) DEFAULT NULL COMMENT 'Yapılan işlem anahtarı',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'İşlem yapılan nesne tipi',
  `entity_id` int(11) DEFAULT NULL COMMENT 'İşlem yapılan nesne ID',
  `description` varchar(500) DEFAULT NULL COMMENT 'İşlem açıklaması',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP adresi',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Tarayıcı bilgisi',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ek işlem verileri (JSON)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Kayıt tarihi',
  PRIMARY KEY (`activity_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_customer_date` (`customer_id`,`created_at`),
  KEY `idx_customer_action` (`customer_id`,`action`),
  KEY `idx_action_date` (`action`,`created_at`),
  CONSTRAINT `fk_activity_logs_customer` FOREIGN KEY (`customer_id`) REFERENCES `kurumlar` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `kurum_kullanicilari` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kullanıcı ve sistem genelindeki tüm işlem aktivitelerinin kaydı';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aktivite_loglari`
--

LOCK TABLES `aktivite_loglari` WRITE;
/*!40000 ALTER TABLE `aktivite_loglari` DISABLE KEYS */;
/*!40000 ALTER TABLE `aktivite_loglari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_anahtarlari`
--

DROP TABLE IF EXISTS `api_anahtarlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_anahtarlari` (
  `api_key_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Api key id Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `api_key` varchar(64) NOT NULL COMMENT 'Api key Bilgisi',
  `api_secret` varchar(64) DEFAULT NULL COMMENT 'Api secret Bilgisi',
  `name` varchar(100) DEFAULT NULL COMMENT 'İsim',
  `permissions` text DEFAULT NULL COMMENT 'JSON: read, write, delete',
  `rate_limit` int(11) DEFAULT 1000 COMMENT 'Rate limit Bilgisi',
  `requests_used` int(11) DEFAULT 0 COMMENT 'Requests used Bilgisi',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Last used at Bilgisi',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Expires at Bilgisi',
  `status` enum('active','revoked','expired') DEFAULT NULL COMMENT 'API anahtar durumu: active = aktif, revoked = iptal, expired = süresi dolmuş',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`api_key_id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_customer_status` (`customer_id`,`status`),
  KEY `idx_api_key_status` (`status`),
  KEY `idx_api_customer_status` (`customer_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri ve sistem entegrasyonları için API anahtarları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_anahtarlari`
--

LOCK TABLES `api_anahtarlari` WRITE;
/*!40000 ALTER TABLE `api_anahtarlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_anahtarlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bakim_loglari`
--

DROP TABLE IF EXISTS `bakim_loglari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bakim_loglari` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Bakım kayıt ID',
  `title` varchar(255) NOT NULL COMMENT 'Bakım başlığı veya kısa açıklama',
  `description` text DEFAULT NULL COMMENT 'Bakım kapsamı ve detaylı bilgi',
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled' COMMENT 'Bakım durumu: Planlandı, Devam Ediyor, Tamamlandı, İptal Edildi',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium' COMMENT 'Önem derecesi',
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Planlanan başlangıç zamanı',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Tahmini bitiş zamanı',
  `is_active` tinyint(1) DEFAULT 0 COMMENT 'Şu an aktif olarak erişim kısıtlanıyor mu? (1: Evet, 0: Hayır)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kaydın oluşturulma tarihi',
  PRIMARY KEY (`id`),
  KEY `idx_status_active` (`status`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem genel bakım ve teknik çalışma takvimi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bakim_loglari`
--

LOCK TABLES `bakim_loglari` WRITE;
/*!40000 ALTER TABLE `bakim_loglari` DISABLE KEYS */;
/*!40000 ALTER TABLE `bakim_loglari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bildirim_kuyrugu`
--

DROP TABLE IF EXISTS `bildirim_kuyrugu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bildirim_kuyrugu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `channel` varchar(20) NOT NULL COMMENT 'Bildirim kanalı (email, sms, push, webhook)',
  `recipient` varchar(255) NOT NULL COMMENT 'Alıcı bilgisi',
  `subject` varchar(255) DEFAULT NULL COMMENT 'Başlık',
  `message` text NOT NULL COMMENT 'Mesaj içeriği',
  `status` varchar(20) DEFAULT 'pending' COMMENT 'Gönderim durumu',
  `attempts` tinyint(3) unsigned DEFAULT 0 COMMENT 'Gönderim deneme sayısı',
  `last_error` text DEFAULT NULL COMMENT 'Son hata mesajı',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'Sent at Bilgisi',
  PRIMARY KEY (`id`),
  KEY `idx_notification_status` (`status`),
  KEY `idx_status_created` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem bildirimlerinin kuyruk mantığıyla işlendiği tablo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bildirim_kuyrugu`
--

LOCK TABLES `bildirim_kuyrugu` WRITE;
/*!40000 ALTER TABLE `bildirim_kuyrugu` DISABLE KEYS */;
/*!40000 ALTER TABLE `bildirim_kuyrugu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bildirimler`
--

DROP TABLE IF EXISTS `bildirimler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bildirimler` (
  `notification_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Notification id Bilgisi',
  `admin_id` int(11) DEFAULT NULL COMMENT 'Yönetici ID',
  `title` varchar(255) NOT NULL COMMENT 'Başlık',
  `message` text NOT NULL COMMENT 'Message Bilgisi',
  `type` enum('info','success','warning','error') DEFAULT NULL COMMENT 'Bildirim türü: bilgi, başarı, uyarı, hata',
  `is_read` tinyint(1) DEFAULT 0 COMMENT 'Is read Bilgisi',
  `related_entity_type` varchar(50) DEFAULT NULL COMMENT 'Related entity type Bilgisi',
  `related_entity_id` int(11) DEFAULT NULL COMMENT 'Related entity id Bilgisi',
  `action_url` varchar(255) DEFAULT NULL COMMENT 'Action url Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Read at Bilgisi',
  PRIMARY KEY (`notification_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_notifications_admin_read_date` (`admin_id`,`is_read`,`created_at`),
  KEY `idx_admin_unread` (`admin_id`,`is_read`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin ve kullanıcı sistem bildirimleri';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bildirimler`
--

LOCK TABLES `bildirimler` WRITE;
/*!40000 ALTER TABLE `bildirimler` DISABLE KEYS */;
/*!40000 ALTER TABLE `bildirimler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!50001 DROP VIEW IF EXISTS `customers`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `customers` AS SELECT
 1 AS `customer_id`,
  1 AS `customer_code`,
  1 AS `company_name`,
  1 AS `company_email`,
  1 AS `company_phone`,
  1 AS `contact_person`,
  1 AS `tax_office`,
  1 AS `tax_number`,
  1 AS `billing_address`,
  1 AS `account_manager_id`,
  1 AS `tenant_alias`,
  1 AS `company_address`,
  1 AS `industry`,
  1 AS `musteri_paketi`,
  1 AS `database_name`,
  1 AS `database_password`,
  1 AS `status`,
  1 AS `trial_start_date`,
  1 AS `trial_end_date`,
  1 AS `sozlesme_bitis_tarihi`,
  1 AS `logo_url`,
  1 AS `contact_name`,
  1 AS `contact_email`,
  1 AS `contact_phone`,
  1 AS `created_at`,
  1 AS `updated_at`,
  1 AS `deleted_at`,
  1 AS `created_by`,
  1 AS `allowed_modules` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `denetim_loglari`
--

DROP TABLE IF EXISTS `denetim_loglari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `denetim_loglari` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `user_type` varchar(20) NOT NULL COMMENT 'İşlemi yapan kullanıcı tipi (admin, customer, system)',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'İşlemi yapan kullanıcı ID',
  `action` varchar(50) NOT NULL COMMENT 'Yapılan işlem (create, update, delete, login vb.)',
  `table_name` varchar(100) NOT NULL COMMENT 'İşlem yapılan tablo adı',
  `record_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Etkilenen kayıt ID',
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'İşlem öncesi veri',
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'İşlem sonrası veri',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Kullanıcı IP adresi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'İşlem zamanı',
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_table` (`table_name`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_table_record` (`table_name`,`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemdeki kritik değişiklikleri ve güvenlik olaylarını izleyen denetim kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `denetim_loglari`
--

LOCK TABLES `denetim_loglari` WRITE;
/*!40000 ALTER TABLE `denetim_loglari` DISABLE KEYS */;
/*!40000 ALTER TABLE `denetim_loglari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destek_cevaplari`
--

DROP TABLE IF EXISTS `destek_cevaplari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destek_cevaplari` (
  `reply_id` bigint(20) NOT NULL COMMENT 'Yanıt benzersiz ID',
  `ticket_id` int(11) DEFAULT NULL COMMENT 'Bağlı olduğu destek talebi ID',
  `replied_by_admin` int(11) DEFAULT NULL COMMENT 'Yanıtlayan admin ID (varsa)',
  `replied_by_customer` int(11) DEFAULT NULL COMMENT 'Yanıtlayan müşteri kullanıcı ID (varsa)',
  `message` text DEFAULT NULL COMMENT 'Yanıt mesaj içeriği',
  `is_internal` tinyint(1) DEFAULT NULL COMMENT '1: Dahili (sadece admin), 0: Müşteriye açık',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Yanıta eklenen dosyalar (JSON)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Yanıt oluşturulma tarihi',
  PRIMARY KEY (`reply_id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_replied_by_admin` (`replied_by_admin`),
  KEY `idx_replied_by_customer` (`replied_by_customer`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ticket_date` (`ticket_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Destek taleplerine (biletlere) verilen cevapların ve yazışmaların tutulduğu tablo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destek_cevaplari`
--

LOCK TABLES `destek_cevaplari` WRITE;
/*!40000 ALTER TABLE `destek_cevaplari` DISABLE KEYS */;
/*!40000 ALTER TABLE `destek_cevaplari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destek_talepleri`
--

DROP TABLE IF EXISTS `destek_talepleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destek_talepleri` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Ticket id Bilgisi',
  `ticket_number` varchar(50) NOT NULL COMMENT 'Ticket number Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `subject` varchar(255) NOT NULL COMMENT 'Subject Bilgisi',
  `description` text NOT NULL COMMENT 'Açıklama',
  `priority` varchar(50) DEFAULT NULL COMMENT 'Destek talebi önceliği (low, medium, high)',
  `status` varchar(50) DEFAULT NULL COMMENT 'Destek talebinin durumu (open, pending, closed vb.)',
  `assigned_to` bigint(20) unsigned DEFAULT NULL COMMENT 'Assigned to Bilgisi',
  `created_by` int(11) DEFAULT NULL COMMENT 'Kaydı Oluşturan Yönetici ID',
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT 'Resolved at Bilgisi',
  `closed_at` timestamp NULL DEFAULT NULL COMMENT 'Closed at Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ticket_id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_customer_status_priority` (`customer_id`,`status`,`priority`),
  FULLTEXT KEY `ft_ticket_search` (`subject`,`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri destek taleplerinin ve durumlarının tutulduğu tablo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destek_talepleri`
--

LOCK TABLES `destek_talepleri` WRITE;
/*!40000 ALTER TABLE `destek_talepleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `destek_talepleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dosyalar`
--

DROP TABLE IF EXISTS `dosyalar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dosyalar` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `owner_type` varchar(20) NOT NULL COMMENT 'Dosya sahibi tipi',
  `owner_id` bigint(20) unsigned NOT NULL COMMENT 'Dosya sahibi ID',
  `file_path` varchar(255) NOT NULL COMMENT 'Dosya yolu',
  `file_name` varchar(255) NOT NULL COMMENT 'Dosya adı',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'Dosya tipi',
  `file_size` bigint(20) unsigned DEFAULT NULL COMMENT 'Dosya boyutu (byte)',
  `storage` varchar(20) DEFAULT 'local' COMMENT 'Depolama türü (local, s3)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_files_owner` (`owner_type`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sisteme yüklenen dosyaların metadata bilgileri';
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
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Announcement id Bilgisi',
  `title` varchar(255) NOT NULL COMMENT 'Başlık',
  `content` text NOT NULL COMMENT 'İçerik',
  `type` enum('info','success','warning','danger') DEFAULT NULL COMMENT 'Duyuru türü: bilgi, başarı, uyarı, kritik',
  `target_audience` enum('all','admins','customers','specific') DEFAULT NULL COMMENT 'Hedef kitle tanımı',
  `target_customer_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of customer IDs if specific',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Is active Bilgisi',
  `show_from` timestamp NULL DEFAULT NULL COMMENT 'Show from Bilgisi',
  `show_until` timestamp NULL DEFAULT NULL COMMENT 'Show until Bilgisi',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Kaydı Oluşturan Yönetici ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`announcement_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_show_dates` (`show_from`,`show_until`),
  KEY `idx_target_audience` (`target_audience`),
  KEY `idx_active_dates` (`is_active`,`show_from`,`show_until`),
  KEY `fk_announcements_admin` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem geneli veya belirli müşterilere yönelik yapılan duyuru ve bilgilendirmeler';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `duyurular`
--

LOCK TABLES `duyurular` WRITE;
/*!40000 ALTER TABLE `duyurular` DISABLE KEYS */;
/*!40000 ALTER TABLE `duyurular` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eposta_kuyrugu`
--

DROP TABLE IF EXISTS `eposta_kuyrugu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eposta_kuyrugu` (
  `queue_id` bigint(20) NOT NULL COMMENT 'E-posta kuyruk ID',
  `template_key` varchar(100) DEFAULT NULL COMMENT 'Kullanılan e-posta şablon anahtarı',
  `to_email` varchar(255) DEFAULT NULL COMMENT 'Alıcı e-posta adresi',
  `to_name` varchar(255) DEFAULT NULL COMMENT 'Alıcı adı',
  `subject` varchar(255) DEFAULT NULL COMMENT 'E-posta konusu',
  `body_html` text DEFAULT NULL COMMENT 'HTML e-posta içeriği',
  `body_text` text DEFAULT NULL COMMENT 'Düz metin e-posta içeriği',
  `cc` varchar(500) DEFAULT NULL COMMENT 'Comma-separated emails',
  `bcc` varchar(500) DEFAULT NULL COMMENT 'Comma-separated emails',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'E-posta ekleri',
  `priority` enum('low','normal','high','urgent') DEFAULT NULL COMMENT 'Öncelik: düşük, normal, yüksek, acil',
  `status` enum('pending','sent','failed','cancelled') DEFAULT NULL COMMENT 'Durum: beklemede, gönderildi, hatalı, iptal',
  `attempts` int(11) DEFAULT NULL COMMENT 'Gönderim deneme sayısı',
  `max_attempts` int(11) DEFAULT NULL COMMENT 'Maksimum deneme sayısı',
  `last_error` text DEFAULT NULL COMMENT 'Son hata mesajı',
  `scheduled_at` timestamp NULL DEFAULT NULL COMMENT 'Planlanan gönderim zamanı',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'Gerçek gönderim zamanı',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`queue_id`),
  KEY `idx_status` (`status`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_priority_status` (`priority`,`status`),
  KEY `idx_to_email` (`to_email`),
  KEY `idx_pending_scheduled` (`status`,`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gönderilecek e-postaların sıraya alındığı ve gönderim durumlarının tutulduğu kuyruk tablosu';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eposta_kuyrugu`
--

LOCK TABLES `eposta_kuyrugu` WRITE;
/*!40000 ALTER TABLE `eposta_kuyrugu` DISABLE KEYS */;
/*!40000 ALTER TABLE `eposta_kuyrugu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eposta_sablonlari`
--

DROP TABLE IF EXISTS `eposta_sablonlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eposta_sablonlari` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Template id Bilgisi',
  `template_key` varchar(100) NOT NULL COMMENT 'Template key Bilgisi',
  `template_name` varchar(255) NOT NULL COMMENT 'Template name Bilgisi',
  `subject` varchar(255) NOT NULL COMMENT 'Subject Bilgisi',
  `body_html` text NOT NULL COMMENT 'Body html Bilgisi',
  `body_text` text DEFAULT NULL COMMENT 'Body text Bilgisi',
  `variables` text DEFAULT NULL COMMENT 'JSON: Available variables like {{company_name}}',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT 'Kayıt Durumu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `template_key` (`template_key`),
  FULLTEXT KEY `ft_template_search` (`template_name`,`subject`),
  FULLTEXT KEY `ft_email_template` (`template_name`,`subject`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemde kullanılan e-posta şablonları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eposta_sablonlari`
--

LOCK TABLES `eposta_sablonlari` WRITE;
/*!40000 ALTER TABLE `eposta_sablonlari` DISABLE KEYS */;
INSERT INTO `eposta_sablonlari` VALUES (1,'welcome_email','Hoş Geldiniz','{{company_name}} - Hoş Geldiniz!','<h1>Merhaba {{contact_name}},</h1><p>{{company_name}} ailesine hoş geldiniz!</p>','Merhaba {{contact_name}}, {{company_name}} ailesine hoş geldiniz!','{\"company_name\": \"Şirket Adı\", \"contact_name\": \"İletişim Adı\"}','active','2025-12-21 20:36:32','2025-12-21 20:36:32'),(2,'trial_ending','Deneme Süresi Bitiyor','Deneme süreniz {{days_left}} gün içinde sona eriyor','<h1>Merhaba {{contact_name}},</h1><p>Deneme süreniz {{days_left}} gün içinde sona erecek.</p>','Merhaba {{contact_name}}, Deneme süreniz {{days_left}} gün içinde sona erecek.','{\"contact_name\": \"İletişim Adı\", \"days_left\": \"Kalan Gün\"}','active','2025-12-21 20:36:32','2025-12-21 20:36:32'),(3,'payment_failed','Ödeme Başarısız','Ödemeniz Başarısız Oldu','<h1>Merhaba {{contact_name}},</h1><p>{{amount}} TL tutarındaki ödemeniz başarısız oldu.</p>','Merhaba {{contact_name}}, {{amount}} TL tutarındaki ödemeniz başarısız oldu.','{\"contact_name\": \"İletişim Adı\", \"amount\": \"Tutar\"}','active','2025-12-21 20:36:32','2025-12-21 20:36:32'),(4,'subscription_renewed','Abonelik Yenilendi','Aboneliğiniz Başarıyla Yenilendi','<h1>Merhaba {{contact_name}},</h1><p>{{plan_name}} paketiniz {{end_date}} tarihine kadar yenilendi.</p>','Merhaba {{contact_name}}, {{plan_name}} paketiniz {{end_date}} tarihine kadar yenilendi.','{\"contact_name\": \"İletişim Adı\", \"plan_name\": \"Paket Adı\", \"end_date\": \"Bitiş Tarihi\"}','active','2025-12-21 20:36:32','2025-12-21 20:36:32');
/*!40000 ALTER TABLE `eposta_sablonlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fatura_adresleri`
--

DROP TABLE IF EXISTS `fatura_adresleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fatura_adresleri` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Address id Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `address_type` enum('billing','shipping') DEFAULT NULL COMMENT 'Adres türü: fatura veya teslimat',
  `company_name` varchar(255) DEFAULT NULL COMMENT 'Kurum Firma Adı',
  `address_line1` varchar(255) NOT NULL COMMENT 'Address line1 Bilgisi',
  `address_line2` varchar(255) DEFAULT NULL COMMENT 'Address line2 Bilgisi',
  `city` varchar(100) NOT NULL COMMENT 'City Bilgisi',
  `state` varchar(100) DEFAULT NULL COMMENT 'State Bilgisi',
  `postal_code` varchar(20) NOT NULL COMMENT 'Postal code Bilgisi',
  `country` varchar(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2 code',
  `tax_id` varchar(50) DEFAULT NULL COMMENT 'VAT/Tax ID number',
  `is_default` tinyint(1) DEFAULT 0 COMMENT 'Is default Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`address_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_country` (`country`),
  KEY `idx_customer_default` (`customer_id`,`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilere ait fatura ve teslimat adresleri';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fatura_adresleri`
--

LOCK TABLES `fatura_adresleri` WRITE;
/*!40000 ALTER TABLE `fatura_adresleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `fatura_adresleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faturalar`
--

DROP TABLE IF EXISTS `faturalar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faturalar` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Invoice id Bilgisi',
  `invoice_number` varchar(50) NOT NULL COMMENT 'Invoice number Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `payment_id` int(11) DEFAULT NULL COMMENT 'Payment id Bilgisi',
  `issue_date` date NOT NULL COMMENT 'Issue date Bilgisi',
  `due_date` date DEFAULT NULL COMMENT 'Due date Bilgisi',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Total amount Bilgisi',
  `tax_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Tax amount Bilgisi',
  `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para Birimi',
  `status` enum('draft','issued','paid','cancelled') DEFAULT NULL COMMENT 'Fatura durumu: taslak, kesildi, ödendi, iptal',
  `pdf_url` varchar(255) DEFAULT NULL COMMENT 'Pdf url Bilgisi',
  `notes` text DEFAULT NULL COMMENT 'Notes Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_payment_id` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilere kesilen fatura kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faturalar`
--

LOCK TABLES `faturalar` WRITE;
/*!40000 ALTER TABLE `faturalar` DISABLE KEYS */;
/*!40000 ALTER TABLE `faturalar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giris_denemeleri`
--

DROP TABLE IF EXISTS `giris_denemeleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giris_denemeleri` (
  `attempt_id` bigint(20) NOT NULL COMMENT 'Giriş denemesi ID',
  `email` varchar(255) DEFAULT NULL COMMENT 'Giriş yapılmaya çalışılan e-posta',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'İstek yapılan IP adresi',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Tarayıcı bilgisi',
  `attempt_type` enum('admin','customer') DEFAULT NULL COMMENT 'Giriş türü: admin = yönetici, customer = müşteri',
  `status` enum('success','failed','blocked') DEFAULT NULL COMMENT 'Deneme sonucu: success = başarılı, failed = hatalı, blocked = engellendi',
  `failure_reason` varchar(255) DEFAULT NULL COMMENT 'Başarısızlık nedeni',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Deneme zamanı',
  PRIMARY KEY (`attempt_id`),
  KEY `idx_email` (`email`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email_ip` (`email`,`ip_address`),
  KEY `idx_status_type` (`status`,`attempt_type`),
  KEY `idx_email_status_date` (`email`,`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin ve müşteri giriş denemelerinin kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giris_denemeleri`
--

LOCK TABLES `giris_denemeleri` WRITE;
/*!40000 ALTER TABLE `giris_denemeleri` DISABLE KEYS */;
/*!40000 ALTER TABLE `giris_denemeleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `izinler`
--

DROP TABLE IF EXISTS `izinler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `izinler` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `name` varchar(100) NOT NULL COMMENT 'Yetki anahtarı',
  `description` varchar(255) DEFAULT NULL COMMENT 'Yetki açıklaması',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemde tanımlı yetkiler';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `izinler`
--

LOCK TABLES `izinler` WRITE;
/*!40000 ALTER TABLE `izinler` DISABLE KEYS */;
INSERT INTO `izinler` VALUES (1,'view_dashboard','Ana Paneli (Dashboard) Görüntüleme'),(2,'manage_admins','Sistem Yöneticilerini Ekleme/Düzenleme/Silme'),(3,'manage_roles','Rolleri ve Yetkileri Yönetme'),(4,'view_logs','Denetim Loglarını İnceleme ve Arşivleme'),(5,'manage_tenants','Kurumları (Müşterileri) Ekleme/Düzenleme'),(6,'manage_subscriptions','Kurum Aboneliklerini ve Paketlerini Yönetme'),(7,'manage_billing','Fatura ve Ödeme Hareketlerini Yönetme'),(8,'manage_support','Destek Taleplerini (Ticket) Yanıtlama ve Yönetme'),(9,'view_analytics','Finansal ve Sistem İstatistiklerini/Raporlarını Görüntüleme'),(10,'manage_settings','Genel Sistem Ayarlarını Değiştirme');
/*!40000 ALTER TABLE `izinler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kullanici_aktivite_loglari`
--

DROP TABLE IF EXISTS `kullanici_aktivite_loglari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kullanici_aktivite_loglari` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `user_type` varchar(20) NOT NULL COMMENT 'Kullanıcı tipi',
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'Kullanıcı ID',
  `activity` varchar(100) NOT NULL COMMENT 'Yapılan işlem / aktivite',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ek aktivite verileri',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP adresi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri paneli (tenant) kullanıcılarının yaptığı işlemlerin ve login denemelerinin detaylı aktivite kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kullanici_aktivite_loglari`
--

LOCK TABLES `kullanici_aktivite_loglari` WRITE;
/*!40000 ALTER TABLE `kullanici_aktivite_loglari` DISABLE KEYS */;
/*!40000 ALTER TABLE `kullanici_aktivite_loglari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kuponlar`
--

DROP TABLE IF EXISTS `kuponlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kuponlar` (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Coupon id Bilgisi',
  `code` varchar(20) NOT NULL COMMENT 'Kod',
  `discount_type` enum('percentage','fixed') NOT NULL COMMENT 'Discount type Bilgisi',
  `discount_value` decimal(10,2) NOT NULL COMMENT 'Discount value Bilgisi',
  `min_spend` decimal(10,2) DEFAULT 0.00 COMMENT 'Min spend Bilgisi',
  `starts_at` timestamp NULL DEFAULT NULL COMMENT 'Starts at Bilgisi',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Expires at Bilgisi',
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Usage limit Bilgisi',
  `used_count` int(11) DEFAULT 0 COMMENT 'Used count Bilgisi',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Is active Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_coupon_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='İndirim kuponları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kuponlar`
--

LOCK TABLES `kuponlar` WRITE;
/*!40000 ALTER TABLE `kuponlar` DISABLE KEYS */;
/*!40000 ALTER TABLE `kuponlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kurum_kullanicilari`
--

DROP TABLE IF EXISTS `kurum_kullanicilari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kurum_kullanicilari` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Kullanıcı ID',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `email` varchar(255) NOT NULL COMMENT 'E-Posta Adresi',
  `password_hash` varchar(255) NOT NULL COMMENT 'Şifre Özeti',
  `full_name` varchar(255) NOT NULL COMMENT 'Ad Soyad',
  `role` enum('admin','manager','user') DEFAULT 'user' COMMENT 'Kullanıcı Rolü',
  `status` enum('active','passive') DEFAULT NULL COMMENT 'Kullanıcı durumu: active = aktif, passive = pasif',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Son Giriş Tarihi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Kayıt Silinme Tarihi (Soft Delete)',
  PRIMARY KEY (`user_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilere bağlı alt kullanıcı hesapları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kurum_kullanicilari`
--

LOCK TABLES `kurum_kullanicilari` WRITE;
/*!40000 ALTER TABLE `kurum_kullanicilari` DISABLE KEYS */;
/*!40000 ALTER TABLE `kurum_kullanicilari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kurum_notlari`
--

DROP TABLE IF EXISTS `kurum_notlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kurum_notlari` (
  `note_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Note id Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `admin_id` bigint(20) unsigned NOT NULL COMMENT 'Yönetici ID',
  `note_text` text NOT NULL COMMENT 'Note text Bilgisi',
  `is_urgent` tinyint(1) DEFAULT 0 COMMENT 'Is urgent Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`note_id`),
  KEY `fk_notes_customer` (`customer_id`),
  KEY `fk_notes_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteriler hakkında dahili admin notları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kurum_notlari`
--

LOCK TABLES `kurum_notlari` WRITE;
/*!40000 ALTER TABLE `kurum_notlari` DISABLE KEYS */;
/*!40000 ALTER TABLE `kurum_notlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kurumlar`
--

DROP TABLE IF EXISTS `kurumlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kurumlar` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Kurum/Müşteri Benzersiz ID',
  `customer_code` varchar(255) DEFAULT NULL COMMENT 'Kuruma Ait Benzersiz Kodu',
  `company_name` varchar(255) NOT NULL COMMENT 'Kurum Firma Adı',
  `company_email` varchar(255) NOT NULL COMMENT 'Kurum İletişim E-posta',
  `company_phone` varchar(20) DEFAULT NULL COMMENT 'Kurum İletişim Telefon',
  `contact_person` varchar(150) DEFAULT NULL,
  `tax_office` varchar(100) DEFAULT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `account_manager_id` int(11) DEFAULT NULL,
  `tenant_alias` varchar(100) DEFAULT NULL,
  `company_address` text DEFAULT NULL COMMENT 'Kurum Adresi',
  `industry` varchar(100) DEFAULT NULL COMMENT 'Kurum Faaliyet Sektörü',
  `musteri_paketi` varchar(100) DEFAULT NULL,
  `database_name` varchar(100) DEFAULT NULL COMMENT 'Kurumun Özel Veritabanı Adı (örn: prolyn_k1)',
  `database_password` varchar(255) DEFAULT NULL COMMENT 'Kurum Veritabanı Şifresi',
  `status` enum('active','passive','suspended') DEFAULT NULL COMMENT 'Müşteri durumu: active = aktif, passive = pasif, suspended = askıya alındı',
  `trial_start_date` date DEFAULT NULL COMMENT 'Deneme Sürümü Başlangıç Tarihi',
  `trial_end_date` date DEFAULT NULL COMMENT 'Deneme Sürümü Bitiş Tarihi',
  `sozlesme_bitis_tarihi` date DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL COMMENT 'Kurum Logo Bağlantısı',
  `contact_name` varchar(255) DEFAULT NULL COMMENT 'Kurum Yetkili Kişi Adı',
  `contact_email` varchar(255) DEFAULT NULL COMMENT 'Kurum Yetkili E-posta',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT 'Kurum Yetkili Telefon',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Kayıt Silinme Tarihi (Soft Delete)',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Kaydı Oluşturan Yönetici ID',
  `allowed_modules` longtext DEFAULT NULL COMMENT 'Kurumun Erişebileceği Modüller',
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `company_email` (`company_email`),
  UNIQUE KEY `database_name` (`database_name`),
  UNIQUE KEY `customer_code` (`customer_code`),
  KEY `idx_status` (`status`),
  KEY `idx_database_name` (`database_name`),
  KEY `idx_company_email` (`company_email`),
  KEY `fk_customers_created_by` (`created_by`),
  KEY `idx_status_created` (`status`,`created_at`),
  KEY `idx_customer_status_created` (`status`,`created_at`),
  FULLTEXT KEY `ft_company_search` (`company_name`,`contact_name`),
  FULLTEXT KEY `ft_customer_search` (`company_name`,`contact_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemi kullanan müşteri firmalar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kurumlar`
--

LOCK TABLES `kurumlar` WRITE;
/*!40000 ALTER TABLE `kurumlar` DISABLE KEYS */;
INSERT INTO `kurumlar` VALUES (5,'K1','Verify Test A.S.','verifyadmin@example.com','05559998877',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'prolyn_k1','RootPass1903!','active','2026-06-27','2026-07-27',NULL,NULL,'Verify Admin','verifyadmin@example.com','05559998877','2026-06-27 12:19:19','2026-06-28 16:40:00',NULL,NULL,'[\"core\",\"customer\",\"commercial\",\"appointment\",\"current_account\",\"inventory\",\"accounting\",\"invoice\",\"contract\",\"reports\"]'),(9,'k2','Başlangıç A.Ş.','admin@k2.com','05559998899',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'prolyn_k2','RootPass1903!','active',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-28 16:50:35','2026-06-28 16:59:55',NULL,NULL,'[\"core\",\"customer\",\"commercial\",\"appointment\",\"current_account\",\"inventory\",\"accounting\",\"invoice\",\"contract\",\"reports\"]'),(10,'k3','Pro A.Ş.','admin@k3.com','05559998888',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'prolyn_k3','RootPass1903!','active',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-28 16:50:35','2026-06-28 16:59:59',NULL,NULL,'[\"core\",\"customer\",\"commercial\",\"appointment\",\"current_account\",\"inventory\",\"accounting\",\"invoice\",\"contract\",\"reports\"]'),(11,'k4','Kurumsal A.Ş.','admin@k4.com','05559998866','Kuramsal 1','','','',1,'',NULL,'','','prolyn_k4','RootPass1903!','active',NULL,'2027-01-28','2026-01-28',NULL,NULL,NULL,NULL,'2026-06-28 16:50:35','2026-06-28 17:33:40',NULL,NULL,'[\"core\",\"customer\",\"commercial\",\"appointment\",\"current_account\",\"inventory\",\"accounting\",\"invoice\",\"contract\",\"reports\"]');
/*!40000 ALTER TABLE `kurumlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `odemeler`
--

DROP TABLE IF EXISTS `odemeler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `odemeler` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Payment id Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `subscription_id` int(11) NOT NULL COMMENT 'Subscription id Bilgisi',
  `amount` decimal(10,2) NOT NULL COMMENT 'Tutar',
  `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para Birimi',
  `payment_method` enum('credit_card','bank_transfer') NOT NULL COMMENT 'Ödeme Yöntemi',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending' COMMENT 'Payment status Bilgisi',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'İşlem Referans ID',
  `payment_gateway` varchar(50) DEFAULT NULL COMMENT 'Payment gateway Bilgisi',
  `invoice_number` varchar(50) DEFAULT NULL COMMENT 'Invoice number Bilgisi',
  `card_last4` varchar(4) DEFAULT NULL COMMENT 'Card last4 Bilgisi',
  `card_brand` varchar(20) DEFAULT NULL COMMENT 'Card brand Bilgisi',
  `bank_code` varchar(10) DEFAULT NULL COMMENT 'Bank code Bilgisi',
  `iban` varchar(50) DEFAULT NULL COMMENT 'Iban Bilgisi',
  `reference_code` varchar(50) DEFAULT NULL COMMENT 'Reference code Bilgisi',
  `notes` text DEFAULT NULL COMMENT 'Notes Bilgisi',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_period_start` date DEFAULT NULL COMMENT 'Paid period start Bilgisi',
  `paid_period_end` date DEFAULT NULL COMMENT 'Paid period end Bilgisi',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Expires at Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_status_date` (`payment_status`,`payment_date`),
  KEY `idx_payment_status_date` (`payment_status`,`payment_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri ödeme hareketleri ve işlem kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `odemeler`
--

LOCK TABLES `odemeler` WRITE;
/*!40000 ALTER TABLE `odemeler` DISABLE KEYS */;
/*!40000 ALTER TABLE `odemeler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oturumlar`
--

DROP TABLE IF EXISTS `oturumlar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oturumlar` (
  `session_id` varchar(128) NOT NULL COMMENT 'Session id Bilgisi',
  `user_type` enum('admin','customer','user') NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID',
  `user_email` varchar(255) DEFAULT NULL,
  `user_full_name` varchar(255) DEFAULT NULL,
  `tenant_db` varchar(100) DEFAULT NULL,
  `firma_adi` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP Adresi',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Tarayıcı/Cihaz Bilgisi',
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data` text DEFAULT NULL COMMENT 'Serialized session data',
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Expires at Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`session_id`),
  KEY `idx_user_type_id` (`user_type`,`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_user_expires` (`user_type`,`user_id`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemdeki aktif kullanıcı oturum (session) bilgileri ve geçerlilik süreleri';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oturumlar`
--

LOCK TABLES `oturumlar` WRITE;
/*!40000 ALTER TABLE `oturumlar` DISABLE KEYS */;
INSERT INTO `oturumlar` VALUES ('m0032vau38p7lb30fgng4477fa','user',1,'admin@k2.com','Admin K2','prolyn_k2','Bilinmeyen Firma','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0','2026-06-28 16:46:26',NULL,'2026-06-29 16:46:26','2026-06-28 16:46:26'),('oglbmdnvl43dd48h1cre768p9q','user',1,'admin@k4.com','Admin K4','prolyn_k4','Kurumsal A.Ş.','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0','2026-06-28 17:02:18',NULL,'2026-06-29 17:02:18','2026-06-28 17:01:52'),('om3eatepaokdbgkr63d2ag7r7r','user',1,'admin@k2.com','Admin K2','prolyn_k2','Bilinmeyen Firma','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0','2026-06-28 16:46:27',NULL,'2026-06-29 16:46:27','2026-06-28 16:46:27');
/*!40000 ALTER TABLE `oturumlar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ozellik_kullanimi`
--

DROP TABLE IF EXISTS `ozellik_kullanimi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ozellik_kullanimi` (
  `usage_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Usage id Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `feature_key` varchar(100) NOT NULL COMMENT 'api_calls, users, storage, etc.',
  `usage_count` int(11) DEFAULT 0 COMMENT 'Usage count Bilgisi',
  `limit_count` int(11) DEFAULT NULL COMMENT 'NULL = unlimited',
  `period_start` date NOT NULL COMMENT 'Period start Bilgisi',
  `period_end` date NOT NULL COMMENT 'Period end Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`usage_id`),
  UNIQUE KEY `unique_customer_feature_period` (`customer_id`,`feature_key`,`period_start`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_period` (`period_start`,`period_end`),
  KEY `idx_feature_key` (`feature_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri bazlı özellik kullanım ve limit takibi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ozellik_kullanimi`
--

LOCK TABLES `ozellik_kullanimi` WRITE;
/*!40000 ALTER TABLE `ozellik_kullanimi` DISABLE KEYS */;
INSERT INTO `ozellik_kullanimi` VALUES (1,5,'Kullanıcı Sınırı',2,5,'2026-06-01','2026-06-30','2026-06-28 16:03:46','2026-06-28 16:03:46'),(2,5,'Fatura Adedi',35,100,'2026-06-01','2026-06-30','2026-06-28 16:03:46','2026-06-28 16:03:46'),(3,5,'E-arşiv',450,500,'2026-06-01','2026-06-30','2026-06-28 16:03:46','2026-06-28 16:03:46');
/*!40000 ALTER TABLE `ozellik_kullanimi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paket_ozellikleri`
--

DROP TABLE IF EXISTS `paket_ozellikleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paket_ozellikleri` (
  `feature_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Feature id Bilgisi',
  `plan_name` enum('basic','pro','enterprise') NOT NULL COMMENT 'Plan name Bilgisi',
  `plan_display_name` varchar(100) DEFAULT NULL COMMENT 'Plan display name Bilgisi',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fiyat Bilgisi',
  `max_admin_users` int(11) DEFAULT NULL COMMENT 'Max admin users Bilgisi',
  `max_total_users` int(11) DEFAULT NULL COMMENT 'Max total users Bilgisi',
  `max_storage_gb` int(11) DEFAULT NULL COMMENT 'Max storage gb Bilgisi',
  `max_file_size_mb` int(11) DEFAULT NULL COMMENT 'Max file size mb Bilgisi',
  `max_files_per_upload` int(11) DEFAULT NULL COMMENT 'Max files per upload Bilgisi',
  `api_access` tinyint(1) DEFAULT 0 COMMENT 'Api access Bilgisi',
  `api_requests_per_month` int(11) DEFAULT NULL COMMENT 'Api requests per month Bilgisi',
  `custom_reports` tinyint(1) DEFAULT 0 COMMENT 'Custom reports Bilgisi',
  `advanced_analytics` tinyint(1) DEFAULT 0 COMMENT 'Advanced analytics Bilgisi',
  `white_label` tinyint(1) DEFAULT 0 COMMENT 'White label Bilgisi',
  `email_campaigns` tinyint(1) DEFAULT 0 COMMENT 'Email campaigns Bilgisi',
  `bulk_operations` tinyint(1) DEFAULT 0 COMMENT 'Bulk operations Bilgisi',
  `support_level` enum('email','priority','dedicated') DEFAULT 'email' COMMENT 'Support level Bilgisi',
  `support_response_hours` int(11) DEFAULT NULL COMMENT 'Support response hours Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`feature_id`),
  UNIQUE KEY `plan_name` (`plan_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Paketlere tanımlı özellik ve limitler';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paket_ozellikleri`
--

LOCK TABLES `paket_ozellikleri` WRITE;
/*!40000 ALTER TABLE `paket_ozellikleri` DISABLE KEYS */;
INSERT INTO `paket_ozellikleri` VALUES (1,'basic','Başlangıç Paketi',499.00,2,5,5,50,10,0,0,0,0,0,0,0,'email',48,'2025-11-21 13:05:29'),(2,'pro','Profosyonel Paketi',999.00,5,25,50,100,25,1,10000,1,1,0,1,1,'priority',24,'2025-11-21 13:05:29'),(3,'enterprise','Kurumsal Paket',2499.00,999,999,999,999,999,1,999999,1,1,1,1,1,'dedicated',4,'2025-11-21 13:05:29');
/*!40000 ALTER TABLE `paket_ozellikleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_izinleri`
--

DROP TABLE IF EXISTS `rol_izinleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol_izinleri` (
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'Role id Bilgisi',
  `permission_id` bigint(20) unsigned NOT NULL COMMENT 'Permission id Bilgisi',
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rollere atanan yetkiler';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_izinleri`
--

LOCK TABLES `rol_izinleri` WRITE;
/*!40000 ALTER TABLE `rol_izinleri` DISABLE KEYS */;
INSERT INTO `rol_izinleri` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(2,1),(2,6),(2,7),(2,9),(3,1),(3,5),(3,8),(4,1),(4,4),(4,9);
/*!40000 ALTER TABLE `rol_izinleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roller`
--

DROP TABLE IF EXISTS `roller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roller` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `name` varchar(50) NOT NULL COMMENT 'Rol adı',
  `description` varchar(255) DEFAULT NULL COMMENT 'Rol açıklaması',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem rollerinin tanımlandığı tablo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roller`
--

LOCK TABLES `roller` WRITE;
/*!40000 ALTER TABLE `roller` DISABLE KEYS */;
INSERT INTO `roller` VALUES (1,'Sistem Yöneticisi','Tüm izinlere sahip tam yetkili yönetici rolü.','2026-06-26 22:27:54'),(2,'Finans Sorumlusu','Faturalar, abonelikler ve finansal raporlarla ilgilenen rol.','2026-06-26 22:27:54'),(3,'Müşteri Temsilcisi','Müşteri kurulumları ve destek taleplerini yöneten rol.','2026-06-26 22:27:54'),(4,'Denetmen (Auditor)','Sistemdeki logları ve raporları inceleyen salt-okunur yetkili rol.','2026-06-26 22:27:54');
/*!40000 ALTER TABLE `roller` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satis_icerigi`
--

DROP TABLE IF EXISTS `satis_icerigi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satis_icerigi` (
  `sales_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Sales id Bilgisi',
  `section` varchar(100) NOT NULL COMMENT 'Section Bilgisi',
  `title_tr` varchar(255) DEFAULT NULL COMMENT 'Title tr Bilgisi',
  `title_en` varchar(255) DEFAULT NULL COMMENT 'Title en Bilgisi',
  `content_tr` longtext DEFAULT NULL COMMENT 'Content tr Bilgisi',
  `content_en` longtext DEFAULT NULL COMMENT 'Content en Bilgisi',
  `icon` varchar(100) DEFAULT NULL COMMENT 'Icon Bilgisi',
  `image_url` varchar(255) DEFAULT NULL COMMENT 'Image url Bilgisi',
  `sort_order` int(11) DEFAULT 999 COMMENT 'Sort order Bilgisi',
  `status` enum('draft','published','archived') DEFAULT NULL COMMENT 'İçerik durumu: taslak, yayında, arşivlenmiş',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`sales_id`) USING BTREE,
  KEY `idx_section` (`section`),
  KEY `idx_status` (`status`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Satış, pazarlama ve tanıtım içeriklerinin tutulduğu tablo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satis_icerigi`
--

LOCK TABLES `satis_icerigi` WRITE;
/*!40000 ALTER TABLE `satis_icerigi` DISABLE KEYS */;
INSERT INTO `satis_icerigi` VALUES (1,'hero','Mutfak Işletmeleri İçin Kapsamlı ERP Çözümü','Complete ERP Solution for Kitchen Businesses','Müşteri yönetimi, kontrat takibi, ödeme takibi ve daha fazlası tek platformda','Customer management, contract tracking, payment management and more on a single platform','fas fa-kitchen-set','',1,'draft','2025-11-21 13:05:29','2025-11-22 12:12:51'),(2,'features','Müşteri Yönetimi','Customer Management','Müşteri bilgilerini merkezi olarak yönetin ve takip edin','Manage and track customer information centrally','fas fa-users','',2,'draft','2025-11-21 13:05:29','2026-06-26 23:47:00'),(3,'features','Kontrat & Sözleşmeler','Contracts & Agreements','Kontrat ve sözleşmeleri kolayca oluşturun ve izleyin','Create and monitor contracts and agreements easily','fas fa-file-contract','',3,'draft','2025-11-21 13:05:29','2026-06-26 23:47:09'),(4,'features','Ödeme Takibi','Payment Tracking','Ödemeleri ve tahsilatları gerçek zamanlı izleyin','Track payments and collections in real-time','fas fa-credit-card','',4,'draft','2025-11-21 13:05:29','2026-06-26 23:47:17'),(5,'features','Raporlar & Analizler','Reports & Analytics','Detaylı raporlar ve analizlerle işletmenizi daha iyi anlayın','Understand your business better with detailed reports and analytics','fas fa-chart-bar','',5,'draft','2025-11-21 13:05:29','2026-06-26 23:47:26'),(6,'features','Kullanıcı Yönetimi','User Management','Ekip üyelerinizi yönetin ve izinleri kontrol edin','Manage team members and control permissions','fas fa-user-tie','',6,'draft','2025-11-21 13:05:29','2026-06-26 23:47:47'),(7,'features','Bulut Depolama','Cloud Storage','Verilerinizi güvenli bulut ortamında saklayın','Store your data securely in the cloud','fas fa-cloud','',7,'draft','2025-11-21 13:05:29','2026-06-26 23:52:49');
/*!40000 ALTER TABLE `satis_icerigi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!50001 DROP VIEW IF EXISTS `setting`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `setting` AS SELECT
 1 AS `site_name`,
  1 AS `trial_period_days`,
  1 AS `max_login_attempts`,
  1 AS `smtp_host`,
  1 AS `smtp_port`,
  1 AS `smtp_user`,
  1 AS `smtp_pass`,
  1 AS `maintenance_mode` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `sifre_sifirlama`
--

DROP TABLE IF EXISTS `sifre_sifirlama`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sifre_sifirlama` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Reset id Bilgisi',
  `email` varchar(255) NOT NULL COMMENT 'E-Posta Adresi',
  `token` varchar(255) NOT NULL COMMENT 'Token Bilgisi',
  `user_type` enum('admin','customer') NOT NULL COMMENT 'User type Bilgisi',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0 COMMENT 'Used Bilgisi',
  `used_at` timestamp NULL DEFAULT NULL COMMENT 'Used at Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reset_id`),
  KEY `idx_token` (`token`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Şifre sıfırlama talepleri ve token kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sifre_sifirlama`
--

LOCK TABLES `sifre_sifirlama` WRITE;
/*!40000 ALTER TABLE `sifre_sifirlama` DISABLE KEYS */;
/*!40000 ALTER TABLE `sifre_sifirlama` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistem_ayarlari`
--

DROP TABLE IF EXISTS `sistem_ayarlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sistem_ayarlari` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Setting id Bilgisi',
  `setting_key` varchar(100) DEFAULT NULL COMMENT 'Ayar anahtarı (benzersiz tanım)',
  `setting_value` text DEFAULT NULL COMMENT 'Ayar değeri',
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string' COMMENT 'Setting type Bilgisi',
  `category` varchar(50) DEFAULT 'general' COMMENT 'Category Bilgisi',
  `description` varchar(255) DEFAULT NULL COMMENT 'Açıklama',
  `is_public` tinyint(1) DEFAULT NULL COMMENT '1: Ön yüzden erişilebilir, 0: Sadece sistem içi',
  `updated_by` int(11) DEFAULT NULL COMMENT 'Updated by Bilgisi',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemin genel yapılandırma ve konfigürasyon ayarlarının tutulduğu tablo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistem_ayarlari`
--

LOCK TABLES `sistem_ayarlari` WRITE;
/*!40000 ALTER TABLE `sistem_ayarlari` DISABLE KEYS */;
INSERT INTO `sistem_ayarlari` VALUES (1,'site_name','Prolyn','string','general','Platform adı',1,NULL,'2026-06-27 12:08:57'),(2,'trial_period_days','30','number','subscription','Deneme süresi (gün)',0,NULL,'2025-12-21 20:36:32'),(3,'max_login_attempts','5','number','security','Maksimum giriş denemesi',0,NULL,'2025-12-21 20:36:32'),(4,'smtp_host','smtp.gmail.com','string','email','SMTP sunucu adresi',0,NULL,'2025-12-21 20:36:32'),(5,'smtp_port','587','number','email','SMTP port',0,NULL,'2025-12-21 20:36:32'),(6,'currency','TRY','string','general','Para birimi',1,NULL,'2025-12-21 20:36:32'),(7,'timezone','Europe/Istanbul','string','general','Saat dilimi',0,NULL,'2025-12-21 20:36:32'),(8,'seo_title','Prolyn ERP - Yeni Nesil Kaynak Planlama','string','seo','Sitenin ana başlığı (Title)',1,NULL,'2026-06-27 11:25:27'),(9,'seo_description','Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız.','string','seo','Site Meta Açıklaması (Description)',1,NULL,'2026-06-27 11:25:27'),(10,'seo_keywords','erp, yazılım, muhasebe, finans, şirket yönetimi','string','seo','Site Meta Anahtar Kelimeleri',1,NULL,'2026-06-27 11:25:27'),(11,'contact_email','info@prolyn.com','string','contact','İletişim E-posta Adresi',1,NULL,'2026-06-27 11:25:27'),(12,'contact_phone','+90 850 123 45 67','string','contact','İletişim Telefon Numarası',1,NULL,'2026-06-27 11:25:27'),(13,'social_twitter','https://twitter.com/prolyn','string','social','Twitter URL',1,NULL,'2026-06-27 11:25:27'),(14,'social_linkedin','https://linkedin.com/company/prolyn','string','social','LinkedIn URL',1,NULL,'2026-06-27 11:25:27'),(15,'website_hero_title','İşletmenizi Dijitalleştirin, Geleceğe Taşıyın','string','general',NULL,1,NULL,'2026-06-28 14:48:07'),(16,'website_hero_subtitle','Prolyn ERP ile tüm iş süreçlerinizi tek bir platformdan yönetin. Bulut tabanlı, güvenli ve esnek yapısıyla işletmenizi bir adım öteye taşıyın.','string','general',NULL,1,NULL,'2026-06-28 14:48:07'),(17,'website_footer_text','Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız. Hız, güvenlik ve esnekliği bir arada sunuyoruz.','string','general',NULL,1,NULL,'2026-06-28 14:48:07');
/*!40000 ALTER TABLE `sistem_ayarlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistem_hatalari`
--

DROP TABLE IF EXISTS `sistem_hatalari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sistem_hatalari` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `source` varchar(50) NOT NULL COMMENT 'Hata kaynağı (api, cron, ui)',
  `message` text NOT NULL COMMENT 'Hata mesajı',
  `stack_trace` longtext DEFAULT NULL COMMENT 'Hata detayları',
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT 'Çözüm zamanı',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_errors_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem genelinde oluşan kritik hatalar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistem_hatalari`
--

LOCK TABLES `sistem_hatalari` WRITE;
/*!40000 ALTER TABLE `sistem_hatalari` DISABLE KEYS */;
/*!40000 ALTER TABLE `sistem_hatalari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistem_olaylari`
--

DROP TABLE IF EXISTS `sistem_olaylari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sistem_olaylari` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Benzersiz Kayıt ID',
  `event_name` varchar(100) NOT NULL COMMENT 'Olay adı',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Olay verisi',
  `status` varchar(20) DEFAULT 'pending' COMMENT 'İşlenme durumu',
  `processed_at` timestamp NULL DEFAULT NULL COMMENT 'Processed at Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_events_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemde tetiklenen asenkron olayların (event) ve işleyiş adımlarının kaydedildiği loglar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistem_olaylari`
--

LOCK TABLES `sistem_olaylari` WRITE;
/*!40000 ALTER TABLE `sistem_olaylari` DISABLE KEYS */;
/*!40000 ALTER TABLE `sistem_olaylari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vergi_oranlari`
--

DROP TABLE IF EXISTS `vergi_oranlari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vergi_oranlari` (
  `tax_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Tax id Bilgisi',
  `tax_name` varchar(50) NOT NULL COMMENT 'KDV, VAT, GST vb.',
  `tax_percent` decimal(5,2) NOT NULL COMMENT 'Tax percent Bilgisi',
  `is_default` tinyint(1) DEFAULT 0 COMMENT 'Is default Bilgisi',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Is active Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tax_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vergi oranları yönetimi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vergi_oranlari`
--

LOCK TABLES `vergi_oranlari` WRITE;
/*!40000 ALTER TABLE `vergi_oranlari` DISABLE KEYS */;
INSERT INTO `vergi_oranlari` VALUES (1,'KDV %20',20.00,1,1,'2025-12-21 22:18:36'),(2,'KDV %10',10.00,0,1,'2026-06-26 22:41:16'),(3,'KDV %18',18.00,0,1,'2026-06-26 22:53:40'),(4,'KDV %1',1.00,0,1,'2026-06-26 22:57:48');
/*!40000 ALTER TABLE `vergi_oranlari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhook_loglari`
--

DROP TABLE IF EXISTS `webhook_loglari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhook_loglari` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Log id Bilgisi',
  `webhook_id` int(11) NOT NULL COMMENT 'Webhook id Bilgisi',
  `event_type` varchar(50) NOT NULL COMMENT 'Event type Bilgisi',
  `payload` longtext NOT NULL COMMENT 'Payload Bilgisi',
  `response_code` int(11) DEFAULT NULL COMMENT 'Response code Bilgisi',
  `response_body` text DEFAULT NULL COMMENT 'Response body Bilgisi',
  `duration_ms` int(11) DEFAULT NULL COMMENT 'Request duration in milliseconds',
  `status` enum('pending','success','failed') DEFAULT NULL COMMENT 'Webhook sonucu: beklemede, başarılı, hatalı',
  `error_message` text DEFAULT NULL COMMENT 'Error message Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_webhook_id` (`webhook_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_webhook_status` (`webhook_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dış sistemlere gönderilen webhook tetiklemelerinin yanıt, hata ve başarı durumlarını içeren detaylı loglar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhook_loglari`
--

LOCK TABLES `webhook_loglari` WRITE;
/*!40000 ALTER TABLE `webhook_loglari` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhook_loglari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhooklar`
--

DROP TABLE IF EXISTS `webhooklar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webhooklar` (
  `webhook_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Webhook id Bilgisi',
  `customer_id` int(11) NOT NULL COMMENT 'Kurum/Müşteri Benzersiz ID',
  `name` varchar(100) NOT NULL COMMENT 'İsim',
  `url` varchar(500) NOT NULL COMMENT 'Url Bilgisi',
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of subscribed event types',
  `secret` varchar(64) DEFAULT NULL COMMENT 'HMAC secret for signature verification',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Is active Bilgisi',
  `retry_count` int(11) DEFAULT 3 COMMENT 'Retry count Bilgisi',
  `timeout_seconds` int(11) DEFAULT 30 COMMENT 'Timeout seconds Bilgisi',
  `last_triggered_at` timestamp NULL DEFAULT NULL COMMENT 'Last triggered at Bilgisi',
  `last_response_code` int(11) DEFAULT NULL COMMENT 'Last response code Bilgisi',
  `last_error` text DEFAULT NULL COMMENT 'Last error Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`webhook_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_customer_active` (`customer_id`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dış uygulamalarla entegrasyon için tanımlanan webhook uç noktaları ve tetikleyici olaylar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhooklar`
--

LOCK TABLES `webhooklar` WRITE;
/*!40000 ALTER TABLE `webhooklar` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhooklar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_features`
--

DROP TABLE IF EXISTS `website_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `website_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `order_no` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_features`
--

LOCK TABLES `website_features` WRITE;
/*!40000 ALTER TABLE `website_features` DISABLE KEYS */;
INSERT INTO `website_features` VALUES (1,'Sistem ve Kullanıcı Yönetimi','Rol bazlı detaylı yetkilendirme ve çoklu kullanıcı yapısıyla güvenli yönetim.','bi bi-shield-check',1,1,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(2,'Müşteri ve Cari Yönetimi','Müşterilerinizin tüm bilgilerini ve cari hesaplarını tek noktadan izleyin.','bi bi-people',1,2,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(3,'Randevu ve İş Takibi','Randevularınızı takvime bağlayın, ekibinizin çalışma planını otomatikleştirin.','bi bi-calendar-check',1,3,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(4,'Stok ve Ürün Yönetimi','Depolarınızı, ürün gruplarınızı ve stok hareketlerinizi gerçek zamanlı takip edin.','bi bi-box-seam',1,4,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(5,'Finans ve Ön Muhasebe','Nakit kasa, bankalar ve çek işlemleriyle tüm finansal akışınızı yönetin.','bi bi-bank',1,5,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(6,'Fatura ve Tahsilat','Saniyeler içinde e-fatura / ticari fatura oluşturun ve tahsilat takibini yapın.','bi bi-receipt',1,6,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(7,'Sözleşme Yönetimi','Hazır şablonlarla dijital sözleşmeler oluşturun ve müşteri bazlı saklayın.','bi bi-file-earmark-text',1,7,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(8,'Raporlar ve İstatistikler','İşletmenizin performansını analiz edin, gelişmiş panolarla karar alma sürecinizi hızlandırın.','bi bi-bar-chart',1,8,'2026-06-28 15:01:40','2026-06-28 15:01:40');
/*!40000 ALTER TABLE `website_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_modules`
--

DROP TABLE IF EXISTS `website_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `website_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `order_no` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_modules`
--

LOCK TABLES `website_modules` WRITE;
/*!40000 ALTER TABLE `website_modules` DISABLE KEYS */;
INSERT INTO `website_modules` VALUES (1,'Sistem ve Kullanıcı Yönetimi','Rol bazlı detaylı yetkilendirme ve çoklu kullanıcı yapısıyla güvenli yönetim.','bi bi-shield-check',1,1,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(2,'Müşteri ve Cari Yönetimi','Müşterilerinizin tüm bilgilerini ve cari hesaplarını tek noktadan izleyin.','bi bi-people',1,2,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(3,'Randevu ve İş Takibi','Randevularınızı takvime bağlayın, ekibinizin çalışma planını otomatikleştirin.','bi bi-calendar-check',1,3,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(4,'Stok ve Ürün Yönetimi','Depolarınızı, ürün gruplarınızı ve stok hareketlerinizi gerçek zamanlı takip edin.','bi bi-box-seam',1,4,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(5,'Finans ve Ön Muhasebe','Nakit kasa, bankalar ve çek işlemleriyle tüm finansal akışınızı yönetin.','bi bi-bank',1,5,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(6,'Fatura ve Tahsilat','Saniyeler içinde e-fatura / ticari fatura oluşturun ve tahsilat takibini yapın.','bi bi-receipt',1,6,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(7,'Sözleşme Yönetimi','Hazır şablonlarla dijital sözleşmeler oluşturun ve müşteri bazlı saklayın.','bi bi-file-earmark-text',1,7,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(8,'Raporlar ve İstatistikler','İşletmenizin performansını analiz edin, gelişmiş panolarla karar alma sürecinizi hızlandırın.','bi bi-bar-chart',1,8,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(9,'Banka ve Pos Yönetimi','Farklı banka hesaplarınızı entegre edin ve POS tahsilatlarını otomatik izleyin.','bi bi-credit-card',1,9,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(10,'Çek ve Senet Birleştirme','Müşteri çeklerini kaydedin, ciro edin ve vade tarihlerine göre uyarılar alın.','bi bi-journal-text',1,10,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(11,'Montaj ve Servis Takibi','Satış sonrası montaj, arıza ve saha servis operasyonlarınızı kolayca yönetin.','bi bi-tools',1,11,'2026-06-28 15:01:40','2026-06-28 15:01:40'),(12,'Log ve Denetim','Sistemdeki her değişikliği log bazında izleyerek tam güvenlik sağlayın.','bi bi-clipboard-data',1,12,'2026-06-28 15:01:40','2026-06-28 15:01:40');
/*!40000 ALTER TABLE `website_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yonetici_loglari`
--

DROP TABLE IF EXISTS `yonetici_loglari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yonetici_loglari` (
  `log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Log id Bilgisi',
  `log_type` tinyint(3) unsigned NOT NULL DEFAULT 3 COMMENT '1=Error, 2=Warning, 3=Admin Action, 4=Info, 5=Debug',
  `log_description` varchar(500) NOT NULL COMMENT 'Action description',
  `log_status` enum('success','error','warning','pending','failed','info') DEFAULT 'info' COMMENT 'Log status Bilgisi',
  `admin_id` int(11) NOT NULL COMMENT 'Admin who performed the action',
  `ip_address` varchar(45) DEFAULT '0.0.0.0' COMMENT 'IP Adresi',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Tarayıcı/Cihaz Bilgisi',
  `request_method` varchar(10) DEFAULT 'GET' COMMENT 'Request method Bilgisi',
  `request_path` varchar(255) DEFAULT NULL COMMENT 'Request path Bilgisi',
  `old_value` mediumtext DEFAULT NULL COMMENT 'Old value Bilgisi',
  `new_value` mediumtext DEFAULT NULL COMMENT 'New value Bilgisi',
  `entity_type` varchar(100) DEFAULT NULL COMMENT 'Entity type Bilgisi',
  `entity_id` int(11) DEFAULT NULL COMMENT 'Entity id Bilgisi',
  `additional_data` mediumtext DEFAULT NULL COMMENT 'Additional data Bilgisi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_log_type` (`log_type`),
  KEY `idx_log_status` (`log_status`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_created_at` (`created_at`),
  FULLTEXT KEY `ft_log_search` (`log_description`),
  FULLTEXT KEY `ft_admin_log` (`log_description`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yönetici panelinde yapılan tüm işlemlerin log kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yonetici_loglari`
--

LOCK TABLES `yonetici_loglari` WRITE;
/*!40000 ALTER TABLE `yonetici_loglari` DISABLE KEYS */;
INSERT INTO `yonetici_loglari` VALUES (1,1,'Yeni vergi oranı eklendi','success',1,'::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','GET',NULL,NULL,'{\"tax_name\":\"KDV %1\",\"tax_percent\":1,\"is_default\":0,\"is_active\":1}','vergi_oranlari',4,NULL,'2026-06-26 22:57:48'),(2,2,'Rol güncellendi','success',1,'::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','GET',NULL,'{\"id\":2,\"name\":\"Finans Sorumlusu\",\"description\":\"Faturalar, abonelikler ve finansal raporlarla ilgilenen rol.\",\"created_at\":\"2026-06-27 01:27:54\",\"permissions\":[\"view_dashboard\",\"manage_subscriptions\",\"manage_billing\",\"view_analytics\"]}','{\"name\":\"Finans Sorumlusu\",\"description\":\"Faturalar, abonelikler ve finansal raporlarla ilgilenen rol.\",\"permissions\":[\"manage_admins\",\"manage_billing\",\"manage_subscriptions\",\"view_analytics\",\"view_dashboard\"]}','roller',2,NULL,'2026-06-26 22:58:09'),(3,2,'Rol güncellendi','success',1,'::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','GET',NULL,'{\"id\":2,\"name\":\"Finans Sorumlusu\",\"description\":\"Faturalar, abonelikler ve finansal raporlarla ilgilenen rol.\",\"created_at\":\"2026-06-27 01:27:54\",\"permissions\":[\"view_dashboard\",\"manage_admins\",\"manage_subscriptions\",\"manage_billing\",\"view_analytics\"]}','{\"name\":\"Finans Sorumlusu\",\"description\":\"Faturalar, abonelikler ve finansal raporlarla ilgilenen rol.\",\"permissions\":[\"manage_billing\",\"manage_subscriptions\",\"view_analytics\",\"view_dashboard\"]}','roller',2,NULL,'2026-06-26 22:58:36');
/*!40000 ALTER TABLE `yonetici_loglari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yonetici_loglari_yedek`
--

DROP TABLE IF EXISTS `yonetici_loglari_yedek`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yonetici_loglari_yedek` (
  `log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Log id Bilgisi',
  `log_type` tinyint(3) unsigned NOT NULL DEFAULT 3 COMMENT '1=Error, 2=Warning, 3=Admin Action, 4=Info, 5=Debug',
  `log_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Action description',
  `log_status` enum('success','error','warning','pending','failed','info') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'info' COMMENT 'Operation status',
  `admin_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Admin who performed the action',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0.0.0.0' COMMENT 'IP address (IPv4 or IPv6)',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Browser/Client information',
  `request_method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'GET' COMMENT 'HTTP Method: GET, POST, PUT, DELETE, PATCH',
  `request_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Request URI path',
  `old_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Old value (JSON format)',
  `new_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'New value (JSON format)',
  `entity_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Table/Entity type: customers, subscriptions, etc.',
  `entity_id` int(10) unsigned DEFAULT NULL COMMENT 'Record ID being modified',
  `additional_data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Additional data (JSON): changed_fields, metadata, etc.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Log creation timestamp',
  PRIMARY KEY (`log_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_log_type` (`log_type`),
  KEY `idx_log_status` (`log_status`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_log_type_date` (`log_type`,`created_at`),
  KEY `idx_admin_date` (`admin_id`,`created_at`),
  FULLTEXT KEY `ft_log_description` (`log_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin loglarının arşivlenmiş (yedek) kayıtları';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yonetici_loglari_yedek`
--

LOCK TABLES `yonetici_loglari_yedek` WRITE;
/*!40000 ALTER TABLE `yonetici_loglari_yedek` DISABLE KEYS */;
/*!40000 ALTER TABLE `yonetici_loglari_yedek` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yonetici_rolleri`
--

DROP TABLE IF EXISTS `yonetici_rolleri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yonetici_rolleri` (
  `admin_id` int(11) NOT NULL COMMENT 'Admin ID',
  `role_id` bigint(20) unsigned NOT NULL COMMENT 'Rol ID',
  PRIMARY KEY (`admin_id`,`role_id`),
  KEY `fk_admin_roles_role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin kullanıcılar ile roller arasındaki ilişki';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yonetici_rolleri`
--

LOCK TABLES `yonetici_rolleri` WRITE;
/*!40000 ALTER TABLE `yonetici_rolleri` DISABLE KEYS */;
INSERT INTO `yonetici_rolleri` VALUES (1,1),(2,3),(3,2);
/*!40000 ALTER TABLE `yonetici_rolleri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yoneticiler`
--

DROP TABLE IF EXISTS `yoneticiler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yoneticiler` (
  `admin_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Yönetici ID',
  `username` varchar(100) NOT NULL COMMENT 'Kullanıcı Adı',
  `email` varchar(255) NOT NULL COMMENT 'E-Posta Adresi',
  `full_name` varchar(255) NOT NULL COMMENT 'Ad Soyad',
  `admin_pic` varchar(255) DEFAULT NULL COMMENT 'Admin pic Bilgisi',
  `password_hash` varchar(255) NOT NULL COMMENT 'Şifre Özeti',
  `role` enum('superadmin','admin','support','financial') DEFAULT 'admin' COMMENT 'Kullanıcı Rolü',
  `can_manage_customers` tinyint(1) DEFAULT 1 COMMENT 'Can manage customers Bilgisi',
  `can_manage_subscriptions` tinyint(1) DEFAULT 1 COMMENT 'Can manage subscriptions Bilgisi',
  `can_manage_payments` tinyint(1) DEFAULT 1 COMMENT 'Can manage payments Bilgisi',
  `can_view_analytics` tinyint(1) DEFAULT 1 COMMENT 'Can view analytics Bilgisi',
  `status` enum('active','passive') DEFAULT NULL COMMENT 'Admin durumu: active = aktif, passive = pasif',
  `two_factor_enabled` tinyint(1) DEFAULT 0 COMMENT 'Two factor enabled Bilgisi',
  `two_factor_secret` varchar(255) DEFAULT NULL COMMENT 'Two factor secret Bilgisi',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Son Giriş Tarihi',
  `last_ip` varchar(45) DEFAULT NULL COMMENT 'Son Giriş Yapılan IP Adresi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Kayıt Silinme Tarihi (Soft Delete)',
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem yöneticileri ve yetkili kullanıcılar';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yoneticiler`
--

LOCK TABLES `yoneticiler` WRITE;
/*!40000 ALTER TABLE `yoneticiler` DISABLE KEYS */;
INSERT INTO `yoneticiler` VALUES (1,'Bilal Sami Zahit Özgül','admin@prolyn.net','Sistem Yöneticisi','dist/assets/media/avatars/300-3.jpg','$2y$10$V3SyScDs8XkuWr4PWpc19O7xnCoQUgNdOKpTLK.IyjV4LSnwjVChy','superadmin',1,1,1,1,'active',0,NULL,'2026-06-28 15:39:56','::1','2025-11-21 13:05:29','2026-06-28 15:39:56',NULL),(2,'support','support@prolyn.net','Destek Müdürü','dist/assets/media/avatars/300-3.jpg','$2y$10$/f9AGVenAO5Hs4cAxw.TwuEGXce1y5oL9qjOqGSli/ZSlUrN.E3HO','support',0,0,0,1,'active',0,NULL,NULL,NULL,'2025-11-21 13:05:29','2026-06-26 21:05:59',NULL),(3,'finance','finance@prolyn.net','Mali İşler Müdürü','dist/assets/media/avatars/300-3.jpg','$2y$10$/f9AGVenAO5Hs4cAxw.TwuEGXce1y5oL9qjOqGSli/ZSlUrN.E3HO','financial',0,0,1,1,'active',0,NULL,NULL,NULL,'2025-11-21 13:05:29','2026-06-26 21:06:29',NULL);
/*!40000 ALTER TABLE `yoneticiler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `customers`
--

/*!50001 DROP VIEW IF EXISTS `customers`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `customers` AS select `kurumlar`.`customer_id` AS `customer_id`,`kurumlar`.`customer_code` AS `customer_code`,`kurumlar`.`company_name` AS `company_name`,`kurumlar`.`company_email` AS `company_email`,`kurumlar`.`company_phone` AS `company_phone`,`kurumlar`.`contact_person` AS `contact_person`,`kurumlar`.`tax_office` AS `tax_office`,`kurumlar`.`tax_number` AS `tax_number`,`kurumlar`.`billing_address` AS `billing_address`,`kurumlar`.`account_manager_id` AS `account_manager_id`,`kurumlar`.`tenant_alias` AS `tenant_alias`,`kurumlar`.`company_address` AS `company_address`,`kurumlar`.`industry` AS `industry`,`kurumlar`.`musteri_paketi` AS `musteri_paketi`,`kurumlar`.`database_name` AS `database_name`,`kurumlar`.`database_password` AS `database_password`,`kurumlar`.`status` AS `status`,`kurumlar`.`trial_start_date` AS `trial_start_date`,`kurumlar`.`trial_end_date` AS `trial_end_date`,`kurumlar`.`sozlesme_bitis_tarihi` AS `sozlesme_bitis_tarihi`,`kurumlar`.`logo_url` AS `logo_url`,`kurumlar`.`contact_name` AS `contact_name`,`kurumlar`.`contact_email` AS `contact_email`,`kurumlar`.`contact_phone` AS `contact_phone`,`kurumlar`.`created_at` AS `created_at`,`kurumlar`.`updated_at` AS `updated_at`,`kurumlar`.`deleted_at` AS `deleted_at`,`kurumlar`.`created_by` AS `created_by`,`kurumlar`.`allowed_modules` AS `allowed_modules` from `kurumlar` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `setting`
--

/*!50001 DROP VIEW IF EXISTS `setting`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `setting` AS select max(case when `sistem_ayarlari`.`setting_key` = 'site_name' then `sistem_ayarlari`.`setting_value` end) AS `site_name`,max(case when `sistem_ayarlari`.`setting_key` = 'trial_period_days' then `sistem_ayarlari`.`setting_value` end) AS `trial_period_days`,max(case when `sistem_ayarlari`.`setting_key` = 'max_login_attempts' then `sistem_ayarlari`.`setting_value` end) AS `max_login_attempts`,max(case when `sistem_ayarlari`.`setting_key` = 'smtp_host' then `sistem_ayarlari`.`setting_value` end) AS `smtp_host`,max(case when `sistem_ayarlari`.`setting_key` = 'smtp_port' then `sistem_ayarlari`.`setting_value` end) AS `smtp_port`,max(case when `sistem_ayarlari`.`setting_key` = 'smtp_user' then `sistem_ayarlari`.`setting_value` end) AS `smtp_user`,max(case when `sistem_ayarlari`.`setting_key` = 'smtp_pass' then `sistem_ayarlari`.`setting_value` end) AS `smtp_pass`,max(case when `sistem_ayarlari`.`setting_key` = 'maintenance_mode' then `sistem_ayarlari`.`setting_value` end) AS `maintenance_mode` from `sistem_ayarlari` */;
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

-- Dump completed on 2026-06-28 20:44:48
