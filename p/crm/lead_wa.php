<?php
/**
 * Lead için WhatsApp tıkla-yaz: mesajı hazırlar, iletişimi kaydeder, wa.me'ye yönlendirir.
 * Mesaj kullanıcının kendi WhatsApp'ında açılır; gönderimi kullanıcı yapar.
 */
require_once __DIR__ . '/../functions/auth.php';
start_secure_session();
require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';
$db = new Database();
oturumkontrol();
Auth::requireModule('manage_tenants');
require_once __DIR__ . '/../functions/lead.php';
lead_sema_kur($db);

$id = (int)($_GET['id'] ?? 0);
$kod = (string)($_GET['s'] ?? '');
if (!validateCSRFToken((string)($_GET['k'] ?? ''))) {
    prolyn_hata_sayfasi(419, 'Bağlantının süresi doldu', 'Listeyi yenileyip WhatsApp butonuna tekrar tıklayın.');
}
$lead = $db->getRowAssoc("SELECT * FROM potansiyel_musteriler WHERE lead_id = ?", [$id]);
$sablon = $db->getRowAssoc("SELECT * FROM lead_sablonlari WHERE kod = ? AND kanal = 'whatsapp'", [$kod]);
if (!$lead || !$sablon) {
    prolyn_hata_sayfasi(404, 'Kayıt bulunamadı', 'Lead veya şablon bulunamadı.');
}
[$ok, $neden] = lead_gonderilebilir_mi($lead, 'whatsapp');
if (!$ok) {
    prolyn_hata_sayfasi(409, 'Bu firmaya mesaj gönderilmiyor', 'Neden: ' . $neden . '.', 'Lead detayından durumu kontrol edin.');
}

$mesaj = lead_sablon_doldur((string)$sablon['govde'], $lead, 'whatsapp', lead_gonderen_adi());
$db->Update("UPDATE potansiyel_musteriler SET wa_sayisi = wa_sayisi + 1, son_iletisim = NOW(),
             durum = IF(durum = 'yeni', 'iletisime_gecildi', durum),
             sonraki_takip = IF(sonraki_takip IS NULL OR sonraki_takip < CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), sonraki_takip)
             WHERE lead_id = ?", [$id]);
lead_aktivite($db, $id, 'whatsapp', 'WhatsApp açıldı: ' . $sablon['ad']);

header('Location: https://wa.me/' . lead_wa_numara((string)$lead['telefon']) . '?text=' . rawurlencode($mesaj), true, 302);
exit;
