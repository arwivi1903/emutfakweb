<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Create website_modules
    $conn->exec("CREATE TABLE IF NOT EXISTS website_modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(100),
        status TINYINT(1) DEFAULT 1,
        order_no INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "website_modules table created.\n";

    // Create website_features
    $conn->exec("CREATE TABLE IF NOT EXISTS website_features (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(100),
        status TINYINT(1) DEFAULT 1,
        order_no INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "website_features table created.\n";
    
    // Insert initial data into sistem_ayarlari
    $settings = [
        ['key' => 'website_hero_title', 'value' => 'İşletmenizi Dijitalleştirin, Geleceğe Taşıyın'],
        ['key' => 'website_hero_subtitle', 'value' => 'Prolyn ERP ile tüm iş süreçlerinizi tek bir platformdan yönetin. Bulut tabanlı, güvenli ve esnek yapısıyla işletmenizi bir adım öteye taşıyın.'],
        ['key' => 'website_footer_text', 'value' => 'Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız. Hız, güvenlik ve esnekliği bir arada sunuyoruz.'],
        ['key' => 'seo_title', 'value' => 'Prolyn - Yeni Nesil Bulut ERP Sistemi'],
        ['key' => 'seo_description', 'value' => 'Prolyn ERP ile işletmenizin tüm süreçlerini tek bir ekrandan yönetin. 30 gün ücretsiz deneyin!'],
        ['key' => 'seo_keywords', 'value' => 'erp, bulut erp, ön muhasebe, crm, insan kaynakları']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO sistem_ayarlari (ayar_key, ayar_value) VALUES (:key, :value)");
    foreach ($settings as $setting) {
        $stmt->execute(['key' => $setting['key'], 'value' => $setting['value']]);
    }
    echo "sistem_ayarlari populated.\n";
    
    // Insert initial module data so it's not empty
    $modules = [
        ['Müşteri İlişkileri (CRM)', 'Müşteri görüşmeleri, teklifler, fırsatlar ve satış süreçlerini tek bir merkezden yönetin.', 'bi-people'],
        ['Ön Muhasebe & Finans', 'Fatura kesimi, gelir-gider takibi, cari hesaplar ve banka entegrasyonları ile finansal kontrolü sağlayın.', 'bi-calculator'],
        ['İnsan Kaynakları', 'Personel özlük dosyaları, izin takibi, bordrolama ve performans değerlendirme süreçlerini dijitalleştirin.', 'bi-person-badge'],
        ['Proje Yönetimi', 'Görev atamaları, zaman takibi, proje bütçelendirme ve ekip içi iletişimi kolaylaştırın.', 'bi-diagram-3']
    ];
    $stmtMod = $conn->prepare("INSERT INTO website_modules (title, description, icon, order_no) VALUES (?, ?, ?, ?)");
    foreach ($modules as $index => $mod) {
        $stmtMod->execute([$mod[0], $mod[1], $mod[2], $index + 1]);
    }
    echo "website_modules populated.\n";
    
    // Insert initial feature data
    $features = [
        ['Bulut Tabanlı Mimari', 'Herhangi bir donanım maliyeti olmadan, internet olan her yerden sisteminize erişin. Verileriniz güvenli sunucularda barındırılır ve düzenli olarak yedeklenir.', 'bi-cloud'],
        ['Yüksek Güvenlik Standartları', '256-bit SSL şifreleme, iki aşamalı doğrulama (2FA) ve rol tabanlı yetkilendirme ile verileriniz her zaman güvende.', 'bi-shield-check'],
        ['Mobil Uyumlu Kullanım', 'Responsive tasarımı sayesinde akıllı telefon, tablet ve bilgisayarlardan kesintisiz kullanım deneyimi yaşayın.', 'bi-phone']
    ];
    $stmtFeat = $conn->prepare("INSERT INTO website_features (title, description, icon, order_no) VALUES (?, ?, ?, ?)");
    foreach ($features as $index => $feat) {
        $stmtFeat->execute([$feat[0], $feat[1], $feat[2], $index + 1]);
    }
    echo "website_features populated.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
