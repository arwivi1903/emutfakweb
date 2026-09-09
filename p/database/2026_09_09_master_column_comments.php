<?php
/**
 * Prolyn - prolyn_master şemasındaki eksik COLUMN COMMENT'leri ekler (SADECE CLI)
 * -----------------------------------------------------------------------------
 * Her kolonun tam mevcut tanımını information_schema'dan okur, sadece COMMENT
 * ekleyerek MODIFY COLUMN çalıştırır (tip/null/default/extra değişmez).
 * Idempotent: tekrar çalıştırmak güvenlidir (aynı yorumları yeniden yazar).
 *
 * Çalıştırma: php p/database/2026_09_09_master_column_comments.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../env.php';

$pdo = new PDO(
    'mysql:host=' . prolynweb_env('DB_HOST', '127.0.0.1') . ';dbname=' . prolynweb_env('DB_NAME', 'prolyn_master') . ';charset=utf8mb4',
    prolynweb_env('DB_USER', ''),
    prolynweb_env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// tablo => [tablo_comment_yoksa_bu, [kolon => yorum, ...]]
$data = [
    'abonelikler' => [null, [
        'created_at' => 'Abonelik kaydının oluşturulma zamanı',
        'updated_at' => 'Abonelik kaydının son güncellenme zamanı',
    ]],
    'api_anahtarlari' => [null, [
        'created_at' => 'API anahtarının oluşturulma zamanı',
    ]],
    'bildirimler' => [null, [
        'created_at' => 'Bildirimin oluşturulma zamanı',
    ]],
    'bildirim_kuyrugu' => [null, [
        'created_at' => 'Kuyruk kaydının oluşturulma zamanı',
    ]],
    'blog_yazilari' => ['Kurumsal site blog yazıları (index.php > Blog, blog.php, blog_detail.php)', [
        'id' => 'Birincil anahtar',
        'title' => 'Yazı başlığı',
        'slug' => 'URL dostu benzersiz kimlik (blog_detail.php?slug=...)',
        'excerpt' => 'Liste sayfasında gösterilen kısa özet',
        'content' => 'Yazının tam içeriği (düz metin, satır sonları nl2br ile gösterilir)',
        'cover_image' => 'Kapak görseli URL\'si (opsiyonel)',
        'status' => 'Yayın durumu: draft = taslak (yalnızca admin görür), published = herkese açık',
        'published_at' => 'Yayına alınma zamanı; status=published olunca otomatik atanır',
        'created_by' => 'yoneticiler.admin_id — yazıyı oluşturan yönetici',
        'created_at' => 'Kaydın oluşturulma zamanı',
        'updated_at' => 'Kaydın son güncellenme zamanı',
    ]],
    'destek_cevaplari' => [null, [
        'reply_id' => 'Birincil anahtar',
        'replied_by_tenant_user' => 'kurum_kullanicilari.user_id — yanıtı yazan kiracı (ERP) kullanıcısı',
        'replied_by_tenant_name' => 'Kiracı kullanıcısının yanıt anındaki görünen adı (kullanıcı silinse de yanıt geçmişte kalır)',
    ]],
    'destek_ekleri' => [null, [
        'ek_id' => 'Birincil anahtar',
        'ticket_id' => 'destek_talepleri.ticket_id — ekin ait olduğu talep',
        'reply_id' => 'destek_cevaplari.reply_id — ek bir yanıta aitse dolu; talebin kendisine aitse NULL',
        'dosya_ad' => 'Kullanıcının yüklediği orijinal dosya adı (görüntülemede kullanılır)',
        'dosya_yol' => 'Diskteki fiziksel yol (uploads/destek altında, rastgele üretilmiş dosya adıyla)',
        'mime_tipi' => 'Dosyanın gerçek MIME tipi (finfo ile tespit edilir, uzantıya güvenilmez)',
        'boyut' => 'Dosya boyutu (byte)',
        'yukleyen_tip' => 'Ekleyen taraf: customer = kiracı kullanıcısı, admin = Prolyn personeli',
        'yukleyen_ad' => 'Ekleyen kişinin görünen adı',
        'yukleyen_id' => 'Ekleyen kişinin kullanıcı/admin kimliği (yukleyen_tip\'e göre yoneticiler.admin_id ya da kurum_kullanicilari.user_id)',
        'created_at' => 'Yükleme zamanı',
    ]],
    'destek_talepleri' => [null, [
        'category' => 'Talep kategorisi (örn. Kullanım Desteği, Faturalama)',
        'created_at' => 'Talebin oluşturulma zamanı',
        'updated_at' => 'Talebin son güncellenme zamanı',
        'tenant_db' => 'Talebi açan kiracının veritabanı adı (kurumlar.database_name), tanı/destek için hızlı referans',
        'created_by_user_id' => 'kurum_kullanicilari.user_id — talebi açan kiracı kullanıcısı',
        'created_by_name' => 'Talebi açan kişinin görünen adı (kayıt zamanındaki değer, sonradan değişmez)',
        'created_by_email' => 'Talebi açan kişinin e-posta adresi (bildirim ve iletişim için)',
        'last_reply_at' => 'Müşteriye giden en son yanıtın zamanı (iç notlar bu alanı değiştirmez)',
        'last_reply_by' => 'Son yanıtı kimin yazdığı: admin veya customer',
        'del_status' => 'Yumuşak silme: 1 = aktif, 0 = silinmiş',
    ]],
    'dosyalar' => [null, [
        'created_at' => 'Dosya kaydının oluşturulma zamanı',
    ]],
    'duyurular' => [null, [
        'created_at' => 'Duyurunun oluşturulma zamanı',
        'updated_at' => 'Duyurunun son güncellenme zamanı',
    ]],
    'eposta_kuyrugu' => [null, [
        'created_at' => 'Kuyruk kaydının oluşturulma zamanı',
        'updated_at' => 'Kuyruk kaydının son güncellenme zamanı',
    ]],
    'eposta_sablonlari' => [null, [
        'created_at' => 'Şablonun oluşturulma zamanı',
        'updated_at' => 'Şablonun son güncellenme zamanı',
    ]],
    'faturalar' => [null, [
        'created_at' => 'Faturanın oluşturulma zamanı',
        'updated_at' => 'Faturanın son güncellenme zamanı',
    ]],
    'fatura_adresleri' => [null, [
        'created_at' => 'Adres kaydının oluşturulma zamanı',
        'updated_at' => 'Adres kaydının son güncellenme zamanı',
    ]],
    'kullanici_aktivite_loglari' => [null, [
        'created_at' => 'Aktivite kaydının oluşturulma zamanı',
    ]],
    'kuponlar' => [null, [
        'created_at' => 'Kuponun oluşturulma zamanı',
    ]],
    'kurumlar' => [null, [
        'official_name' => 'Kurumun resmi/ticari unvanı (fatura ve sözleşmelerde kullanılır; company_name pazarlama/panel görünen adıdır)',
        'contact_person' => 'Birincil yetkili/iletişim kişisinin adı',
        'tax_office' => 'Vergi dairesi',
        'tax_number' => 'Vergi numarası veya TC kimlik numarası',
        'billing_address' => 'Fatura adresi',
        'city_id' => 'prolyn_default.iller.iller_id — kurumun ili',
        'district_id' => 'prolyn_default.ilceler.ilce_id — kurumun ilçesi',
        'account_manager_id' => 'yoneticiler.admin_id — kurumdan sorumlu Prolyn temsilcisi',
        'tenant_alias' => 'Kiracının kısa/görünen takma adı (raporlarda ve URL\'lerde kullanılabilir)',
        'musteri_paketi' => 'Müşterinin satın aldığı paketin görünen adı (örn. "Pro"); plan_name çözümlemesi için prolyn_plan_adi_coz() kullanılır',
        'database_name' => 'Kiracıya ait izole MySQL veritabanının adı (prolyn_kN)',
        'database_user' => 'Kiracı veritabanına özel MySQL kullanıcı adı (izolasyon migrasyonu ile atanır; boşsa henüz migrate edilmemiştir)',
        'database_password' => 'Kiracı veritabanı kullanıcısının parolası (düz metin — yalnızca dahili sunucudan erişilen bu tabloda saklanır)',
        'status' => 'Kurumun hesap durumu: active, passive, suspended',
        'subscription_type' => 'Abonelik türü: trial = deneme, contract = sözleşmeli',
        'trial_start_date' => 'Deneme süresinin başlangıç tarihi',
        'trial_end_date' => 'Deneme süresinin bitiş tarihi; bu tarihten sonra subscription_type=trial olan hesaplar erişimini kaybeder',
        'sozlesme_baslangic_tarihi' => 'Sözleşmeli abonelik başlangıç tarihi (yalnızca subscription_type=contract için dolu)',
        'sozlesme_bitis_tarihi' => 'Sözleşmeli abonelik bitiş tarihi',
        'logo_url' => 'Kurum logosunun URL\'si (panelde ve kurum listesinde gösterilir)',
        'contact_name' => 'İkincil/genel iletişim adı (fatura vb. için company_email/company_phone ile birlikte kullanılır)',
        'contact_email' => 'İkincil iletişim e-postası',
        'contact_phone' => 'İkincil iletişim telefonu',
        'created_at' => 'Kurum kaydının oluşturulma zamanı',
        'updated_at' => 'Kurum kaydının son güncellenme zamanı',
    ]],
    'kurum_kullanicilari' => [null, [
        'created_at' => 'Kullanıcı kaydının oluşturulma zamanı',
        'updated_at' => 'Kullanıcı kaydının son güncellenme zamanı',
    ]],
    'kurum_notlari' => [null, [
        'created_at' => 'Notun oluşturulma zamanı',
    ]],
    'mail_kuyrugu' => [null, [
        'queue_id' => 'Birincil anahtar',
        'channel' => 'Gönderim kanalı (örn. email, sms)',
        'recipient' => 'Alıcı adresi/numarası',
        'subject' => 'Mesaj konusu',
        'message' => 'Mesaj gövdesi',
        'source' => 'Mesajı kuyruğa ekleyen özellik/modül (izleme ve hata ayıklama için)',
        'status' => 'Gönderim durumu: pending, queued, sent, failed vb.',
        'attempts' => 'Gönderim deneme sayısı',
        'last_error' => 'Son başarısız denemenin hata mesajı',
        'config_source' => 'Gönderim ayarlarının alındığı kaynak (örn. sistem varsayılanı, kurum özel SMTP)',
        'config_snapshot' => 'Gönderim anındaki SMTP/ayar bilgisinin JSON anlık görüntüsü (ayarlar sonradan değişse de o anki durum korunur)',
        'payload' => 'Şablon değişkenleri ve ek verilerin JSON gövdesi',
        'created_at' => 'Kuyruğa eklenme zamanı',
        'processed_at' => 'İşlenme (gönderim denemesi yapılma) zamanı',
    ]],
    'odemeler' => [null, [
        'payment_date' => 'Ödemenin gerçekleştiği tarih',
        'created_at' => 'Ödeme kaydının oluşturulma zamanı',
        'updated_at' => 'Ödeme kaydının son güncellenme zamanı',
    ]],
    'oturumlar' => [null, [
        'user_type' => 'Oturum sahibinin tipi: admin (Prolyn personeli), customer (kiracı kullanıcısı) veya user',
        'user_email' => 'Oturum sahibinin e-posta adresi',
        'user_full_name' => 'Oturum sahibinin görünen adı',
        'tenant_db' => 'customer tipi oturumlarda kiracının veritabanı adı',
        'firma_adi' => 'customer tipi oturumlarda kurumun görünen adı',
        'last_activity' => 'Oturumdaki son aktivite zamanı (zaman aşımı hesabında kullanılır)',
        'created_at' => 'Oturumun başlatılma zamanı',
    ]],
    'ozellik_kullanimi' => [null, [
        'created_at' => 'Kullanım kaydının oluşturulma zamanı',
        'updated_at' => 'Kullanım kaydının son güncellenme zamanı',
    ]],
    'paket_ozellikleri' => [null, [
        'created_at' => 'Paket tanımının oluşturulma zamanı',
        'allowed_modules' => 'Pakette açık olan modül anahtarlarının JSON dizisi (örn. ["core","customer","invoice"]); kurum kaydında allowed_modules alanına buradan kopyalanır',
    ]],
    'roller' => [null, [
        'created_at' => 'Rolün oluşturulma zamanı',
    ]],
    'satis_icerigi' => [null, [
        'created_at' => 'İçeriğin oluşturulma zamanı',
        'updated_at' => 'İçeriğin son güncellenme zamanı',
    ]],
    'sifre_sifirlama' => [null, [
        'expires_at' => 'Sıfırlama tokenının geçerlilik süresinin dolduğu zaman',
        'created_at' => 'Tokenın oluşturulma zamanı',
    ]],
    'sifre_sifirlama_talepleri' => [null, [
        'request_id' => 'Birincil anahtar',
        'kurum_kodu' => 'Talep formunda girilen kurum kodu (customer_code)',
        'request_email' => 'Talep formunda girilen e-posta adresi',
        'customer_id' => 'kurumlar.customer_id — eşleşen kurum (eşleşme bulunduysa dolu)',
        'tenant_db' => 'Eşleşen kurumun veritabanı adı',
        'user_id' => 'kurum_kullanicilari.user_id — eşleşen kiracı kullanıcısı',
        'request_status' => 'Talebin durumu: pending, in_review, matched, completed, rejected vb.',
        'mail_queue_status' => 'Bu talep için gönderilecek e-postanın mail_kuyrugu durumu',
        'request_ip' => 'Talebin yapıldığı IP adresi',
        'user_agent' => 'Talebin yapıldığı tarayıcı/istemci bilgisi',
        'created_at' => 'Talebin oluşturulma zamanı',
    ]],
    'sistem_ayarlari' => [null, [
        'updated_at' => 'Ayarın son güncellenme zamanı',
    ]],
    'sistem_hatalari' => [null, [
        'created_at' => 'Hatanın kaydedilme zamanı',
    ]],
    'sistem_olaylari' => [null, [
        'created_at' => 'Olayın kaydedilme zamanı',
    ]],
    'vergi_oranlari' => [null, [
        'created_at' => 'Vergi oranı kaydının oluşturulma zamanı',
    ]],
    'videolar' => ['Kurumsal site video vitrin — YouTube videoları, oEmbed ile otomatik çekilen meta bilgilerle (video.php)', [
        'id' => 'Birincil anahtar',
        'youtube_video_id' => 'YouTube video kimliği (11 karakter); video URL\'sinden otomatik çıkarılır',
        'title' => 'Video başlığı; eklenirken YouTube oEmbed servisinden otomatik çekilir, sonradan düzenlenebilir',
        'thumbnail_url' => 'YouTube kapak görseli URL\'si; oEmbed servisinden otomatik çekilir',
        'author_name' => 'YouTube kanal adı; oEmbed servisinden otomatik çekilir',
        'order_no' => 'Video sayfasındaki gösterim sırası (küçük sayı önce gösterilir)',
        'status' => 'Yayın durumu: draft = taslak (yalnızca admin görür), published = herkese açık',
        'created_by' => 'yoneticiler.admin_id — videoyu ekleyen yönetici',
        'created_at' => 'Kaydın oluşturulma zamanı',
    ]],
    'webhooklar' => [null, [
        'created_at' => 'Webhook tanımının oluşturulma zamanı',
        'updated_at' => 'Webhook tanımının son güncellenme zamanı',
    ]],
    'webhook_loglari' => [null, [
        'created_at' => 'Log kaydının oluşturulma zamanı',
    ]],
    'yoneticiler' => [null, [
        'created_at' => 'Yönetici hesabının oluşturulma zamanı',
        'updated_at' => 'Yönetici hesabının son güncellenme zamanı',
    ]],
    'yonetici_loglari' => [null, [
        'created_at' => 'Log kaydının oluşturulma zamanı',
    ]],
];

$fixed = 0;
$errors = [];

foreach ($data as $table => [$tableComment, $columns]) {
    foreach ($columns as $col => $comment) {
        $stmt = $pdo->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLLATION_NAME
                                FROM information_schema.COLUMNS
                                WHERE TABLE_SCHEMA = 'prolyn_master' AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $col]);
        $meta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$meta) {
            $errors[] = "$table.$col: kolon bulunamadı (view olabilir, atlandı)";
            continue;
        }

        $def = "`$col` " . $meta['COLUMN_TYPE'];
        if ($meta['COLLATION_NAME']) {
            $def .= " COLLATE " . $meta['COLLATION_NAME'];
        }
        $def .= $meta['IS_NULLABLE'] === 'NO' ? ' NOT NULL' : ' NULL';

        $default = $meta['COLUMN_DEFAULT'];
        if ($default !== null && strtoupper(trim($default)) !== 'NULL') {
            if (in_array(strtoupper($default), ['CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP()'], true)) {
                $def .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif (is_numeric($default)) {
                $def .= " DEFAULT $default";
            } elseif (strlen($default) >= 2 && $default[0] === "'" && substr($default, -1) === "'") {
                // information_schema zaten tırnaklı döndürmüş (ör. 'draft') — tekrar tırnaklama
                $def .= " DEFAULT $default";
            } else {
                $def .= ' DEFAULT ' . $pdo->quote($default);
            }
        } elseif ($meta['IS_NULLABLE'] === 'YES') {
            $def .= ' DEFAULT NULL';
        }

        if (stripos($meta['EXTRA'], 'on update current_timestamp') !== false) {
            $def .= ' ON UPDATE CURRENT_TIMESTAMP';
        }
        if (stripos($meta['EXTRA'], 'auto_increment') !== false) {
            $def .= ' AUTO_INCREMENT';
        }

        $def .= ' COMMENT ' . $pdo->quote($comment);

        $sql = "ALTER TABLE `$table` MODIFY COLUMN $def";
        try {
            $pdo->exec($sql);
            $fixed++;
        } catch (Throwable $e) {
            $errors[] = "$table.$col: " . $e->getMessage() . " | SQL: $sql";
        }
    }

    if ($tableComment !== null) {
        try {
            $pdo->exec("ALTER TABLE `$table` COMMENT = " . $pdo->quote($tableComment));
        } catch (Throwable $e) {
            $errors[] = "$table (table comment): " . $e->getMessage();
        }
    }
}

echo "Düzeltilen kolon sayısı: $fixed\n";
if ($errors) {
    echo "Hatalar:\n" . implode("\n", $errors) . "\n";
}
