-- kurumlar tablosunda kod tarafından kullanılan ancak şemada var olmayan
-- 6 kolonu ekler. Eksiklikleri nedeniyle şu ekranlar tamamen çalışmıyordu:
--   - crm/tenant_form.php  (kurum ekleme/düzenleme kaydı sessizce başarısız oluyordu)
--   - crm/tenant_detail.php (deneme -> sözleşmeli dönüşümü, sözleşme bilgisi gösterimi)
--   - crm/feature_usage.php ("Müşteri Kullanım Limitleri" sayfası hep boştu)
--   - crm/ajax/calculate_proration.php (paket yükseltme hep "sözleşme yok" hatası veriyordu)
--
-- Üretim veritabanına uygulamadan önce yedek alın.

ALTER TABLE kurumlar
  ADD COLUMN official_name VARCHAR(255) NULL AFTER company_name,
  ADD COLUMN subscription_type VARCHAR(20) NULL DEFAULT 'contract' AFTER status,
  ADD COLUMN city_id INT(11) NULL AFTER billing_address,
  ADD COLUMN district_id INT(11) NULL AFTER city_id,
  ADD COLUMN sozlesme_baslangic_tarihi DATE NULL AFTER trial_end_date,
  ADD COLUMN database_user VARCHAR(64) NULL AFTER database_name;
