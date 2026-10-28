<?php
/**
 * Master panel yan menüsü (sidebar).
 *
 * Neden yeniden düzenlendi:
 *  - Tek alt öğesi olan 6 akordeon (Destek, Duyuru, Kupon, Ödeme...) doğrudan bağlantıya çevrildi: 1 tık az.
 *  - 15 öğelik "Log ve Sistem Yönetimi" üç amaca göre bölündü (denetim/güvenlik, sistem/bakım, entegrasyon).
 *  - Aktif sayfa tespiti "içerir" yerine TAM yol eşleşmesi: eskiden 'settings' anahtarı yüzünden
 *    Yönetim Ayarları ile E-posta grubu aynı anda açılıyordu.
 *  - Bekleyen sayılar "(3)" metni yerine rozet; grup kapalıyken de rozet görünür (içinde bekleyen iş var).
 *  - Menü veri olarak tanımlandı; yeni sayfa eklemek tek satır.
 * NOT: Menüde göstermek/gizlemek yetkilendirme DEĞİLDİR; her sayfa kendi yetki kontrolünü yapmaya devam eder.
 * Bağlantılar header.php'deki mevcut yapıya göre köke göre görecelidir (değiştirilmedi).
 */
$sbPath = (string)($_SERVER['PHP_SELF'] ?? '');

/** Bağlantı bu sayfa mı? Sorgu parametresi tanımlıysa o da eşleşmeli (örn. Bugün Takip). */
function sbAktifMi(array $oge): bool {
    global $sbPath;
    $yol = strtok($oge['href'], '?');
    $son = '/' . ltrim($yol, '/');
    if (substr($sbPath, -strlen($son)) !== $son) { return false; }
    if (!empty($oge['q'])) {
        foreach ($oge['q'] as $k => $v) { if (($_GET[$k] ?? null) !== $v) { return false; } }
        return true;
    }
    // Sorgu parametreli kardeş (Bugün Takip) açıkken ana liste aktif görünmesin
    return empty($oge['digerQ']) || empty($_GET[$oge['digerQ']]);
}

// Bekleyen iş sayaçları (tablo yoksa veya hata olursa 0: menü hiçbir zaman sayfayı bozmaz)
$sbSay = ['mesaj' => 0, 'sifreTalep' => 0, 'sifreToken' => 0, 'mailKuyruk' => 0];
$sbSor = [
    'mesaj'      => ['iletisim_mesajlari', "SELECT COUNT(*) FROM iletisim_mesajlari WHERE status = 'new'"],
    'sifreTalep' => ['sifre_sifirlama_talepleri', "SELECT COUNT(*) FROM sifre_sifirlama_talepleri WHERE request_status IN ('pending','in_review','matched')"],
    'sifreToken' => ['sifre_sifirlama', "SELECT COUNT(*) FROM sifre_sifirlama WHERE used = 0 AND expires_at >= NOW()"],
    'mailKuyruk' => ['mail_kuyrugu', "SELECT COUNT(*) FROM mail_kuyrugu WHERE status IN ('pending','queued')"],
];
foreach ($sbSor as $anahtar => [$tablo, $sql]) {
    try {
        if (isset($db) && method_exists($db, 'tableExists') && $db->tableExists($tablo)) {
            $sbSay[$anahtar] = (int)$db->getColumn($sql);
        }
    } catch (Throwable $e) { $sbSay[$anahtar] = 0; }
}

