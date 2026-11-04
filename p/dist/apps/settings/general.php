<?php
require_once __DIR__ . '/../../../../env.php';
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
// Yetki sunucu tarafında: SMTP şifresi / WhatsApp anahtarı içeren sayfa, yalnızca ayar yetkilisine açık
Auth::requireModule('manage_settings');
require_once __DIR__ . '/../../../functions/mailer.php';
require_once __DIR__ . '/../../../functions/whatsapp.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();

// Tablo yeni kurulumda (üretim) yoksa oluştur; ayarlar kaydedilemiyordu.
try {
    $db->Insert("CREATE TABLE IF NOT EXISTS system_settings (
        setting_id INT(11) NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT DEFAULT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (setting_id),
        UNIQUE KEY uq_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {
    error_log('[ayarlar] system_settings oluşturulamadı: ' . $e->getMessage());
}

// Varsayılanlar (şifre/anahtar HARİÇ; onları panelden gir). Var olan satırlara dokunulmaz.
$requiredMailSettings = [
    'smtp_host' => (string)prolynweb_env('MAIL_HOST', ''),
    'smtp_port' => (string)prolynweb_env('MAIL_PORT', '587'),
    'smtp_secure' => (string)prolynweb_env('MAIL_SECURE', 'tls'),
    'smtp_user' => (string)prolynweb_env('MAIL_USER', ''),
    'smtp_pass' => '',
    'from_name' => (string)prolynweb_env('MAIL_FROM_NAME', 'Prolyn'),
    'from_email' => 'info@prolyn.net',        // bilgi: iletişim yanıtları, bilgilendirme
    'support_email' => 'destek@prolyn.net',   // destek: lead / kampanya e-postaları
    'noreply_email' => 'noreply@prolyn.net',  // geri dönüşsüz sistem bildirimleri
    'notify_email' => 'info@prolyn.net',      // panele düşen bildirimlerin (iletişim formu vb.) gideceği kutu
    'mail_pass_destek' => '',                 // kimlik başına kutu şifresi (isteğe bağlı)
    'mail_pass_bilgi' => '',
    'mail_pass_noreply' => '',
    'mail_sent_copy' => '1',                  // gönderilen her iletinin kopyası "Gönderilenler" klasörüne
    'imap_host' => '',                        // boşsa SMTP host kullanılır
    'imap_port' => '993',
    'imap_sent_folder' => '',                 // boşsa sunucudan otomatik bulunur
    'whatsapp_enabled' => '0',
    'whatsapp_notify_number' => '',
    'whatsapp_api_url' => 'https://graph.facebook.com/v23.0/{phone_id}/messages',
    'whatsapp_phone_id' => '',
    'whatsapp_token' => '',
];
// Gizli alanlar: formda asla geri basılmaz, boş gönderilirse mevcut değer korunur
$gizliAlanlar = ['smtp_pass', 'whatsapp_token', 'mail_pass_destek', 'mail_pass_bilgi', 'mail_pass_noreply'];
$epostaAlanlari = ['from_email', 'support_email', 'noreply_email', 'notify_email'];

foreach ($requiredMailSettings as $settingKey => $defaultValue) {
    try {
        $exists = (int)$db->getColumn("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?", [$settingKey]);
        if ($exists === 0) {
            $db->Insert("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)", [$settingKey, $defaultValue]);
        }
    } catch (Exception $e) {
        // Sessiz gec
    }
}
prolyn_mail_ayarlari(true);   // bir kerelik göç (destek / info / noreply düzeni) burada da çalışır

$kayit_ok = false; $kayit_hata = '';
$test = null;   // [tür, mesaj]
$action = (string)($_POST['action'] ?? '');

/* ESKİ HATA: yalnızca UPDATE yapılıyordu; satırı olmayan anahtarlar sessizce atlanıyordu.
   Artık izinli anahtarlar UPSERT ediliyor. site_name sistem_ayarlari ile de eşitlenir. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_settings') {
    $izinli = array_merge(array_keys($requiredMailSettings), ['site_name', 'currency', 'trial_period_days']);
    $degerler = [];
    foreach ($izinli as $key) {
        if (!array_key_exists($key, $_POST)) continue;
        $value = trim((string)$_POST[$key]);
        if (in_array($key, $gizliAlanlar, true) && $value === '') continue;   // boş = değiştirme
        if (in_array($key, $epostaAlanlari, true) && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $kayit_hata = 'Geçersiz e-posta adresi: ' . $value; break;
        }
        if ($key === 'whatsapp_notify_number') { $value = preg_replace('/\D/', '', $value); }
        if ($key === 'imap_port' && $value !== '' && !in_array((int)$value, [993, 143], true)) {
            $kayit_hata = 'IMAP port 993 (SSL) veya 143 (STARTTLS) olmalı.'; break;
        }
        if ($key === 'imap_sent_folder' && strpbrk($value, "\"\\\r\n") !== false) {
            $kayit_hata = 'Klasör adında tırnak, ters bölü veya satır sonu olamaz.'; break;
        }
        if ($key === 'whatsapp_phone_id' && $value !== '' && !ctype_digit($value)) {
            $kayit_hata = 'Phone number ID yalnızca rakamlardan oluşur.'; break;
        }
        // Anahtar başka bir sunucuya gönderilmesin: yalnızca Meta Graph API adresi kabul edilir
        if ($key === 'whatsapp_api_url' && $value !== '' && !preg_match('~^https://graph\.facebook\.com/v\d+\.\d+/(\{phone_id\}|\d+)/messages$~', $value)) {
            $kayit_hata = 'WhatsApp API URL biçimi: https://graph.facebook.com/v23.0/{phone_id}/messages'; break;
        }
        $degerler[$key] = $value;
    }
    if ($kayit_hata === '') {
        try {
            foreach ($degerler as $key => $value) {
                $db->Update("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)
                             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)", [$key, $value]);
            }
            if (isset($degerler['site_name']) && $degerler['site_name'] !== '') {
                $db->Update("INSERT INTO sistem_ayarlari (setting_key, setting_value, setting_type, category, description, is_public, updated_at)
                             VALUES ('site_name', ?, 'string', 'general', 'Footer ve copyright metninde kullanılan site adı', 1, NOW())
                             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                    [$degerler['site_name']]);
            }
            // Denetim kaydı: hangi anahtarlar değişti (gizli değerler loga yazılmaz)
            if (function_exists('xlog')) { xlog('Sistem ayarları güncellendi', 'Anahtarlar: ' . implode(', ', array_keys($degerler))); }
            $kayit_ok = true;
        } catch (Throwable $e) {
            error_log('[ayarlar] kayıt hatası: ' . $e->getMessage());
            $kayit_hata = 'Ayarlar kaydedilemedi. Ayrıntı hata kaydında.';
        }
    }
}

// Anlık test gönderimleri (kuyruğa girmez; ayarların doğru olduğunu hemen gösterir)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mail_test') {
    $ay = prolyn_mail_ayarlari(true);
    $kimlik = in_array($_POST['kimlik'] ?? '', ['destek', 'bilgi', 'noreply'], true) ? $_POST['kimlik'] : 'noreply';
    $alici = trim((string)($_POST['test_alici'] ?? '')) ?: $ay['notify_email'];
    if (!filter_var($alici, FILTER_VALIDATE_EMAIL)) {
        $test = ['danger', 'Test alıcısı geçersiz.'];
    } else {
        $ok = prolyn_send_mail($alici, 'Prolyn test e-postası (' . $ay['kimlikler'][$kimlik] . ')',
            "Bu bir test e-postasıdır.\nGönderen kimliği: {$kimlik} <{$ay['kimlikler'][$kimlik]}>\nTarih: " . date('d.m.Y H:i'), false, [], $kimlik);
        $kopyaMsg = !$ay['kopya']['enabled'] ? '' : (prolyn_mail_kopya_hata() === '' ? ' Kopyası "Gönderilenler" klasörüne eklendi.' : ' Ancak "Gönderilenler" kopyası alınamadı: ' . prolyn_mail_kopya_hata());
        $test = $ok ? [prolyn_mail_kopya_hata() === '' ? 'success' : 'warning', 'Test e-postası ' . $ay['kimlikler'][$kimlik] . ' adresinden ' . $alici . ' adresine gönderildi.' . $kopyaMsg . ' Gelen kutusunu ve spam klasörünü kontrol edin.']
                    : ['danger', 'Gönderilemedi: ' . (prolyn_mail_son_hata() ?: 'bilinmeyen hata')];
        if (function_exists('xlog')) { xlog('Test e-postası', $kimlik . ' → ' . $alici . ($ok ? ' (başarılı)' : ' (başarısız)')); }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'wa_test') {
    $wa = prolyn_mail_ayarlari(true)['whatsapp'];
    if ($wa['notify'] === '') {
        $test = ['warning', 'Önce "WhatsApp Bildirim No" alanını doldurup kaydedin.'];
    } else {
        // hello_world: Meta'nın her hesaba onaylı verdiği örnek şablon (24 saat kuralına takılmaz)
        [$ok, $hata] = prolyn_whatsapp_gonder($wa['notify'], '', ['ad' => 'hello_world', 'dil' => 'en_US']);
        $test = $ok ? ['success', 'WhatsApp test mesajı ' . $wa['notify'] . ' numarasına gönderildi.'] : ['danger', 'WhatsApp gönderilemedi: ' . $hata];
        if (function_exists('xlog')) { xlog('WhatsApp test', $wa['notify'] . ($ok ? ' (başarılı)' : ' (başarısız)')); }
    }
}

$settings = $db->allAssoc("SELECT * FROM system_settings");
$config = [];
foreach ($settings as $s) {
    if (isset($s['setting_key'])) {
        $config[$s['setting_key']] = $s['setting_value'];
    }
}
if (empty($config['site_name'])) {
    try {
        $config['site_name'] = (string)$db->getColumn("SELECT setting_value FROM sistem_ayarlari WHERE setting_key = 'site_name'");
    } catch (Throwable $e) { $config['site_name'] = ''; }
}
if (!isset($config['currency']) || $config['currency'] === '') $config['currency'] = 'TRY';
$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$v = static fn(string $k, string $d = '') => $h($config[$k] ?? $d);
$doluMu = static fn(string $k) => trim((string)($config[$k] ?? '')) !== '';

/** Tek satırlık form alanı (AdminLTE/Metronic düzeni). Etiket/yardım metinleri sabittir, kullanıcı girdisi içermez. */
$alan = static function (string $etiket, string $girdi, string $yardim = ''): string {
    return '<div class="row mb-6"><label class="col-lg-4 col-form-label fw-semibold fs-6">' . $etiket . '</label>'
         . '<div class="col-lg-8 fv-row">' . $girdi . ($yardim !== '' ? '<div class="form-text">' . $yardim . '</div>' : '') . '</div></div>';
};
$inp = 'form-control form-control-lg form-control-solid';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <?php if ($kayit_ok): ?><div class="alert alert-success mb-6">Ayarlar kaydedildi.</div><?php endif; ?>
                <?php if ($kayit_hata !== ''): ?><div class="alert alert-danger mb-6"><?= $h($kayit_hata) ?></div><?php endif; ?>
                <?php if ($test): ?><div class="alert alert-<?= $h($test[0]) ?> mb-6"><?= $h($test[1]) ?></div><?php endif; ?>

                <form id="kt_account_profile_details_form" class="form" method="POST" autocomplete="off">
                    <?= csrfTokenField() ?>
                    <input type="hidden" name="action" value="save_settings">

                    <!-- GENEL -->
                    <div class="card mb-6">
                        <div class="card-header border-0"><div class="card-title m-0"><h3 class="fw-bold m-0">Sistem Ayarları</h3></div></div>
                        <div class="card-body border-top p-9">
                            <?= $alan('Site Adı', '<input type="text" name="site_name" class="' . $inp . '" value="' . $v('site_name') . '" required>') ?>
                            <?= $alan('Para Birimi', '<input type="text" name="currency" class="' . $inp . '" value="' . $v('currency', 'TRY') . '" required>') ?>
                            <?= $alan('Deneme Süresi (Gün)', '<input type="number" min="0" name="trial_period_days" class="' . $inp . '" value="' . $v('trial_period_days', '30') . '">') ?>
                        </div>
                    </div>

                    <!-- SMTP -->
                    <div class="card mb-6">
                        <div class="card-header border-0"><div class="card-title m-0"><h3 class="fw-bold m-0">SMTP Sunucusu</h3></div></div>
                        <div class="card-body border-top p-9">
                            <?= $alan('SMTP Host', '<input type="text" name="smtp_host" class="' . $inp . '" value="' . $v('smtp_host') . '" placeholder="mail.prolyn.net">') ?>
                            <?= $alan('SMTP Port', '<input type="number" name="smtp_port" class="' . $inp . '" value="' . $v('smtp_port', '587') . '">', 'TLS için genelde 587, SSL için 465.') ?>
                            <?php $sec = $config['smtp_secure'] ?? 'tls'; ?>
                            <?= $alan('SMTP Güvenlik', '<select name="smtp_secure" class="form-select form-select-solid form-select-lg">'
                                . '<option value="tls"' . ($sec === 'tls' ? ' selected' : '') . '>TLS</option>'
                                . '<option value="ssl"' . ($sec === 'ssl' ? ' selected' : '') . '>SSL</option>'
                                . '<option value="none"' . ($sec === 'none' ? ' selected' : '') . '>Yok</option></select>') ?>
                            <?= $alan('SMTP Kullanıcı', '<input type="text" name="smtp_user" class="' . $inp . '" value="' . $v('smtp_user') . '" autocomplete="off">',
                                'Tek hesapla oturum açılır; aşağıdaki üç gönderen adresi de aynı alan adında (prolyn.net) olmalıdır.') ?>
                            <?= $alan('SMTP Şifre', '<input type="password" name="smtp_pass" class="' . $inp . '" value="" autocomplete="new-password" placeholder="' . ($doluMu('smtp_pass') ? '•••••••• (kayıtlı — değiştirmek için yazın)' : 'Şifre girilmedi') . '">',
                                'Güvenlik için kayıtlı şifre ekranda gösterilmez. Boş bırakırsanız mevcut şifre korunur.') ?>
                        </div>
                    </div>

                    <!-- GÖNDEREN ADRESLERİ -->
                    <div class="card mb-6">
                        <div class="card-header border-0"><div class="card-title m-0"><h3 class="fw-bold m-0">Gönderen Adresleri</h3></div></div>
                        <div class="card-body border-top p-9">
                            <div class="notice bg-light-primary rounded border-primary border border-dashed p-5 mb-8 fs-7">
                                Hangi e-posta hangi adresten gider:
                                <strong>Lead / kampanya</strong> → Destek ·
                                <strong>İletişim formu yanıtları, bilgilendirme</strong> → Bilgi ·
                                <strong>Sistem bildirimleri, şifre işlemleri</strong> → No-reply.
                            </div>
                            <?= $alan('Gönderen Ad', '<input type="text" name="from_name" class="' . $inp . '" value="' . $v('from_name', 'Prolyn') . '">') ?>
                            <?= $alan('Destek E-posta <span class="badge badge-light-info ms-1">lead</span>', '<input type="email" name="support_email" class="' . $inp . '" value="' . $v('support_email') . '" placeholder="destek@prolyn.net">',
                                'Lead kampanya e-postaları bu adresten gider; firmaların yanıtları bu kutuya düşer.') ?>
                            <?= $alan('Bilgi E-posta <span class="badge badge-light-primary ms-1">info</span>', '<input type="email" name="from_email" class="' . $inp . '" value="' . $v('from_email') . '" placeholder="info@prolyn.net">',
                                'İletişim mesajlarına verilen yanıtlar ve bilgilendirme e-postaları.') ?>
                            <?= $alan('Geri Dönüşsüz E-posta <span class="badge badge-light ms-1">noreply</span>', '<input type="email" name="noreply_email" class="' . $inp . '" value="' . $v('noreply_email') . '" placeholder="noreply@prolyn.net">',
                                'Otomatik sistem bildirimleri. Yanıt beklenmeyen e-postalar.') ?>
                            <?= $alan('Bildirim Alıcısı', '<input type="email" name="notify_email" class="' . $inp . '" value="' . $v('notify_email') . '" placeholder="info@prolyn.net">',
                                'Siteden gelen iletişim formu bildirimleri bu adrese gelir (gönderen değil, alıcı).') ?>
                        </div>
                    </div>

                    <!-- GÖNDERİLENLER KOPYASI -->
                    <div class="card mb-6">
                        <div class="card-header border-0"><div class="card-title m-0"><h3 class="fw-bold m-0">Gönderilenler Kopyası</h3></div></div>
                        <div class="card-body border-top p-9">
                            <div class="notice bg-light-info rounded border-info border border-dashed p-5 mb-8 fs-7">
                                Sistemden giden her e-postanın birebir kopyası, gönderen posta kutusunun <strong>Gönderilenler</strong> klasörüne eklenir;
                                webmail'de veya telefonda ne gittiğini görürsünüz. Kutu şifresi girilen adres kendi kutusuyla gönderir ve kopya o kutuya düşer;
                                şifre boşsa ana SMTP hesabıyla gönderilir ve kopya o hesabın Gönderilenler klasörüne düşer.
                                Teslim edilemeyen e-postaların geri dönüş bildirimi (bounce) gönderen kutusunun <strong>Gelen</strong> klasörüne gelir.
                            </div>
                            <?php $kopyaAcik = ($config['mail_sent_copy'] ?? '1') === '1'; ?>
                            <?= $alan('Kopya al', '<select name="mail_sent_copy" class="form-select form-select-solid form-select-lg">'
                                . '<option value="1"' . ($kopyaAcik ? ' selected' : '') . '>Açık</option>'
                                . '<option value="0"' . (!$kopyaAcik ? ' selected' : '') . '>Kapalı</option></select>') ?>
                            <?= $alan('Destek kutu şifresi', '<input type="password" name="mail_pass_destek" class="' . $inp . '" value="" autocomplete="new-password" placeholder="' . ($doluMu('mail_pass_destek') ? '•••••••• (kayıtlı — değiştirmek için yazın)' : 'Boş: ana SMTP hesabı kullanılır') . '">') ?>
                            <?= $alan('Bilgi kutu şifresi', '<input type="password" name="mail_pass_bilgi" class="' . $inp . '" value="" autocomplete="new-password" placeholder="' . ($doluMu('mail_pass_bilgi') ? '•••••••• (kayıtlı — değiştirmek için yazın)' : 'Boş: ana SMTP hesabı kullanılır') . '">') ?>
                            <?= $alan('No-reply kutu şifresi', '<input type="password" name="mail_pass_noreply" class="' . $inp . '" value="" autocomplete="new-password" placeholder="' . ($doluMu('mail_pass_noreply') ? '•••••••• (kayıtlı — değiştirmek için yazın)' : 'Boş: ana SMTP hesabı kullanılır') . '">') ?>
                            <?= $alan('IMAP Host', '<input type="text" name="imap_host" class="' . $inp . '" value="' . $v('imap_host') . '" placeholder="Boş: SMTP host (' . $v('smtp_host') . ')">') ?>
                            <?= $alan('IMAP Port', '<input type="number" name="imap_port" class="' . $inp . '" value="' . $v('imap_port', '993') . '">', '993 = SSL (önerilen), 143 = STARTTLS.') ?>
                            <?= $alan('Gönderilenler klasörü', '<input type="text" name="imap_sent_folder" class="' . $inp . '" value="' . $v('imap_sent_folder') . '" placeholder="Boş: otomatik bulunur (cPanel: INBOX.Sent)">',
                                'Kopya yanlış klasöre düşerse buraya tam adı yazın.') ?>
                        </div>
                    </div>

                    <!-- WHATSAPP -->
                    <div class="card mb-6">
                        <div class="card-header border-0"><div class="card-title m-0"><h3 class="fw-bold m-0">WhatsApp Bildirimleri</h3></div></div>
                        <div class="card-body border-top p-9">
                            <?php $waAktif = ($config['whatsapp_enabled'] ?? '0') === '1'; ?>
                            <?= $alan('WhatsApp Bildirim', '<select name="whatsapp_enabled" class="form-select form-select-solid form-select-lg">'
                                . '<option value="0"' . (!$waAktif ? ' selected' : '') . '>Pasif</option>'
                                . '<option value="1"' . ($waAktif ? ' selected' : '') . '>Aktif</option></select>',
                                'Aktifken siteden gelen her iletişim mesajı aşağıdaki numaraya WhatsApp ile bildirilir.') ?>
                            <?= $alan('WhatsApp Bildirim No', '<input type="text" name="whatsapp_notify_number" class="' . $inp . '" value="' . $v('whatsapp_notify_number') . '" placeholder="905xxxxxxxxx" inputmode="numeric">',
                                'Bildirimi alacak numara; ülke koduyla, başında + ve 0 olmadan.') ?>
                            <?= $alan('WhatsApp API URL', '<input type="text" name="whatsapp_api_url" class="' . $inp . '" value="' . $v('whatsapp_api_url', 'https://graph.facebook.com/v23.0/{phone_id}/messages') . '">',
                                'Meta WhatsApp Cloud API adresi. <code>{phone_id}</code> kısmı aşağıdaki Phone number ID ile otomatik doldurulur; olduğu gibi bırakın.') ?>
                            <?= $alan('Phone number ID', '<input type="text" name="whatsapp_phone_id" class="' . $inp . '" value="' . $v('whatsapp_phone_id') . '" inputmode="numeric" placeholder="1234567890123456">',
                                'Meta for Developers → uygulamanız → WhatsApp → API Setup ekranındaki "Phone number ID".') ?>
                            <?= $alan('Erişim Anahtarı (Token)', '<input type="password" name="whatsapp_token" class="' . $inp . '" value="" autocomplete="new-password" placeholder="' . ($doluMu('whatsapp_token') ? '•••••••• (kayıtlı — değiştirmek için yazın)' : 'Anahtar girilmedi') . '">',
                                'Business Manager → System Users ekranından üretilen kalıcı anahtar (geçici 24 saatlik anahtar kısa sürede düşer).') ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-8">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">İptal</button>
                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                    </div>
                </form>

                <!-- TESTLER: ayrı formlar; kaydedilmiş ayarlarla anında gönderir -->
                <div class="card mb-10">
                    <div class="card-header border-0"><div class="card-title m-0"><h3 class="fw-bold m-0">Bağlantı Testi</h3></div></div>
                    <div class="card-body border-top p-9">
                        <form method="POST" class="row g-3 align-items-end mb-8">
                            <?= csrfTokenField() ?><input type="hidden" name="action" value="mail_test">
                            <div class="col-md-4"><label class="form-label fs-7">Gönderen</label>
                                <select name="kimlik" class="form-select form-select-solid">
                                    <option value="destek">Destek (lead)</option>
                                    <option value="bilgi">Bilgi (info)</option>
                                    <option value="noreply">No-reply</option>
                                </select></div>
                            <div class="col-md-5"><label class="form-label fs-7">Alıcı</label>
                                <input type="email" name="test_alici" class="form-control form-control-solid" placeholder="<?= $v('notify_email') ?>"></div>
                            <div class="col-md-3"><button class="btn btn-light-primary w-100">Test e-postası gönder</button></div>
                        </form>
                        <form method="POST" class="d-flex flex-wrap align-items-center gap-3">
                            <?= csrfTokenField() ?><input type="hidden" name="action" value="wa_test">
                            <button class="btn btn-light-success">WhatsApp test mesajı gönder</button>
                            <span class="text-muted fs-7">Meta'nın onaylı <code>hello_world</code> şablonu, "WhatsApp Bildirim No" numarasına gider.</span>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
