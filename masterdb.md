# 📦 E-Mutfak Master Yönetim Paneli - Teknik Dokümantasyon

Bu belge, SaaS tabanlı E-Mutfak platformunun merkezi (master) veritabanı yapısını, işleyişini ve yönetimini anlamak için hazırlanmıştır.

## 🚀 Proje Genel Bakışı
Sistem, birden fazla müşterinin (restoran, catering vb.) aboneliklerini, ödemelerini ve teknik loglarını tek bir merkezden yönetmek üzere tasarlanmıştır.

## 🛠 Veritabanı Grupları

### 1. Çekirdek Yönetim (Core)
* **customers:** Sisteme kayıtlı ana firmalar. Her müşterinin kendi `database_name` bilgisi burada tutulur.
* **admins:** Paneli yöneten süper yöneticiler ve destek ekibi.
* **system_settings:** Uygulamanın genel ayarları (KDV oranları, SMTP bilgileri, deneme süreleri).

### 2. Abonelik ve Finans (Billing)
* **subscriptions:** Müşterilerin aktif paketleri ve bitiş tarihleri.
* **payments:** Kredi kartı veya havale ile yapılan tüm tahsilatlar.
* **package_features:** Paketlerin (Basic, Pro, Enterprise) teknik limitleri (Max kullanıcı, depolama vb.).
* **tax_rates:** Dinamik vergi yönetimi.

### 3. İletişim ve Destek
* **support_tickets & ticket_replies:** Müşterilerden gelen talepler ve admin yanıtları.
* **email_queue:** Sistem tarafından gönderilecek otomatik e-postaların bekleme alanı.
* **announcements:** Tüm veya belirli müşterilere gönderilen sistem duyuruları.

### 4. Güvenlik ve İzleme (Audit)
* **audit_logs:** Veritabanında kim neyi değiştirdi? (Eski ve yeni veri karşılaştırmalı).
* **activity_logs:** Kullanıcıların sistem içindeki tıklamaları ve aksiyonları.
* **login_attempts:** Hatalı giriş denemeleri ve güvenlik takibi.

## 📈 Operasyonel Akış

1.  **Müşteri Kaydı:** `customers` tablosuna kayıt açılır ve bir deneme süresi (`trial_end_date`) tanımlanır.
2.  **Abonelik:** Müşteri paket seçtiğinde `subscriptions` ve `subscription_history` güncellenir.
3.  **Ödeme:** Ödeme başarılı olduğunda `payments` tablosuna kayıt düşer ve otomatik olarak `invoices` (fatura) oluşturulur.
4.  **Hata Takibi:** Sistemde oluşan yazılımsal hatalar `system_errors` tablosunda toplanır.

## ⚠️ Dikkat Edilmesi Gerekenler
- **Soft Delete:** Müşteri silerken veriyi kalıcı silmek yerine `deleted_at` sütununu kullanın.
- **Güvenlik:** `database_password` gibi alanlar uygulama tarafında şifrelenerek saklanmalıdır.
- **Performans:** Log tabloları (`audit_logs`) çok hızlı büyür; yıllık olarak arşivlenmelidir.