// ikon: ki-duotone adı + path sayısı (Metronic duotone ikonları için gerekli)
$sbMenu = [
    ['href' => 'index.php', 'baslik' => 'Dashboard', 'ikon' => ['element-11', 4]],

    ['bolum' => 'Müşteriler ve Satış'],
    ['href' => 'crm/messages.php', 'baslik' => 'Gelen Mesajlar', 'ikon' => ['sms', 2], 'rozet' => 'mesaj', 'rozetRenk' => 'primary'],
    ['baslik' => 'Potansiyel Müşteriler', 'ikon' => ['rocket', 2], 'alt' => [
        ['href' => 'crm/leads.php', 'baslik' => 'Lead Listesi', 'digerQ' => 'takip'],
        ['href' => 'crm/leads.php?takip=bugun', 'baslik' => 'Bugün Takip', 'q' => ['takip' => 'bugun']],
        ['href' => 'crm/lead_import.php', 'baslik' => 'İçe Aktar'],
        ['href' => 'crm/lead_templates.php', 'baslik' => 'Mesaj Şablonları'],
        // Lead detayı listeden açılır; menüde yok ama grup açık kalsın
        ['href' => 'crm/lead_detail.php', 'baslik' => '', 'gizli' => true],
    ]],
    ['baslik' => 'Müşteri Firmalar', 'ikon' => ['briefcase', 2], 'alt' => [
        ['href' => 'crm/tenants.php', 'baslik' => 'Firma Listesi'],
        ['href' => 'crm/feature_usage.php', 'baslik' => 'Özellik Kullanımı'],
        ['href' => 'crm/tenant_detail.php', 'baslik' => '', 'gizli' => true],
    ]],
    ['baslik' => 'Abonelikler', 'ikon' => ['calendar-tick', 6], 'alt' => [
        ['href' => 'crm/subscriptions.php', 'baslik' => 'Abonelik Listesi'],
        ['href' => 'crm/subscription_history.php', 'baslik' => 'Abonelik Geçmişi'],
    ]],
    ['href' => 'crm/tickets.php', 'baslik' => 'Destek Talepleri', 'ikon' => ['messages', 5], 'ek' => ['crm/ticket_detail.php']],
    ['href' => 'crm/announcements.php', 'baslik' => 'Duyurular', 'ikon' => ['notification-on', 5]],
    ['href' => 'crm/coupons.php', 'baslik' => 'Kuponlar', 'ikon' => ['discount', 2]],
    ['href' => 'content/sales.php', 'baslik' => 'Satış İçerikleri', 'ikon' => ['document', 2]],

    ['bolum' => 'Finans'],
    ['baslik' => 'Faturalar', 'ikon' => ['document', 2], 'alt' => [
        ['href' => 'finance/invoices.php', 'baslik' => 'Fatura Listesi'],
        ['href' => 'finance/invoice_addresses.php', 'baslik' => 'Fatura Adresleri'],
    ]],
    ['href' => 'finance/payments.php', 'baslik' => 'Ödemeler', 'ikon' => ['wallet', 4]],
    ['href' => 'finance/taxes.php', 'baslik' => 'Vergi Oranları', 'ikon' => ['bank', 2]],

    ['bolum' => 'Web Sitesi'],
    ['baslik' => 'Site İçeriği', 'ikon' => ['screen', 4], 'alt' => [
        ['href' => 'website/seo.php', 'baslik' => 'SEO ve Temel Ayarlar'],
        ['href' => 'website/form_security.php', 'baslik' => 'Form Güvenliği (reCAPTCHA)'],
        ['href' => 'website/features.php', 'baslik' => 'Ana Sayfa Özellikleri'],
        ['href' => 'website/modules.php', 'baslik' => 'Web Modülleri'],
        ['href' => 'website/module_form.php', 'baslik' => '', 'gizli' => true],
        ['href' => 'website/blog.php', 'baslik' => 'Blog'],
        ['href' => 'website/blog_form.php', 'baslik' => '', 'gizli' => true],
        ['href' => 'website/videos.php', 'baslik' => 'Videolar'],
    ]],

    ['bolum' => 'Paketler ve Sistem'],
    ['baslik' => 'Paketler ve Modüller', 'ikon' => ['category', 4], 'alt' => [
        ['href' => 'plans.php', 'baslik' => 'Paket Matrisi'],
        ['href' => 'settings/package_features.php', 'baslik' => 'Paket Özellikleri'],
        ['href' => 'settings/app_modules.php', 'baslik' => 'Uygulama Modülleri'],
    ]],
    ['baslik' => 'Yönetim ve Güvenlik', 'ikon' => ['shield-tick', 2], 'alt' => [
        ['href' => 'settings/admins.php', 'baslik' => 'Yöneticiler'],
        ['href' => 'settings/roles.php', 'baslik' => 'Roller ve Yetkiler'],
        ['href' => 'settings/password_reset.php', 'baslik' => 'Şifre Talepleri', 'rozet' => 'sifreTalep', 'rozetRenk' => 'warning'],
        ['href' => 'settings/api_keys.php', 'baslik' => 'API Anahtarları'],
    ]],
    ['baslik' => 'E-posta', 'ikon' => ['sms', 2], 'alt' => [
        ['href' => 'dist/apps/settings/general.php', 'baslik' => 'Mail Ayarları (SMTP)'],
        ['href' => 'settings/email_templates.php', 'baslik' => 'E-posta Şablonları'],
        ['href' => 'settings/email_queue.php', 'baslik' => 'E-posta Kuyruğu', 'rozet' => 'mailKuyruk', 'rozetRenk' => 'warning'],
    ]],
    ['href' => 'settings/files.php', 'baslik' => 'Sistem Dosyaları', 'ikon' => ['folder', 2]],

    ['bolum' => 'İzleme ve Kayıtlar'],
    ['baslik' => 'Denetim ve Güvenlik', 'ikon' => ['shield-search', 3], 'alt' => [
        ['href' => 'settings/logs.php', 'baslik' => 'Yönetici Logları'],
        ['href' => 'logs/audit.php', 'baslik' => 'Denetim Logları'],
        ['href' => 'logs/login_attempts.php', 'baslik' => 'Giriş Denemeleri'],
        ['href' => 'logs/sessions.php', 'baslik' => 'Aktif Oturumlar'],
        ['href' => 'logs/password_resets.php', 'baslik' => 'Şifre Sıfırlama Logları', 'rozet' => 'sifreToken', 'rozetRenk' => 'secondary'],
        ['href' => 'settings/logs_archive.php', 'baslik' => 'Log Arşivi'],
    ]],
    ['baslik' => 'Sistem ve Bakım', 'ikon' => ['setting-2', 2], 'alt' => [
        ['href' => 'logs/system_errors.php', 'baslik' => 'Sistem Hataları'],
        ['href' => 'logs/system_events.php', 'baslik' => 'Sistem Olayları'],
        ['href' => 'logs/maintenance.php', 'baslik' => 'Bakım Logları'],
        ['href' => 'logs/activity.php', 'baslik' => 'Aktivite Logları'],
        ['href' => 'logs/user_activity.php', 'baslik' => 'Kullanıcı Aktiviteleri'],
    ]],
    ['baslik' => 'Bildirim ve Webhook', 'ikon' => ['notification-status', 4], 'alt' => [
        ['href' => 'logs/notifications.php', 'baslik' => 'Uygulama Bildirimleri'],
        ['href' => 'logs/notification_queue.php', 'baslik' => 'Bildirim Kuyruğu'],
        ['href' => 'logs/webhooks.php', 'baslik' => 'Webhook Tanımları'],
        ['href' => 'logs/webhook_logs.php', 'baslik' => 'Webhook İstek Geçmişi'],
    ]],
];

