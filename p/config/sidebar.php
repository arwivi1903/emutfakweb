<?php
// Sidebar Menu Structure - 13-Module Pro SaaS Layout
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = dirname($_SERVER['PHP_SELF']);

function isActive($keyword) {
    global $current_page, $current_dir;
    return (strpos($current_page, $keyword) !== false || strpos($current_dir, $keyword) !== false) ? 'active' : '';
}

function showAccordion($keywords) {
    global $current_page, $current_dir;
    foreach ($keywords as $keyword) {
        if (strpos($current_page, $keyword) !== false || strpos($current_dir, $keyword) !== false) return 'here show';
    }
    return '';
}

$sidebarResetRequestPending = 0;
$sidebarResetQueuePending = 0;
$sidebarResetTokenOpen = 0;

try {
    if (isset($db) && method_exists($db, 'tableExists') && $db->tableExists('sifre_sifirlama_talepleri')) {
        $sidebarResetRequestPending = (int)$db->getColumn(
            "SELECT COUNT(*) FROM sifre_sifirlama_talepleri WHERE request_status IN ('pending', 'in_review', 'matched')"
        );
    }
    if (isset($db) && method_exists($db, 'tableExists') && $db->tableExists('mail_kuyrugu')) {
        $sidebarResetQueuePending = (int)$db->getColumn(
            "SELECT COUNT(*) FROM mail_kuyrugu WHERE status IN ('pending', 'queued')"
        );
    }
    if (isset($db) && method_exists($db, 'tableExists') && $db->tableExists('sifre_sifirlama')) {
        $sidebarResetTokenOpen = (int)$db->getColumn(
            "SELECT COUNT(*) FROM sifre_sifirlama WHERE used = 0 AND expires_at >= NOW()"
        );
    }
} catch (Throwable $e) {
    $sidebarResetRequestPending = 0;
    $sidebarResetQueuePending = 0;
    $sidebarResetTokenOpen = 0;
}
?>
<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
            data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">
                
                <!-- 1. DASHBOARD -->
                <div class="menu-item">
                    <a class="menu-link <?= isActive('index.php') ?>" href="index.php">
                        <span class="menu-icon"><i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                <!-- 2. CRM / MÜŞTERİ YÖNETİMİ -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">CRM Yönetimi</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['leads.php', 'lead_detail.php', 'lead_import.php', 'lead_templates.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-rocket fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Potansiyel Müşteriler</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/leads.php') ?>" href="crm/leads.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Lead Listesi</span></a></div>
                        <div class="menu-item"><a class="menu-link" href="crm/leads.php?takip=bugun"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Bugün Takip</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/lead_import.php') ?>" href="crm/lead_import.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">İçe Aktar</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/lead_templates.php') ?>" href="crm/lead_templates.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Mesaj Şablonları</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['tenants.php', 'tenant_detail.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-briefcase fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Kurum / Müşteriler</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/tenants.php') ?>" href="crm/tenants.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Müşteri Listesi</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/feature_usage.php') ?>" href="crm/feature_usage.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Müşteri Kullanımları</span></a></div>
                    </div>
                </div>
                
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['subscriptions.php', 'subscription_history.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-calendar-tick fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i></span>
                        <span class="menu-title">Abonelik Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/subscriptions.php') ?>" href="crm/subscriptions.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Abonelik Listesi</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/subscription_history.php') ?>" href="crm/subscription_history.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Abonelik Geçmişi</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['tickets.php', 'ticket_detail.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-messages fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></span>
                        <span class="menu-title">Destek Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/tickets.php') ?>" href="crm/tickets.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Destek Talepleri</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['announcements.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-notification-on fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></span>
                        <span class="menu-title">Duyuru Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/announcements.php') ?>" href="crm/announcements.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Duyurular Listesi</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['coupons.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-discount fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Kupon Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('crm/coupons.php') ?>" href="crm/coupons.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Kuponlar</span></a></div>
                    </div>
                </div>

                <!-- İÇERİK YÖNETİMİ -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">İçerik Yönetimi</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['content/sales.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Satış ve Pazarlama</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('content/sales.php') ?>" href="content/sales.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Satış İçerikleri</span></a></div>
                    </div>
                </div>

                <!-- 3. FİNANS YÖNETİMİ -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Finans Yönetimi</span></div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['invoices.php', 'invoice_addresses.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Fatura Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/invoices.php') ?>" href="finance/invoices.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Faturalar</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/invoice_addresses.php') ?>" href="finance/invoice_addresses.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Fatura Adresleri</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['payments.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-wallet fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                        <span class="menu-title">Ödeme Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/payments.php') ?>" href="finance/payments.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Ödemeler Listesi</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['finance', 'finance/taxes.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-bank fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Finans Ayarları</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/taxes.php') ?>" href="finance/taxes.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Vergi Oranları</span></a></div>
                    </div>
                </div>

                <!-- 4. WEB SİTESİ YÖNETİMİ -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Web Sitesi Yönetimi</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['website']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-screen fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                        <span class="menu-title">Vitrin Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('website/seo.php') ?>" href="website/seo.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">SEO ve Temel Ayarlar</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('website/features.php') ?>" href="website/features.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Ana Sayfa Özellikleri</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('website/modules.php') ?>" href="website/modules.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Web Modülleri</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('website/blog.php') ?>" href="website/blog.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Blog</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('website/videos.php') ?>" href="website/videos.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Videolar</span></a></div>
                    </div>
                </div>

                <!-- 5. SİSTEM AYARLARI -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Sistem Ayarları</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['settings', 'settings/api_keys.php', 'plans.php', 'settings/app_modules.php', 'settings/password_reset.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-setting-2 fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Yönetim Ayarları</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('plans.php') ?>" href="plans.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">SaaS Paketleri (Matris)</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/admins.php') ?>" href="settings/admins.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Yöneticiler</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/password_reset.php') ?>" href="settings/password_reset.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Şifre Talep Havuzu (<?= (int)$sidebarResetRequestPending ?>)</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/roles.php') ?>" href="settings/roles.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Roller ve Yetkiler</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/api_keys.php') ?>" href="settings/api_keys.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">API Anahtarları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/files.php') ?>" href="settings/files.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Sistem Dosyaları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/package_features.php') ?>" href="settings/package_features.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Paket Özellikleri</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/app_modules.php') ?>" href="settings/app_modules.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Uygulama Modülleri</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['email_templates.php', 'email_queue.php', 'dist/apps/settings/general.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-sms fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">E-posta Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('dist/apps/settings/general.php') ?>" href="dist/apps/settings/general.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Mail Ayarları (SMTP)</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/email_templates.php') ?>" href="settings/email_templates.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">E-posta Şablonları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/email_queue.php') ?>" href="settings/email_queue.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">E-posta Kuyruğu</span></a></div>
                    </div>
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['logs', 'settings/logs.php', 'settings/logs_archive.php', 'logs/notifications.php', 'logs/notification_queue.php', 'logs/webhooks.php', 'logs/webhook_logs.php']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-shield-search fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                        <span class="menu-title">Log ve Sistem Yönetimi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/logs.php') ?>" href="settings/logs.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Yönetici Logları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/logs_archive.php') ?>" href="settings/logs_archive.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Log Arşivi</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/audit.php') ?>" href="logs/audit.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Sistem Denetim Logları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/activity.php') ?>" href="logs/activity.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Aktivite Logları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/user_activity.php') ?>" href="logs/user_activity.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Kullanıcı Aktiviteleri</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/login_attempts.php') ?>" href="logs/login_attempts.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Giriş Denemeleri</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/sessions.php') ?>" href="logs/sessions.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Aktif Oturumlar</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/maintenance.php') ?>" href="logs/maintenance.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Bakım Logları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/system_errors.php') ?>" href="logs/system_errors.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Sistem Hataları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/system_events.php') ?>" href="logs/system_events.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Sistem Olayları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/password_resets.php') ?>" href="logs/password_resets.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Şifre Sıfırlama Logları (<?= (int)$sidebarResetTokenOpen ?>)</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/webhooks.php') ?>" href="logs/webhooks.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Webhook Tanımları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/webhook_logs.php') ?>" href="logs/webhook_logs.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Webhook İstek Geçmişi</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/notifications.php') ?>" href="logs/notifications.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Uygulama Bildirimleri</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('logs/notification_queue.php') ?>" href="logs/notification_queue.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Bildirim Kuyruğu (<?= (int)$sidebarResetQueuePending ?>)</span></a></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!--end::Sidebar menu-->
</div>
<!--end::Sidebar-->
