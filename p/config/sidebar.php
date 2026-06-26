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
?>
<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
            data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">
                
                <!-- 1. DASHBOARD -->
                <div class="menu-item">
                    <a class="menu-link <?= isActive('index.php') ?>" href="index.php">
                        <span class="menu-icon"><i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                <!-- 2. KULLANICI & MÜŞTERİ -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Kullanıcı & Müşteri</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['customers']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-people fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></span>
                        <span class="menu-title">İşletmeler (Tenants)</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('tenants.php') ?>" href="tenants.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">İşletme Listesi</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('customers/admins.php') ?>" href="dist/apps/customers/admins.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Yöneticiler</span></a></div>
                    </div>
                </div>

                <!-- 3. ABONELİK & PAKET -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Abonelik & Paket</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['subscriptions']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-bill fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i></span>
                        <span class="menu-title">Abonelikler & Paketler</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('subscriptions/list.php') ?>" href="dist/apps/subscriptions/list.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Aktif Abonelikler</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('plans.php') ?>" href="plans.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">SaaS Paketleri</span></a></div>
                    </div>
                </div>

                <!-- 4. FİNANS & FATURA -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Finans & Fatura</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['finance']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-wallet fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                        <span class="menu-title">Finans</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/payments.php') ?>" href="dist/apps/finance/payments.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Ödemeler</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/invoices.php') ?>" href="dist/apps/finance/invoices.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Faturalar</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/billing_addresses.php') ?>" href="dist/apps/finance/billing_addresses.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Fatura Adresleri</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/coupons.php') ?>" href="dist/apps/finance/coupons.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Kuponlar</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('finance/tax_rates.php') ?>" href="dist/apps/finance/tax_rates.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Vergi Oranları</span></a></div>
                    </div>
                </div>

                <!-- 5. DESTEK -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Destek Merkezi</span></div>
                </div>
                <div class="menu-item">
                    <a class="menu-link <?= isActive('support/tickets.php') ?>" href="dist/apps/support/tickets.php">
                        <span class="menu-icon"><i class="ki-duotone ki-messages fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></span>
                        <span class="menu-title">Destek Talepleri</span>
                    </a>
                </div>

                <!-- 6. BİLDİRİM & İLETİŞİM -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Bildirim & İletişim</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['communication']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-sms fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">İletişim</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('communication/notifications.php') ?>" href="dist/apps/communication/notifications.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Bildirimler</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('communication/email_templates.php') ?>" href="dist/apps/communication/email_templates.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Mail Şablonları</span></a></div>
                    </div>
                </div>

                <!-- 7. DOSYA & İÇERİK -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Dosya & İçerik</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['content']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-file fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">İçerik Yön.</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('content/files.php') ?>" href="dist/apps/content/files.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Dosya Yöneticisi</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('content/sales.php') ?>" href="dist/apps/content/sales.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Satış İçerikleri</span></a></div>
                    </div>
                </div>
                
                <!-- 8. ENTEGRASYON & API (YENİ) -->
                 <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Entegrasyon & API</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['integration']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-technology-4 fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">API & Webhooks</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                         <div class="menu-item"><a class="menu-link <?= isActive('integration/webhooks.php') ?>" href="dist/apps/integration/webhooks.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Webhook Yönetimi</span></a></div>
                         <div class="menu-item"><a class="menu-link <?= isActive('integration/api_keys.php') ?>" href="dist/apps/integration/api_keys.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">API Anahtarları</span></a></div>
                    </div>
                </div>
                
                <!-- 9. FEATURE & KOTA (YENİ) -->
                 <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Feature & Kota</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['features']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-graph-up fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Özellik & Limitler</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                         <div class="menu-item"><a class="menu-link <?= isActive('features/usage.php') ?>" href="dist/apps/features/usage.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Kullanım Takibi</span></a></div>
                         <div class="menu-item"><a class="menu-link <?= isActive('features/limits.php') ?>" href="dist/apps/features/limits.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Kota Limitleri</span></a></div>
                    </div>
                </div>

                <!-- 10. DUYURU SİSTEMİ (YENİ) -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Duyuru Sistemi</span></div>
                </div>
                <div class="menu-item">
                    <a class="menu-link <?= isActive('announcements/list.php') ?>" href="dist/apps/announcements/list.php">
                        <span class="menu-icon"><i class="ki-duotone ki-notification-on fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                        <span class="menu-title">Duyurular</span>
                    </a>
                </div>

                <!-- 11. SİSTEM & GÜVENLİK -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Sistem & Güvenlik</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['security']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-shield-tick fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Güvenlik</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('security/logs.php') ?>" href="dist/apps/security/logs.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Sistem Logları</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('security/login_attempts.php') ?>" href="dist/apps/security/login_attempts.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Giriş Denemeleri</span></a></div>
                    </div>
                </div>
                
                <!-- 12. AUDIT & COMPLIANCE (YENİ) -->
                 <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Denetim & Uyum</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['audit']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-file-sheet fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Audit Logları</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                         <div class="menu-item"><a class="menu-link <?= isActive('audit/trail.php') ?>" href="dist/apps/audit/trail.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">İşlem Geçmişi</span></a></div>
                         <div class="menu-item"><a class="menu-link <?= isActive('audit/gdpr.php') ?>" href="dist/apps/audit/gdpr.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">GDPR Raporları</span></a></div>
                    </div>
                </div>

                <!-- 13. SİSTEM AYARLARI -->
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Ayarlar</span></div>
                </div>
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= showAccordion(['settings']) ?>">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="ki-duotone ki-setting-2 fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <span class="menu-title">Yapılandırma</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/general.php') ?>" href="dist/apps/settings/general.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Genel Ayarlar</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('settings/maintenance.php') ?>" href="dist/apps/settings/maintenance.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">Bakım Modu</span></a></div>
                        <div class="menu-item"><a class="menu-link <?= isActive('toolbars/db_control.php') ?>" href="dist/toolbars/db_control.php"><span class="menu-bullet"><span class="bullet bullet-dot"></span></span><span class="menu-title">DB Kontrol</span></a></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
    <a href="https://www.prolyn.net/docs" target="_blank" class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Dokümantasyon">
        <span class="btn-label">Prolyn Docs</span>
        <i class="ki-duotone ki-document btn-icon fs-2 m-0"><span class="path1"></span><span class="path2"></span></i>
    </a>
</div>
</div>