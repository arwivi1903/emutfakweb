# Admin Users Yönetim Sistemi

## 📋 Genel Bilgiler

`emutfak_master` veritabanının `admin_users` tablosu için tam CRUD (Create, Read, Update, Delete) operasyonlarını içeren bir yönetim sistemi oluşturulmuştur.

## 📁 Oluşturulan Dosyalar

### 1. **usersapi.php** - API Handler
**Konum:** `/Applications/XAMPP/xamppfiles/htdocs/emutfakweb/p/dist/api/usersapi.php`

Tüm admin_users CRUD operasyonları için API endpoint'lerini içerir:
- ✅ **LIST** - Admin kullanıcılarını listele (arama, filtreleme, sayfalama ile)
- ✅ **DETAIL** - Tek admin kullanıcı bilgisini getir
- ✅ **CREATE** - Yeni admin kullanıcı ekle
- ✅ **UPDATE** - Admin kullanıcı bilgisini güncelle
- ✅ **DELETE** - Admin kullanıcı sil

**Özellikler:**
- Session kontrol ve güvenlik
- Parametre validasyonu
- E-posta benzersizlik kontrolü
- Şifre hash'leme (PASSWORD_BCRYPT)
- JSON response
- Sayfalama ve arama özellikleri
- Hata yönetimi

### 2. **userslist.php** - Listeleme Sayfası
**Konum:** `/Applications/XAMPP/xamppfiles/htdocs/emutfakweb/p/dist/toolbars/userslist.php`

Admin kullanıcılarının listelendiği sayfa:

**Özellikler:**
- 📊 Tablo halinde tüm kullanıcıları göster
- 🔍 Kullanıcı adı, e-mail ve ad soyad ile arama
- 📑 Sayfalama (10 kayıt/sayfa)
- 🗂️ Sıralama seçenekleri
- ✏️ Düzenleme butonu (usersedit.php'ye yönlendir)
- 🗑️ Silme butonu (inline delete)
- 📈 Kullanıcı durumu badge'i (Aktif/Pasif)
- 🏷️ Yönetici seviyesi badge'i
- ➕ Yeni Admin Ekle butonu

**Tablo Sütunları:**
- ID
- Kullanıcı Adı
- E-mail
- Ad Soyad
- Telefon
- Seviye (Super Admin/Admin/Operatör)
- Durum (Aktif/Pasif)
- Oluş. Tarihi
- İşlemler

### 3. **usersadd.php** - Yeni Kullanıcı Ekleme
**Konum:** `/Applications/XAMPP/xamppfiles/htdocs/emutfakweb/p/dist/toolbars/usersadd.php`

Yeni admin kullanıcı ekleme formu:

**Form Alanları:**
- Kullanıcı Adı * (zorunlu, benzersiz)
- E-mail Adresi * (zorunlu, benzersiz, geçerli format)
- Şifre * (zorunlu, min 8 karakter)
- Şifre Tekrar * (doğrulama)
- Ad Soyad (opsiyonel)
- Telefon (opsiyonel)
- Yönetici Seviyesi * (Super Admin/Admin/Operatör)
- Durum * (Aktif/Pasif)

**Validasyonlar:**
- ✓ Tüm zorunlu alanlar boş mu kontrolü
- ✓ E-posta format doğrulaması
- ✓ Şifre eşleşme kontrolü
- ✓ Şifre minimum karakter kontrolü
- ✓ Duplicated kullanıcı adı/e-mail kontrolü (API tarafı)

### 4. **usersedit.php** - Kullanıcı Düzenleme
**Konum:** `/Applications/XAMPP/xamppfiles/htdocs/emutfakweb/p/dist/toolbars/usersedit.php`

Mevcut admin kullanıcı düzenleme sayfası:

**Düzenlenebilir Alanlar:**
- E-mail Adresi
- Ad Soyad
- Telefon
- Yönetici Seviyesi
- Durum
- Şifre (isteğe bağlı)

**Özellikler:**
- 🔒 Kullanıcı adı değiştirilemez (salt okunur)
- 📅 Oluş. Tarihi ve Son Güncellenme tarihleri görünür
- 🔄 Otomatik veri yükleme
- ✓ Şifre değişimi isteğe bağlı
- ⚠️ Hata yönetimi ve yükleme gösterimi

## 🔌 API Endpoint'leri

### List - Kullanıcıları Listele
```
GET /dist/api/usersapi.php?action=list&search=&sort=admin_id&order=DESC&page=1&limit=10
```

**Parametreler:**
- `search` (string) - Arama metni
- `sort` (string) - Sıralama alanı (admin_id, admin_username, admin_email, admin_level, admin_status)
- `order` (string) - ASC veya DESC
- `page` (int) - Sayfa numarası
- `limit` (int) - Kayıt sayısı

**Response:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 10,
    "page": 1,
    "limit": 10,
    "totalPages": 1
  }
}
```

### Detail - Tek Kullanıcı Getir
```
GET /dist/api/usersapi.php?action=detail&id=1
```

### Create - Yeni Kullanıcı Ekle
```
POST /dist/api/usersapi.php?action=create
Content-Type: application/json

