-- sistem_ayarlari tablosu tamamen BOŞTU. Bunun iki sonucu vardı:
--   1) db.php'nin $appSettings dizisi hep boş geliyor, her sayfa PHP
--      içindeki sabit (hardcoded) yedek değerlere düşüyordu.
--   2) p/website/seo.php'deki "Kaydet" butonu satır yoksa hiçbir şeyi
--      GÜNCELLEMİYORDU (UPDATE ... WHERE setting_key=? 0 satır etkiliyordu) —
--      admin formu doldurup kaydedince "başarılı" görünüyor ama hiçbir
--      şey kalıcı olmuyordu. (Kod tarafı ayrıca INSERT ... ON DUPLICATE KEY
--      UPDATE'e çevrildi, bkz. p/website/seo.php.)
--
-- Bu satırlar, sitede zaten görünen sabit değerlerle birebir aynı — yani
-- görsel bir değişiklik YARATMAZ, sadece artık admin panelden gerçekten
-- düzenlenebilir hale getirir.

INSERT INTO sistem_ayarlari (setting_key, setting_value, setting_type, category, description, is_public, updated_at) VALUES
('seo_title', 'Prolyn ERP - Yeni Çağın İş Yönetimi', 'string', 'seo', 'Tüm sayfalarda <title> etiketinin temeli olarak kullanılır', 1, NOW()),
('seo_description', 'Kurumsal Kaynak Planlama, CRM, İK ve Ön Muhasebe çözümleri tek bir platformda.', 'string', 'seo', 'Arama motorları için meta açıklama', 1, NOW()),
('seo_keywords', 'erp, bulut erp, crm, ön muhasebe, ik', 'string', 'seo', 'Arama motorları için anahtar kelimeler', 1, NOW()),
('site_name', 'Prolyn ERP', 'string', 'general', 'Footer ve copyright metninde kullanılan site adı', 1, NOW()),
('website_footer_text', 'Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız. Hız, güvenlik ve esnekliği bir arada sunuyoruz.', 'string', 'general', 'Site footer''ındaki tanıtım metni', 1, NOW()),
('website_hero_badge', '✨ Yeni Nesil ERP ile Tanışın', 'string', 'general', 'Anasayfa hero bölümündeki rozet metni', 1, NOW()),
('website_hero_title', 'İşletmenizi Geleceğe Taşıyın', 'string', 'general', 'Anasayfa hero başlığı ("Geleceğe" kelimesi otomatik vurgulanır)', 1, NOW()),
('website_hero_subtitle', 'Karmaşık süreçlere son verin. Bulut tabanlı, ışık hızında ve tamamen izole mimari ile iş akışlarınızı anında dijitalleştirin.', 'string', 'general', 'Anasayfa hero alt açıklaması', 1, NOW())
ON DUPLICATE KEY UPDATE setting_value = setting_value;
