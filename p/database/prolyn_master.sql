-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 04 Şub 2026, 19:04:06
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
-- Veritabanı: `prolyn_master`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `activity_logs`
--

CREATE TABLE `activity_logs` (
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Kayıt tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admins`
--

CREATE TABLE `admins` (
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `admin_pic` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','support','financial') DEFAULT 'admin',
  `can_manage_customers` tinyint(1) DEFAULT 1,
  `can_manage_subscriptions` tinyint(1) DEFAULT 1,
  `can_manage_payments` tinyint(1) DEFAULT 1,
  `can_view_analytics` tinyint(1) DEFAULT 1,
  `status` enum('active','passive') DEFAULT NULL COMMENT 'Admin durumu: active = aktif, passive = pasif',
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem yöneticileri ve yetkili kullanıcılar';

--
-- Tablo döküm verisi `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `full_name`, `admin_pic`, `password_hash`, `role`, `can_manage_customers`, `can_manage_subscriptions`, `can_manage_payments`, `can_view_analytics`, `status`, `two_factor_enabled`, `two_factor_secret`, `last_login`, `last_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'admin@emutfak.com', 'Sistem Yöneticisi', 'dist/assets/media/avatars/300-3.jpg', '$2y$10$/f9AGVenAO5Hs4cAxw.TwuEGXce1y5oL9qjOqGSli/ZSlUrN.E3HO', 'superadmin', 1, 1, 1, 1, 'active', 0, NULL, '2026-02-04 17:47:41', '::1', '2025-11-21 13:05:29', '2026-02-04 17:47:41', NULL),
(2, 'support', 'support@emutfak.com', 'Destek Müdürü', 'dist/assets/media/avatars/300-3.jpg', '$2y$10$/f9AGVenAO5Hs4cAxw.TwuEGXce1y5oL9qjOqGSli/ZSlUrN.E3HO', 'support', 0, 0, 0, 1, 'active', 0, NULL, NULL, NULL, '2025-11-21 13:05:29', '2025-12-15 16:43:10', NULL),
(3, 'finance', 'finance@emutfak.com', 'Mali İşler Müdürü', 'dist/assets/media/avatars/300-3.jpg', '$2y$10$/f9AGVenAO5Hs4cAxw.TwuEGXce1y5oL9qjOqGSli/ZSlUrN.E3HO', 'financial', 0, 0, 1, 1, 'active', 0, NULL, NULL, NULL, '2025-11-21 13:05:29', '2025-12-15 16:43:53', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `log_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 3 COMMENT '1=Error, 2=Warning, 3=Admin Action, 4=Info, 5=Debug',
  `log_description` varchar(500) NOT NULL COMMENT 'Action description',
  `log_status` enum('success','error','warning','pending','failed','info') DEFAULT 'info',
  `admin_id` int(11) NOT NULL COMMENT 'Admin who performed the action',
  `ip_address` varchar(45) DEFAULT '0.0.0.0',
  `user_agent` varchar(255) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT 'GET',
  `request_path` varchar(255) DEFAULT NULL,
  `old_value` mediumtext DEFAULT NULL,
  `new_value` mediumtext DEFAULT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `additional_data` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yönetici panelinde yapılan tüm işlemlerin log kayıtları';

--
-- Tablo döküm verisi `admin_logs`
--

INSERT INTO `admin_logs` (`log_id`, `log_type`, `log_description`, `log_status`, `admin_id`, `ip_address`, `user_agent`, `request_method`, `request_path`, `old_value`, `new_value`, `entity_type`, `entity_id`, `additional_data`, `created_at`) VALUES
(1, 2, 'GIRIS_BASARISIZ', 'error', 0, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, '{\"reason\":\"user_not_found\",\"identifier\":\"bilal@bilal.com\",\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\"}', '2026-02-01 20:59:40'),
(2, 3, 'GIRIS_BASARILI - Admin giriş başarılı - admin@emutfak.com', 'success', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, NULL, '2026-02-01 21:01:24'),
(3, 3, 'UPDATE_LOGIN_INFO - Son giriş bilgisi güncellendi (IP dahil: ::1)', 'info', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, NULL, '2026-02-01 21:01:24'),
(4, 3, 'GIRIS_BASARILI', 'success', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, '{\"email\":\"admin@emutfak.com\",\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"full_name\":\"Sistem Yöneticisi\",\"role\":\"superadmin\"}', '2026-02-01 21:01:24'),
(5, 2, 'GIRIS_BASARISIZ', 'error', 0, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, '{\"reason\":\"user_not_found\",\"identifier\":\"bilal@bilal.com\",\"extra\":null,\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\"}', '2026-02-04 17:47:11'),
(6, 3, 'UPDATE_LOGIN_INFO - Son giriş bilgisi güncellendi (IP: ::1)', 'info', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, NULL, '2026-02-04 17:47:41'),
(7, 3, 'GIRIS_BASARILI - Admin giriş başarılı - admin@emutfak.com', 'success', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, NULL, '2026-02-04 17:47:41'),
(8, 3, 'GIRIS_BASARILI', 'success', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'POST', '/prolynweb/p/config/islem.php', NULL, NULL, NULL, NULL, '{\"email\":\"admin@emutfak.com\",\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"full_name\":\"Sistem Yöneticisi\",\"role\":\"superadmin\"}', '2026-02-04 17:47:41');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin_logs_backup`
--

CREATE TABLE `admin_logs_backup` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `log_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 3 COMMENT '1=Error, 2=Warning, 3=Admin Action, 4=Info, 5=Debug',
  `log_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Action description',
  `log_status` enum('success','error','warning','pending','failed','info') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'info' COMMENT 'Operation status',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Admin who performed the action',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0.0.0.0' COMMENT 'IP address (IPv4 or IPv6)',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Browser/Client information',
  `request_method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'GET' COMMENT 'HTTP Method: GET, POST, PUT, DELETE, PATCH',
  `request_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Request URI path',
  `old_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Old value (JSON format)',
  `new_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'New value (JSON format)',
  `entity_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Table/Entity type: customers, subscriptions, etc.',
  `entity_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Record ID being modified',
  `additional_data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Additional data (JSON): changed_fields, metadata, etc.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Log creation timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin loglarının arşivlenmiş (yedek) kayıtları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin_roles`
--

CREATE TABLE `admin_roles` (
  `admin_id` int(11) NOT NULL COMMENT 'Admin ID',
  `role_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Rol ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin kullanıcılar ile roller arasındaki ilişki';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT NULL COMMENT 'Duyuru türü: bilgi, başarı, uyarı, kritik',
  `target_audience` enum('all','admins','customers','specific') DEFAULT NULL COMMENT 'Hedef kitle tanımı',
  `target_customer_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of customer IDs if specific',
  `is_active` tinyint(1) DEFAULT 1,
  `show_from` timestamp NULL DEFAULT NULL,
  `show_until` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `api_keys`
--

CREATE TABLE `api_keys` (
  `api_key_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `api_secret` varchar(64) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON: read, write, delete',
  `rate_limit` int(11) DEFAULT 1000,
  `requests_used` int(11) DEFAULT 0,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','revoked','expired') DEFAULT NULL COMMENT 'API anahtar durumu: active = aktif, revoked = iptal, expired = süresi dolmuş',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri ve sistem entegrasyonları için API anahtarları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_type` varchar(20) NOT NULL COMMENT 'İşlemi yapan kullanıcı tipi (admin, customer, system)',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'İşlemi yapan kullanıcı ID',
  `action` varchar(50) NOT NULL COMMENT 'Yapılan işlem (create, update, delete, login vb.)',
  `table_name` varchar(100) NOT NULL COMMENT 'İşlem yapılan tablo adı',
  `record_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Etkilenen kayıt ID',
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'İşlem öncesi veri',
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'İşlem sonrası veri',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Kullanıcı IP adresi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'İşlem zamanı'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `billing_addresses`
--

CREATE TABLE `billing_addresses` (
  `address_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `address_type` enum('billing','shipping') DEFAULT NULL COMMENT 'Adres türü: fatura veya teslimat',
  `company_name` varchar(255) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2 code',
  `tax_id` varchar(50) DEFAULT NULL COMMENT 'VAT/Tax ID number',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilere ait fatura ve teslimat adresleri';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_spend` decimal(10,2) DEFAULT 0.00,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='İndirim kuponları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_code` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_email` varchar(255) NOT NULL,
  `company_phone` varchar(20) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `database_name` varchar(100) DEFAULT NULL,
  `database_password` varchar(255) DEFAULT NULL,
  `status` enum('active','passive','suspended') DEFAULT NULL COMMENT 'Müşteri durumu: active = aktif, passive = pasif, suspended = askıya alındı',
  `trial_start_date` date DEFAULT NULL,
  `trial_end_date` date DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemi kullanan müşteri firmalar';

--
-- Tablo döküm verisi `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_code`, `company_name`, `company_email`, `company_phone`, `company_address`, `industry`, `database_name`, `database_password`, `status`, `trial_start_date`, `trial_end_date`, `logo_url`, `contact_name`, `contact_email`, `contact_phone`, `created_at`, `updated_at`, `deleted_at`, `created_by`) VALUES
(1, 'elit', 'Elit Mutfak Ltd.', 'info@elitmutfak.com', '+90 212 123 4567', 'İstanbul, Türkiye', 'Catering', 'prolyn_k1', 'RootPass1903!', 'active', '2025-12-01', '2026-03-31', NULL, 'Ahmet Yılmaz', 'ahmet@elitmutfak.com', NULL, '2025-11-21 13:05:29', '2025-12-26 20:34:13', NULL, 1),
(2, 'sofra', 'Sofra Aşçılık Hizmetleri', 'contact@sofra.com.tr', '+90 532 987 6543', 'Ankara, Türkiye', 'Restaurant', 'prolyn_k2', 'RootPass1903!', 'active', '2025-10-22', '2025-12-21', NULL, 'Fatma Demir', 'fatma@sofra.com.tr', NULL, '2025-11-21 13:05:29', '2025-12-26 20:34:18', NULL, 1),
(3, 'ankara', 'Ankara Mutfak Yönetimi', 'admin@ankaramutfak.com', '+90 312 555 8899', 'Ankara, Türkiye', 'Catering', 'prolyn_k3', 'RootPass1903!', 'active', '2025-09-22', '2026-01-20', NULL, 'Mehmet Şahin', 'mehmet@ankaramutfak.com', NULL, '2025-11-21 13:05:29', '2025-12-26 20:34:28', NULL, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_notes`
--

CREATE TABLE `customer_notes` (
  `note_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` int(11) NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `note_text` text NOT NULL,
  `is_urgent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteriler hakkında dahili admin notları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `customer_users`
--

CREATE TABLE `customer_users` (
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','manager','user') DEFAULT 'user',
  `status` enum('active','passive') DEFAULT NULL COMMENT 'Kullanıcı durumu: active = aktif, passive = pasif',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilere bağlı alt kullanıcı hesapları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `email_queue`
--

CREATE TABLE `email_queue` (
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `email_templates`
--

CREATE TABLE `email_templates` (
  `template_id` int(11) NOT NULL,
  `template_key` varchar(100) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `body_text` text DEFAULT NULL,
  `variables` text DEFAULT NULL COMMENT 'JSON: Available variables like {{company_name}}',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemde kullanılan e-posta şablonları';

--
-- Tablo döküm verisi `email_templates`
--

INSERT INTO `email_templates` (`template_id`, `template_key`, `template_name`, `subject`, `body_html`, `body_text`, `variables`, `status`, `created_at`, `updated_at`) VALUES
(1, 'welcome_email', 'Hoş Geldiniz', '{{company_name}} - Hoş Geldiniz!', '<h1>Merhaba {{contact_name}},</h1><p>{{company_name}} ailesine hoş geldiniz!</p>', 'Merhaba {{contact_name}}, {{company_name}} ailesine hoş geldiniz!', '{\"company_name\": \"Şirket Adı\", \"contact_name\": \"İletişim Adı\"}', 'active', '2025-12-21 20:36:32', '2025-12-21 20:36:32'),
(2, 'trial_ending', 'Deneme Süresi Bitiyor', 'Deneme süreniz {{days_left}} gün içinde sona eriyor', '<h1>Merhaba {{contact_name}},</h1><p>Deneme süreniz {{days_left}} gün içinde sona erecek.</p>', 'Merhaba {{contact_name}}, Deneme süreniz {{days_left}} gün içinde sona erecek.', '{\"contact_name\": \"İletişim Adı\", \"days_left\": \"Kalan Gün\"}', 'active', '2025-12-21 20:36:32', '2025-12-21 20:36:32'),
(3, 'payment_failed', 'Ödeme Başarısız', 'Ödemeniz Başarısız Oldu', '<h1>Merhaba {{contact_name}},</h1><p>{{amount}} TL tutarındaki ödemeniz başarısız oldu.</p>', 'Merhaba {{contact_name}}, {{amount}} TL tutarındaki ödemeniz başarısız oldu.', '{\"contact_name\": \"İletişim Adı\", \"amount\": \"Tutar\"}', 'active', '2025-12-21 20:36:32', '2025-12-21 20:36:32'),
(4, 'subscription_renewed', 'Abonelik Yenilendi', 'Aboneliğiniz Başarıyla Yenilendi', '<h1>Merhaba {{contact_name}},</h1><p>{{plan_name}} paketiniz {{end_date}} tarihine kadar yenilendi.</p>', 'Merhaba {{contact_name}}, {{plan_name}} paketiniz {{end_date}} tarihine kadar yenilendi.', '{\"contact_name\": \"İletişim Adı\", \"plan_name\": \"Paket Adı\", \"end_date\": \"Bitiş Tarihi\"}', 'active', '2025-12-21 20:36:32', '2025-12-21 20:36:32');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `feature_usage`
--

CREATE TABLE `feature_usage` (
  `usage_id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `feature_key` varchar(100) NOT NULL COMMENT 'api_calls, users, storage, etc.',
  `usage_count` int(11) DEFAULT 0,
  `limit_count` int(11) DEFAULT NULL COMMENT 'NULL = unlimited',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri bazlı özellik kullanım ve limit takibi';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_type` varchar(20) NOT NULL COMMENT 'Dosya sahibi tipi',
  `owner_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Dosya sahibi ID',
  `file_path` varchar(255) NOT NULL COMMENT 'Dosya yolu',
  `file_name` varchar(255) NOT NULL COMMENT 'Dosya adı',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'Dosya tipi',
  `file_size` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Dosya boyutu (byte)',
  `storage` varchar(20) DEFAULT 'local' COMMENT 'Depolama türü (local, s3)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sisteme yüklenen dosyaların metadata bilgileri';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'TRY',
  `status` enum('draft','issued','paid','cancelled') DEFAULT NULL COMMENT 'Fatura durumu: taslak, kesildi, ödendi, iptal',
  `pdf_url` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilere kesilen fatura kayıtları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` bigint(20) NOT NULL COMMENT 'Giriş denemesi ID',
  `email` varchar(255) DEFAULT NULL COMMENT 'Giriş yapılmaya çalışılan e-posta',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'İstek yapılan IP adresi',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Tarayıcı bilgisi',
  `attempt_type` enum('admin','customer') DEFAULT NULL COMMENT 'Giriş türü: admin = yönetici, customer = müşteri',
  `status` enum('success','failed','blocked') DEFAULT NULL COMMENT 'Deneme sonucu: success = başarılı, failed = hatalı, blocked = engellendi',
  `failure_reason` varchar(255) DEFAULT NULL COMMENT 'Başarısızlık nedeni',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Deneme zamanı'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin ve müşteri giriş denemelerinin kayıtları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `maintenance_log`
--

CREATE TABLE `maintenance_log` (
  `id` int(11) NOT NULL COMMENT 'Bakım kayıt ID',
  `title` varchar(255) NOT NULL COMMENT 'Bakım başlığı veya kısa açıklama',
  `description` text DEFAULT NULL COMMENT 'Bakım kapsamı ve detaylı bilgi',
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled' COMMENT 'Bakım durumu: Planlandı, Devam Ediyor, Tamamlandı, İptal Edildi',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium' COMMENT 'Önem derecesi',
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Planlanan başlangıç zamanı',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Tahmini bitiş zamanı',
  `is_active` tinyint(1) DEFAULT 0 COMMENT 'Şu an aktif olarak erişim kısıtlanıyor mu? (1: Evet, 0: Hayır)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kaydın oluşturulma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem genel bakım ve teknik çalışma takvimi';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(20) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT NULL COMMENT 'Bildirim türü: bilgi, başarı, uyarı, hata',
  `is_read` tinyint(1) DEFAULT 0,
  `related_entity_type` varchar(50) DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin ve kullanıcı sistem bildirimleri';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notification_queue`
--

CREATE TABLE `notification_queue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `channel` varchar(20) NOT NULL COMMENT 'Bildirim kanalı (email, sms, push, webhook)',
  `recipient` varchar(255) NOT NULL COMMENT 'Alıcı bilgisi',
  `subject` varchar(255) DEFAULT NULL COMMENT 'Başlık',
  `message` text NOT NULL COMMENT 'Mesaj içeriği',
  `status` varchar(20) DEFAULT 'pending' COMMENT 'Gönderim durumu',
  `attempts` tinyint(3) UNSIGNED DEFAULT 0 COMMENT 'Gönderim deneme sayısı',
  `last_error` text DEFAULT NULL COMMENT 'Son hata mesajı',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem bildirimlerinin kuyruk mantığıyla işlendiği tablo';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `package_features`
--

CREATE TABLE `package_features` (
  `feature_id` int(11) NOT NULL,
  `plan_name` enum('basic','pro','enterprise') NOT NULL,
  `plan_display_name` varchar(100) DEFAULT NULL,
  `max_admin_users` int(11) DEFAULT NULL,
  `max_total_users` int(11) DEFAULT NULL,
  `max_storage_gb` int(11) DEFAULT NULL,
  `max_file_size_mb` int(11) DEFAULT NULL,
  `max_files_per_upload` int(11) DEFAULT NULL,
  `api_access` tinyint(1) DEFAULT 0,
  `api_requests_per_month` int(11) DEFAULT NULL,
  `custom_reports` tinyint(1) DEFAULT 0,
  `advanced_analytics` tinyint(1) DEFAULT 0,
  `white_label` tinyint(1) DEFAULT 0,
  `email_campaigns` tinyint(1) DEFAULT 0,
  `bulk_operations` tinyint(1) DEFAULT 0,
  `support_level` enum('email','priority','dedicated') DEFAULT 'email',
  `support_response_hours` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Paketlere tanımlı özellik ve limitler';

--
-- Tablo döküm verisi `package_features`
--

INSERT INTO `package_features` (`feature_id`, `plan_name`, `plan_display_name`, `max_admin_users`, `max_total_users`, `max_storage_gb`, `max_file_size_mb`, `max_files_per_upload`, `api_access`, `api_requests_per_month`, `custom_reports`, `advanced_analytics`, `white_label`, `email_campaigns`, `bulk_operations`, `support_level`, `support_response_hours`, `created_at`) VALUES
(1, 'basic', NULL, 2, 5, 5, 50, 10, 0, 0, 0, 0, 0, 0, 0, 'email', 48, '2025-11-21 13:05:29'),
(2, 'pro', NULL, 5, 25, 50, 100, 25, 1, 10000, 1, 1, 0, 1, 1, 'priority', 24, '2025-11-21 13:05:29'),
(3, 'enterprise', NULL, 999, 999, 999, 999, 999, 1, 999999, 1, 1, 1, 1, 1, 'dedicated', 4, '2025-11-21 13:05:29');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_type` enum('admin','customer') NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Şifre sıfırlama talepleri ve token kayıtları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'TRY',
  `payment_method` enum('credit_card','bank_transfer') NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `card_brand` varchar(20) DEFAULT NULL,
  `bank_code` varchar(10) DEFAULT NULL,
  `iban` varchar(50) DEFAULT NULL,
  `reference_code` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_period_start` date DEFAULT NULL,
  `paid_period_end` date DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri ödeme hareketleri ve işlem kayıtları';

--
-- Tablo döküm verisi `payments`
--

INSERT INTO `payments` (`payment_id`, `customer_id`, `subscription_id`, `amount`, `currency`, `payment_method`, `payment_status`, `transaction_id`, `payment_gateway`, `invoice_number`, `card_last4`, `card_brand`, `bank_code`, `iban`, `reference_code`, `notes`, `payment_date`, `paid_period_start`, `paid_period_end`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 299.00, 'TRY', 'credit_card', 'completed', 'TXN20250001', 'iyzico', 'INV001', '4242', 'Visa', NULL, NULL, NULL, NULL, '2025-12-21 20:52:03', NULL, NULL, '0000-00-00 00:00:00', '2025-11-21 13:05:29', '2025-12-21 20:52:03'),
(2, 2, 2, 999.00, 'TRY', 'credit_card', 'completed', 'TXN20250002', 'iyzico', 'INV002', '5555', 'MasterCard', NULL, NULL, NULL, NULL, '2025-12-21 20:52:03', NULL, NULL, '0000-00-00 00:00:00', '2025-10-22 13:05:29', '2025-12-21 20:52:03'),
(3, 3, 3, 999.00, 'TRY', 'bank_transfer', 'completed', 'TXN20250003', NULL, 'INV003', NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-22 13:05:29', NULL, NULL, '0000-00-00 00:00:00', '2025-09-22 13:05:29', '2025-11-21 13:05:29'),
(4, 2, 2, 999.00, 'TRY', 'credit_card', 'pending', 'TXN20250004', 'iyzico', 'INV004', '5555', 'MasterCard', NULL, NULL, NULL, NULL, '2025-12-21 20:52:03', NULL, NULL, '0000-00-00 00:00:00', '2025-11-21 13:05:29', '2025-12-21 20:52:03');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Yetki anahtarı',
  `description` varchar(255) DEFAULT NULL COMMENT 'Yetki açıklaması'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemde tanımlı yetkiler';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Rol adı',
  `description` varchar(255) DEFAULT NULL COMMENT 'Rol açıklaması',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem rollerinin tanımlandığı tablo';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rollere atanan yetkiler';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sales_content`
--

CREATE TABLE `sales_content` (
  `sales_id` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `title_tr` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `content_tr` longtext DEFAULT NULL,
  `content_en` longtext DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 999,
  `status` enum('draft','published','archived') DEFAULT NULL COMMENT 'İçerik durumu: taslak, yayında, arşivlenmiş',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Satış, pazarlama ve tanıtım içeriklerinin tutulduğu tablo';

--
-- Tablo döküm verisi `sales_content`
--

INSERT INTO `sales_content` (`sales_id`, `section`, `title_tr`, `title_en`, `content_tr`, `content_en`, `icon`, `image_url`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'hero', 'Mutfak Işletmeleri İçin Kapsamlı ERP Çözümü', 'Complete ERP Solution for Kitchen Businesses', 'Müşteri yönetimi, kontrat takibi, ödeme takibi ve daha fazlası tek platformda', 'Customer management, contract tracking, payment management and more on a single platform', 'fas fa-kitchen-set', '', 1, 'draft', '2025-11-21 13:05:29', '2025-11-22 12:12:51'),
(2, 'features', 'Müşteri Yönetimi', 'Customer Management', 'Müşteri bilgilerini merkezi olarak yönetin ve takip edin', 'Manage and track customer information centrally', 'fas fa-users', NULL, 10, 'draft', '2025-11-21 13:05:29', '2025-11-21 13:05:29'),
(3, 'features', 'Kontrat & Sözleşmeler', 'Contracts & Agreements', 'Kontrat ve sözleşmeleri kolayca oluşturun ve izleyin', 'Create and monitor contracts and agreements easily', 'fas fa-file-contract', NULL, 20, 'draft', '2025-11-21 13:05:29', '2025-11-21 13:05:29'),
(4, 'features', 'Ödeme Takibi', 'Payment Tracking', 'Ödemeleri ve tahsilatları gerçek zamanlı izleyin', 'Track payments and collections in real-time', 'fas fa-credit-card', NULL, 30, 'draft', '2025-11-21 13:05:29', '2025-11-21 13:05:29'),
(5, 'features', 'Raporlar & Analizler', 'Reports & Analytics', 'Detaylı raporlar ve analizlerle işletmenizi daha iyi anlayın', 'Understand your business better with detailed reports and analytics', 'fas fa-chart-bar', NULL, 40, 'draft', '2025-11-21 13:05:29', '2025-11-21 13:05:29'),
(6, 'features', 'Kullanıcı Yönetimi', 'User Management', 'Ekip üyelerinizi yönetin ve izinleri kontrol edin', 'Manage team members and control permissions', 'fas fa-user-tie', NULL, 50, 'draft', '2025-11-21 13:05:29', '2025-11-21 13:05:29'),
(7, 'features', 'Bulut Depolama', 'Cloud Storage', 'Verilerinizi güvenli bulut ortamında saklayın', 'Store your data securely in the cloud', 'fas fa-cloud', '', 60, 'draft', '2025-11-21 13:05:29', '2025-11-22 12:12:50');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_type` enum('admin','customer') NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data` text DEFAULT NULL COMMENT 'Serialized session data',
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User session management';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `plan_name` enum('basic','pro','enterprise') NOT NULL,
  `num_branches` int(11) NOT NULL,
  `price_per_month` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `billing_cycle` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `next_renewal_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `renewal_notified` tinyint(1) DEFAULT 0,
  `final_reminder_sent` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL COMMENT 'Abonelik durumu (active, cancelled, expired vb.)',
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_date` timestamp NULL DEFAULT NULL,
  `last_payment_date` timestamp NULL DEFAULT NULL,
  `last_payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri abonelik planları ve süre bilgilerinin tutulduğu tablo';

--
-- Tablo döküm verisi `subscriptions`
--

INSERT INTO `subscriptions` (`subscription_id`, `customer_id`, `plan_name`, `num_branches`, `price_per_month`, `total_price`, `billing_cycle`, `start_date`, `end_date`, `next_renewal_date`, `auto_renew`, `renewal_notified`, `final_reminder_sent`, `status`, `cancellation_reason`, `cancelled_date`, `last_payment_date`, `last_payment_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'basic', 1, 299.00, 299.00, 12, '2025-11-21', '2026-11-21', NULL, 1, 0, 0, 'active', NULL, '2025-11-24 11:35:37', '0000-00-00 00:00:00', NULL, '2025-11-21 13:05:29', '2025-11-24 11:35:37'),
(2, 2, 'pro', 3, 999.00, 999.00, 30, '2025-10-22', '2025-12-21', NULL, 1, 0, 0, 'active', NULL, '2025-11-21 13:05:29', '0000-00-00 00:00:00', NULL, '2025-11-21 13:05:29', '2025-11-21 13:05:29'),
(3, 3, 'pro', 2, 999.00, 999.00, 30, '2025-09-22', '2026-01-20', NULL, 1, 0, 0, 'active', NULL, '2025-11-21 13:05:29', '0000-00-00 00:00:00', NULL, '2025-11-21 13:05:29', '2025-11-21 13:05:29');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `subscription_history`
--

CREATE TABLE `subscription_history` (
  `history_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Geçmiş kayıt benzersiz kimliği',
  `subscription_id` int(11) NOT NULL COMMENT 'İlgili abonelik ID',
  `customer_id` int(11) NOT NULL,
  `old_plan_name` varchar(100) DEFAULT NULL COMMENT 'Önceki paket adı',
  `new_plan_name` varchar(100) DEFAULT NULL COMMENT 'Geçiş yapılan yeni paket adı',
  `change_type` enum('upgrade','downgrade','renewal','cancellation','trial_start') NOT NULL COMMENT 'Değişim türü: Yükseltme, Düşürme, Yenileme, İptal, Deneme Başlatma',
  `reason` text DEFAULT NULL COMMENT 'Değişiklik nedeni veya sistem notu',
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'İşlemi gerçekleştiren yönetici ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'İşlem tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşterilerin abonelik değişim ve geçmiş kayıtları';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `support_tickets`
--

CREATE TABLE `support_tickets` (
  `ticket_id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` varchar(50) DEFAULT NULL COMMENT 'Destek talebi önceliği (low, medium, high)',
  `status` varchar(50) DEFAULT NULL COMMENT 'Destek talebinin durumu (open, pending, closed vb.)',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Müşteri destek taleplerinin ve durumlarının tutulduğu tablo';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `system_errors`
--

CREATE TABLE `system_errors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source` varchar(50) NOT NULL COMMENT 'Hata kaynağı (api, cron, ui)',
  `message` text NOT NULL COMMENT 'Hata mesajı',
  `stack_trace` longtext DEFAULT NULL COMMENT 'Hata detayları',
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT 'Çözüm zamanı',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistem genelinde oluşan kritik hatalar';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `system_events`
--

CREATE TABLE `system_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_name` varchar(100) NOT NULL COMMENT 'Olay adı',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Olay verisi',
  `status` varchar(20) DEFAULT 'pending' COMMENT 'İşlenme durumu',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) DEFAULT NULL COMMENT 'Ayar anahtarı (benzersiz tanım)',
  `setting_value` text DEFAULT NULL COMMENT 'Ayar değeri',
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `category` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT NULL COMMENT '1: Ön yüzden erişilebilir, 0: Sadece sistem içi',
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistemin genel yapılandırma ve konfigürasyon ayarlarının tutulduğu tablo';

--
-- Tablo döküm verisi `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_public`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'Emutfak', 'string', 'general', 'Platform adı', 1, NULL, '2025-12-21 20:36:32'),
(2, 'trial_period_days', '30', 'number', 'subscription', 'Deneme süresi (gün)', 0, NULL, '2025-12-21 20:36:32'),
(3, 'max_login_attempts', '5', 'number', 'security', 'Maksimum giriş denemesi', 0, NULL, '2025-12-21 20:36:32'),
(4, 'smtp_host', 'smtp.gmail.com', 'string', 'email', 'SMTP sunucu adresi', 0, NULL, '2025-12-21 20:36:32'),
(5, 'smtp_port', '587', 'number', 'email', 'SMTP port', 0, NULL, '2025-12-21 20:36:32'),
(6, 'currency', 'TRY', 'string', 'general', 'Para birimi', 1, NULL, '2025-12-21 20:36:32'),
(7, 'timezone', 'Europe/Istanbul', 'string', 'general', 'Saat dilimi', 0, NULL, '2025-12-21 20:36:32');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tax_rates`
--

CREATE TABLE `tax_rates` (
  `tax_id` int(11) NOT NULL,
  `tax_name` varchar(50) NOT NULL COMMENT 'KDV, VAT, GST vb.',
  `tax_percent` decimal(5,2) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vergi oranları yönetimi';

--
-- Tablo döküm verisi `tax_rates`
--

INSERT INTO `tax_rates` (`tax_id`, `tax_name`, `tax_percent`, `is_default`, `created_at`) VALUES
(1, 'KDV %20', 20.00, 1, '2025-12-21 22:18:36');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `reply_id` bigint(20) NOT NULL COMMENT 'Yanıt benzersiz ID',
  `ticket_id` int(11) DEFAULT NULL COMMENT 'Bağlı olduğu destek talebi ID',
  `replied_by_admin` int(11) DEFAULT NULL COMMENT 'Yanıtlayan admin ID (varsa)',
  `replied_by_customer` int(11) DEFAULT NULL COMMENT 'Yanıtlayan müşteri kullanıcı ID (varsa)',
  `message` text DEFAULT NULL COMMENT 'Yanıt mesaj içeriği',
  `is_internal` tinyint(1) DEFAULT NULL COMMENT '1: Dahili (sadece admin), 0: Müşteriye açık',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Yanıta eklenen dosyalar (JSON)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Yanıt oluşturulma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_type` varchar(20) NOT NULL COMMENT 'Kullanıcı tipi',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Kullanıcı ID',
  `activity` varchar(100) NOT NULL COMMENT 'Yapılan işlem / aktivite',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ek aktivite verileri',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP adresi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `webhooks`
--

CREATE TABLE `webhooks` (
  `webhook_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of subscribed event types',
  `secret` varchar(64) DEFAULT NULL COMMENT 'HMAC secret for signature verification',
  `is_active` tinyint(1) DEFAULT 1,
  `retry_count` int(11) DEFAULT 3,
  `timeout_seconds` int(11) DEFAULT 30,
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `last_response_code` int(11) DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `webhook_logs`
--

CREATE TABLE `webhook_logs` (
  `log_id` bigint(20) NOT NULL,
  `webhook_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `response_code` int(11) DEFAULT NULL,
  `response_body` text DEFAULT NULL,
  `duration_ms` int(11) DEFAULT NULL COMMENT 'Request duration in milliseconds',
  `status` enum('pending','success','failed') DEFAULT NULL COMMENT 'Webhook sonucu: beklemede, başarılı, hatalı',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_customer_date` (`customer_id`,`created_at`),
  ADD KEY `idx_customer_action` (`customer_id`,`action`),
  ADD KEY `idx_action_date` (`action`,`created_at`);

--
-- Tablo için indeksler `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_role` (`role`);

--
-- Tablo için indeksler `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_log_type` (`log_type`),
  ADD KEY `idx_log_status` (`log_status`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created_at` (`created_at`);
ALTER TABLE `admin_logs` ADD FULLTEXT KEY `ft_log_search` (`log_description`);
ALTER TABLE `admin_logs` ADD FULLTEXT KEY `ft_admin_log` (`log_description`);

--
-- Tablo için indeksler `admin_logs_backup`
--
ALTER TABLE `admin_logs_backup`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_log_type` (`log_type`),
  ADD KEY `idx_log_status` (`log_status`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_log_type_date` (`log_type`,`created_at`),
  ADD KEY `idx_admin_date` (`admin_id`,`created_at`);
ALTER TABLE `admin_logs_backup` ADD FULLTEXT KEY `ft_log_description` (`log_description`);

--
-- Tablo için indeksler `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`admin_id`,`role_id`),
  ADD KEY `fk_admin_roles_role` (`role_id`);

--
-- Tablo için indeksler `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_show_dates` (`show_from`,`show_until`),
  ADD KEY `idx_target_audience` (`target_audience`),
  ADD KEY `idx_active_dates` (`is_active`,`show_from`,`show_until`),
  ADD KEY `fk_announcements_admin` (`created_by`);

--
-- Tablo için indeksler `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`api_key_id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_customer_status` (`customer_id`,`status`),
  ADD KEY `idx_api_key_status` (`status`),
  ADD KEY `idx_api_customer_status` (`customer_id`,`status`);

--
-- Tablo için indeksler `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_table` (`table_name`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`);

--
-- Tablo için indeksler `billing_addresses`
--
ALTER TABLE `billing_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_is_default` (`is_default`),
  ADD KEY `idx_country` (`country`),
  ADD KEY `idx_customer_default` (`customer_id`,`is_default`);

--
-- Tablo için indeksler `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_coupon_code` (`code`);

--
-- Tablo için indeksler `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `company_email` (`company_email`),
  ADD UNIQUE KEY `database_name` (`database_name`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_database_name` (`database_name`),
  ADD KEY `idx_company_email` (`company_email`),
  ADD KEY `fk_customers_created_by` (`created_by`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_customer_status_created` (`status`,`created_at`);
ALTER TABLE `customers` ADD FULLTEXT KEY `ft_company_search` (`company_name`,`contact_name`);
ALTER TABLE `customers` ADD FULLTEXT KEY `ft_customer_search` (`company_name`,`contact_name`);

--
-- Tablo için indeksler `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `fk_notes_customer` (`customer_id`),
  ADD KEY `fk_notes_admin` (`admin_id`);

--
-- Tablo için indeksler `customer_users`
--
ALTER TABLE `customer_users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_email` (`email`);

--
-- Tablo için indeksler `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`queue_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_priority_status` (`priority`,`status`),
  ADD KEY `idx_to_email` (`to_email`),
  ADD KEY `idx_pending_scheduled` (`status`,`scheduled_at`);

--
-- Tablo için indeksler `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `template_key` (`template_key`);
ALTER TABLE `email_templates` ADD FULLTEXT KEY `ft_template_search` (`template_name`,`subject`);
ALTER TABLE `email_templates` ADD FULLTEXT KEY `ft_email_template` (`template_name`,`subject`);

--
-- Tablo için indeksler `feature_usage`
--
ALTER TABLE `feature_usage`
  ADD PRIMARY KEY (`usage_id`),
  ADD UNIQUE KEY `unique_customer_feature_period` (`customer_id`,`feature_key`,`period_start`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_period` (`period_start`,`period_end`),
  ADD KEY `idx_feature_key` (`feature_key`);

--
-- Tablo için indeksler `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_files_owner` (`owner_type`,`owner_id`);

--
-- Tablo için indeksler `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_payment_id` (`payment_id`);

--
-- Tablo için indeksler `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_email_ip` (`email`,`ip_address`),
  ADD KEY `idx_status_type` (`status`,`attempt_type`),
  ADD KEY `idx_email_status_date` (`email`,`status`,`created_at`);

--
-- Tablo için indeksler `maintenance_log`
--
ALTER TABLE `maintenance_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_active` (`status`,`is_active`);

--
-- Tablo için indeksler `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_notifications_admin_read_date` (`admin_id`,`is_read`,`created_at`),
  ADD KEY `idx_admin_unread` (`admin_id`,`is_read`,`created_at`);

--
-- Tablo için indeksler `notification_queue`
--
ALTER TABLE `notification_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notification_status` (`status`),
  ADD KEY `idx_status_created` (`status`,`created_at`);

--
-- Tablo için indeksler `package_features`
--
ALTER TABLE `package_features`
  ADD PRIMARY KEY (`feature_id`),
  ADD UNIQUE KEY `plan_name` (`plan_name`);

--
-- Tablo için indeksler `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Tablo için indeksler `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_status_date` (`payment_status`,`payment_date`),
  ADD KEY `idx_payment_status_date` (`payment_status`,`payment_date`);

--
-- Tablo için indeksler `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Tablo için indeksler `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Tablo için indeksler `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Tablo için indeksler `sales_content`
--
ALTER TABLE `sales_content`
  ADD PRIMARY KEY (`sales_id`) USING BTREE,
  ADD KEY `idx_section` (`section`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Tablo için indeksler `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_user_type_id` (`user_type`,`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_user_expires` (`user_type`,`user_id`,`expires_at`);

--
-- Tablo için indeksler `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_auto_renew` (`auto_renew`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_plan_name` (`plan_name`),
  ADD KEY `idx_status_end_date` (`status`,`end_date`),
  ADD KEY `idx_auto_renew_date` (`auto_renew`,`next_renewal_date`),
  ADD KEY `idx_sub_status_end` (`status`,`end_date`),
  ADD KEY `idx_sub_auto_renew` (`auto_renew`,`next_renewal_date`),
  ADD KEY `idx_customer_status` (`customer_id`,`status`);

--
-- Tablo için indeksler `subscription_history`
--
ALTER TABLE `subscription_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_subscription_id` (`subscription_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_changed_by` (`changed_by`);

--
-- Tablo için indeksler `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_customer_status_priority` (`customer_id`,`status`,`priority`);
ALTER TABLE `support_tickets` ADD FULLTEXT KEY `ft_ticket_search` (`subject`,`description`);

--
-- Tablo için indeksler `system_errors`
--
ALTER TABLE `system_errors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_errors_created` (`created_at`);

--
-- Tablo için indeksler `system_events`
--
ALTER TABLE `system_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_status` (`status`);

--
-- Tablo için indeksler `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `tax_rates`
--
ALTER TABLE `tax_rates`
  ADD PRIMARY KEY (`tax_id`);

--
-- Tablo için indeksler `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `idx_ticket_id` (`ticket_id`),
  ADD KEY `idx_replied_by_admin` (`replied_by_admin`),
  ADD KEY `idx_replied_by_customer` (`replied_by_customer`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_ticket_date` (`ticket_id`,`created_at`);

--
-- Tablo için indeksler `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_user` (`user_id`);

--
-- Tablo için indeksler `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`webhook_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_customer_active` (`customer_id`,`is_active`);

--
-- Tablo için indeksler `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_webhook_id` (`webhook_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_webhook_status` (`webhook_id`,`status`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `admin_logs_backup`
--
ALTER TABLE `admin_logs_backup`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `api_key_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `billing_addresses`
--
ALTER TABLE `billing_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `customer_notes`
--
ALTER TABLE `customer_notes`
  MODIFY `note_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customer_users`
--
ALTER TABLE `customer_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `feature_usage`
--
ALTER TABLE `feature_usage`
  MODIFY `usage_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `maintenance_log`
--
ALTER TABLE `maintenance_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Bakım kayıt ID';

--
-- Tablo için AUTO_INCREMENT değeri `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `notification_queue`
--
ALTER TABLE `notification_queue`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `package_features`
--
ALTER TABLE `package_features`
  MODIFY `feature_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sales_content`
--
ALTER TABLE `sales_content`
  MODIFY `sales_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `subscription_history`
--
ALTER TABLE `subscription_history`
  MODIFY `history_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Geçmiş kayıt benzersiz kimliği';

--
-- Tablo için AUTO_INCREMENT değeri `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `system_errors`
--
ALTER TABLE `system_errors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `system_events`
--
ALTER TABLE `system_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `tax_rates`
--
ALTER TABLE `tax_rates`
  MODIFY `tax_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `webhook_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `webhook_logs`
--
ALTER TABLE `webhook_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `customer_users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