function sbIkon(array $ikon): string {
    $p = '';
    for ($i = 1; $i <= (int)$ikon[1]; $i++) { $p .= '<span class="path' . $i . '"></span>'; }
    return '<span class="menu-icon"><i class="ki-duotone ki-' . htmlspecialchars($ikon[0], ENT_QUOTES, 'UTF-8') . ' fs-2">' . $p . '</i></span>';
}
function sbRozet(?string $anahtar, string $renk, int $sayi): string {
    if (!$anahtar || $sayi < 1) { return ''; }
    return '<span class="menu-badge"><span class="badge badge-' . htmlspecialchars($renk, ENT_QUOTES, 'UTF-8')
        . ' badge-circle fw-bold fs-8">' . ($sayi > 99 ? '99+' : $sayi) . '</span></span>';
}
function sbH(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
            data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">
<?php foreach ($sbMenu as $oge): ?>
<?php if (isset($oge['bolum'])): ?>
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7"><?= sbH($oge['bolum']) ?></span></div>
                </div>
<?php elseif (isset($oge['alt'])): ?>
<?php
    $acik = false; $toplam = 0; $rozetRenk = 'primary';
    foreach ($oge['alt'] as $a) {
        if (sbAktifMi($a)) { $acik = true; }
        if (!empty($a['rozet'])) { $toplam += $sbSay[$a['rozet']] ?? 0; $rozetRenk = $a['rozetRenk'] ?? 'primary'; }
    }
?>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion<?= $acik ? ' here show' : '' ?>">
                    <span class="menu-link">
                        <?= sbIkon($oge['ikon']) ?>
                        <span class="menu-title"><?= sbH($oge['baslik']) ?></span>
                        <?php if ($toplam > 0 && !$acik): ?><?= sbRozet('x', $rozetRenk, $toplam) ?><?php endif; ?>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
<?php foreach ($oge['alt'] as $a): if (!empty($a['gizli'])) { continue; } ?>
                        <div class="menu-item"><a class="menu-link<?= sbAktifMi($a) ? ' active' : '' ?>" href="<?= sbH($a['href']) ?>"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title"><?= sbH($a['baslik']) ?></span><?= sbRozet($a['rozet'] ?? null, $a['rozetRenk'] ?? 'primary', $sbSay[$a['rozet'] ?? ''] ?? 0) ?></a></div>
<?php endforeach; ?>
                    </div>
                </div>
<?php else: ?>
<?php
    $aktif = sbAktifMi($oge);
    foreach (($oge['ek'] ?? []) as $e) { if (sbAktifMi(['href' => $e])) { $aktif = true; } }
?>
                <div class="menu-item">
                    <a class="menu-link<?= $aktif ? ' active' : '' ?>" href="<?= sbH($oge['href']) ?>">
                        <?= sbIkon($oge['ikon']) ?>
                        <span class="menu-title"><?= sbH($oge['baslik']) ?></span>
                        <?= sbRozet($oge['rozet'] ?? null, $oge['rozetRenk'] ?? 'primary', $sbSay[$oge['rozet'] ?? ''] ?? 0) ?>
                    </a>
                </div>
<?php endif; ?>
<?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
<!--end::Sidebar menu-->
</div>
<!--end::Sidebar-->
