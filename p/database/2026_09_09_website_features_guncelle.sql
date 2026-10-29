-- website_features tablosu admin panelden ("Ana Sayfa Özellikleri")
-- yönetilebiliyordu ama index.php hiçbir zaman bu tablodan okumuyordu;
-- anasayfadaki "Neden Prolyn?" bölümü tamamen sabit (hardcoded) HTML idi.
-- Sonuç: admin bu ekranda ne değiştirirse değiştirsin canlı sitede hiçbir
-- etkisi olmuyordu. İçerik, index.php'de fiilen gösterilen 6 kartla
-- birebir eşleşecek şekilde yenilendi; index.php artık bu tablodan okuyor
-- (bkz. index.php Features bölümü).

DELETE FROM website_features;

INSERT INTO website_features (title, description, icon, status, order_no, created_at) VALUES
('Işık Hızında Performans', 'Optimize edilmiş veritabanı mimarisiyle binlerce kaydı hızla işleyin; yoğun kullanımda bile akıcı bir deneyim yaşayın.', 'bi bi-lightning-charge', 1, 1, NOW()),
('Tam İzolasyon (%100)', 'Her müşteriye (tenant) özel ayrı veritabanı (Multi-DB) mimarisi ile verileriniz diğer firmalardan tamamen izole ve güvende.', 'bi bi-shield-lock', 1, 2, NOW()),
('Raporlama ve Denetim', 'Randevu, sözleşme ve tahsilat raporlarını görün; log kayıtlarıyla kim, ne zaman, ne yaptı takip edin.', 'bi bi-graph-up-arrow', 1, 3, NOW()),
('Kolay Başlangıç', 'Mevcut müşteri ve cari kayıtlarınızı kurulum ekibimizle birlikte aktarın; banka, fatura ve cari süreçlerinizi tek panelde yönetin.', 'bi bi-plug', 1, 4, NOW()),
('Mobil Uyumluluk', 'Tüm cihazlardan kusursuz erişim. İşinizi ofise bağlı kalmadan cep telefonunuzdan veya tabletinizden yönetin.', 'bi bi-phone', 1, 5, NOW()),
('Güvenli Veri Saklama', 'Verileriniz kurumunuza özel veritabanında tutulur ve tüm bağlantılar HTTPS ile şifrelenir.', 'bi bi-cloud-arrow-up', 1, 6, NOW());