{
  "admin_username": "username",
  "admin_email": "email@example.com",
  "admin_password": "password123",
  "admin_name": "Ad Soyad",
  "admin_phone": "0123456789",
  "admin_level": 2,
  "admin_status": 1
}
```

### Update - Kullanıcı Güncelle
```
POST /dist/api/usersapi.php?action=update
Content-Type: application/json

{
  "admin_id": 1,
  "admin_email": "newemail@example.com",
  "admin_name": "Yeni Ad",
  "admin_phone": "0123456789",
  "admin_level": 2,
  "admin_status": 1,
  "admin_password": "newpassword123" // optional
}
```

### Delete - Kullanıcı Sil
```
POST /dist/api/usersapi.php?action=delete
Content-Type: application/json

{
  "admin_id": 1
}
```

## 🔐 Güvenlik Özellikleri

✅ Session kontrol - Giriş yapılmış olmalı
✅ CSRF koruması - JSON header ile
✅ Şifre hash'leme - PASSWORD_BCRYPT
✅ SQL Injection koruması - PDO prepared statements
✅ E-posta benzersizlik kontrolü
✅ Kullanıcı adı benzersizlik kontrolü
✅ Kendini silemesin kontrolü
✅ Input sanitization

## 📊 Veritabanı Tablosu

**Tablo Adı:** `admin_users`

**Sütunlar:**
- `admin_id` (int, PRIMARY KEY, AUTO_INCREMENT)
- `admin_username` (varchar, UNIQUE)
- `admin_email` (varchar, UNIQUE)
- `admin_password` (varchar, hashed)
- `admin_name` (varchar)
- `admin_phone` (varchar)
- `admin_level` (int) - 1:Super Admin, 2:Admin, 3:Operatör
- `admin_status` (int) - 1:Aktif, 0:Pasif
- `admin_create_date` (datetime)
- `admin_update_date` (datetime)

## 🎨 UI Özellikleri

- ✨ Bootstrap 5 temelli responsive tasarım
- 🔍 Gerçek zamanlı arama
- 📱 Mobil uyumlu tablo
- 🎯 Belirtken filtreleme
- ⚡ AJAX ile sayfa yenileme olmaksızın işlem
- 📍 Sayfalama navigasyonu
- 📊 Durum ve seviye badge'leri
- ⏳ Loading gösterimi
- ⚠️ Silme onayı dialog'u
- 📧 Form validasyonu mesajları

## 🚀 Kullanım

### 1. Listeleme Sayfasına Erişim
Tarayıcıda: `/emutfakweb/p/dist/toolbars/userslist.php`

### 2. Yeni Kullanıcı Ekleme
- "Yeni Admin Ekle" butonuna tıkla
- Formu doldur
- "Ekle" butonuna tıkla
- Başarılı ise otomatik listeye yönlendir

### 3. Kullanıcı Düzenleme
- Listedeki edit butonuna tıkla
- Formu güncelle
- "Güncelle" butonuna tıkla
- Başarılı ise otomatik listeye yönlendir

### 4. Kullanıcı Silme
- Listedeki sil butonuna tıkla
- Onay mesajında "Evet" seç
- Başarılı ise sayfa otomatik yenilenir

## 🔧 Teknik Detaylar

- **Framework:** Vanilla PHP + MySQL
- **Database Class:** PDO ile PDO\Prepare statements
- **Authentication:** PHP Session
- **Hashing:** PASSWORD_BCRYPT
- **Frontend:** Bootstrap 5 + Vanilla JS
- **API:** REST JSON

## ✅ Test Edilmiş Özellikler

✓ Kullanıcı listesi yükleme
✓ Arama ve filtreleme
✓ Sayfalama
✓ Yeni kullanıcı ekleme (tüm validasyonlar)
✓ Kullanıcı güncelleme
✓ Şifre değişimi
✓ Kullanıcı silme
✓ Form validasyonu
✓ E-posta benzersizlik
✓ Session kontrol
✓ Error handling

## 📝 Notlar

- Tüm tarih formatları Türkçe (tr-TR) kullanır
- Şifre minimum 8 karakter olmalıdır
- Kendini silemesin kontrolü vardır
- E-posta ve kullanıcı adı benzersiz olmalıdır
- Şifre değişimi isteğe bağlıdır
- API'ye yanlış istek göndermek 401/400/404/500 hata codes döndürür
