-- moduller.php (kurumsal site) sayfası website_modules tablosundan
-- besleniyor ama içerik gerçek ürünün (prolyn/ - tenant ERP uygulaması,
-- sidebar.php) modül yapısıyla örtüşmüyordu; örneğin ön muhasebe
-- modülüne eklenen personel gider/maaş/prim takibi hiç yer almıyordu.
-- İçerik, sidebar.php'deki gerçek Auth::hasAccess() modül anahtarlarına
-- (core, customer, commercial, appointment, current_account, inventory,
-- accounting, invoice, contract, reports, planning, bank, statistics)
-- birebir karşılık gelecek şekilde yeniden yazıldı.

DELETE FROM website_modules;

INSERT INTO website_modules (title, description, icon, status, order_no, created_at, updated_at) VALUES
('Sistem ve Kullanıcı Yönetimi', 'Rol bazlı yetkilendirme, çoklu şube ve kullanıcı yönetimi; tüm işlemler log kaydıyla denetlenir.', 'bi bi-shield-check', 1, 1, NOW(), NOW()),
('Müşteri Yönetimi', 'Müşteri kayıtlarınızı tek merkezden ekleyin, listeleyin ve yönetin.', 'bi bi-people', 1, 2, NOW(), NOW()),
('Ticari Müşteri Takibi', 'Ticari müşterilerinizin cari alacak, borç ve durum bilgilerini ayrı takip edin.', 'bi bi-briefcase', 1, 3, NOW(), NOW()),
('Randevu ve Saha Yönetimi', 'Randevu, ziyaret ve sevkiyat süreçlerini planlayın; ekibinizin saha operasyonunu tek ekrandan yönetin.', 'bi bi-calendar-check', 1, 4, NOW(), NOW()),
('Cari Hesap Yönetimi', 'Cari kartlar, dekontlar ve taksit takibiyle tüm alacak-borç durumunuzu anlık görün.', 'bi bi-journal-text', 1, 5, NOW(), NOW()),
('Ürün Yönetimi', 'Ürün kartlarınızı oluşturun; randevu ve sözleşmelerde kullanın. Stok takibi çok yakında.', 'bi bi-box-seam', 1, 6, NOW(), NOW()),
('Ön Muhasebe ve Personel Giderleri', 'Kasa ve virman hareketlerinin yanı sıra personel maaş, prim ve gider ödemelerini de tek modülden takip edin.', 'bi bi-bank', 1, 7, NOW(), NOW()),
('Fatura ve Tahsilat', 'Alış, satış ve iade faturalarınızı saniyeler içinde oluşturun, tahsilat sürecini takip edin.', 'bi bi-receipt', 1, 8, NOW(), NOW()),
('Sözleşme Yönetimi', 'Hazır şablonlarla dijital sözleşmeler oluşturun, müşteri bazlı arşivleyin.', 'bi bi-file-earmark-text', 1, 9, NOW(), NOW()),
('Raporlar ve Analiz', 'Satış personeli, ürün, şube ve tarih bazlı detaylı raporlarla işletmenizi analiz edin.', 'bi bi-bar-chart', 1, 10, NOW(), NOW()),
('Planlama ve İmalat', 'Üretim ve imalat süreçlerinizi planlayın, iş akışlarınızı organize edin.', 'bi bi-diagram-3', 1, 11, NOW(), NOW()),
('Banka ve Çek İşlemleri', 'Banka hesap hareketlerinizi ve çek işlemlerinizi entegre şekilde takip edin.', 'bi bi-credit-card', 1, 12, NOW(), NOW()),
('İstatistik ve Performans Analizi', 'Toplam satış tutarı, satış adedi ve yıl-ay bazlı grafiklerle performansınızı izleyin.', 'bi bi-graph-up', 1, 13, NOW(), NOW());
