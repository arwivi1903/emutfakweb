<?php

require_once __DIR__ . '/../../env.php';
require_once __DIR__ . '/../functions/auth.php';

// Hata gösterimi ortama göre (üretimde kapalı)
$__appDebug = filter_var(prolynweb_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $__appDebug ? '1' : '0');
ini_set('display_startup_errors', $__appDebug ? '1' : '0');
error_reporting($__appDebug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_NOTICE));

// Panel taban adresi (sabit /prolynweb/ yerine .env'den)
$__panelBase = prolynweb_panel_url() . '/';

// Güvenlik Başlıkları
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Oturumu, güvenli çerez parametreleri (Secure/HttpOnly/SameSite) devreye girmeden
// hiçbir kodun (ör. otomatik CSRF kontrolü) başlatmadığından emin olmak için
// combine.php'den ÖNCE açılır.
start_secure_session();

require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';

$db = new Database();

oturumkontrol();

// Aktif oturum takibini güncelle (Sistem Geneli Aktif Oturumlar Takibi)
if (isset($_SESSION['admin_id'])) {
    try {
        $current_sess = session_id();
        $check_sess = $db->getRow("SELECT session_id FROM oturumlar WHERE session_id = ?", [$current_sess]);
        if (!$check_sess) {
            // Oturum DB'den silinmiş (admin tarafından sonlandırılmış)
            header("Location: " . $__panelBase . "logout.php");
            exit;
        } else {
            $db->Update("UPDATE oturumlar SET last_activity = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 1440 MINUTE) WHERE session_id = ?", [$current_sess]);
        }
    } catch (Exception $e) {
        // Hata durumunda yoksay
    }
}

// meta ayarları çekme
$metatag = $db->getrow("SELECT * FROM setting");

$headerNotifications = [];
$headerUnreadCount = 0;
$headerAdminId = (int)($_SESSION['admin_id'] ?? 0);
$headerResetRequestPending = 0;
$headerResetQueuePending = 0;
$headerResetTokenOpen = 0;

try {
    if ($headerAdminId > 0 && method_exists($db, 'tableExists') && $db->tableExists('bildirimler')) {
        $headerNotifications = $db->getRowsAssoc(
            "SELECT notification_id, title, message, type, action_url, created_at
             FROM bildirimler
             WHERE admin_id = ? AND is_read = 0
             ORDER BY notification_id DESC
             LIMIT 10",
            [$headerAdminId]
        );
    }
} catch (Throwable $e) {
    $headerNotifications = [];
}

// Canlı hesaplanan bildirimler (yeni abonelik, süresi yaklaşan abonelik vb.)
$headerNotifications = array_merge($headerNotifications, prolyn_canli_bildirimler($db));
$headerUnreadCount = count($headerNotifications);

$headerOpenTickets = 0;
try {
    if (method_exists($db, 'tableExists') && $db->tableExists('destek_talepleri')) {
        $headerOpenTickets = (int)$db->getColumn(
            "SELECT COUNT(*) FROM destek_talepleri WHERE del_status = 1 AND status IN ('new', 'open', 'in_progress')"
        );
    }
} catch (Throwable $e) {
    $headerOpenTickets = 0;
}

try {
    if (method_exists($db, 'tableExists') && $db->tableExists('sifre_sifirlama_talepleri')) {
        $headerResetRequestPending = (int)$db->getColumn(
            "SELECT COUNT(*) FROM sifre_sifirlama_talepleri WHERE request_status IN ('pending', 'in_review', 'matched')"
        );
    }

    if (method_exists($db, 'tableExists') && $db->tableExists('mail_kuyrugu')) {
        $headerResetQueuePending = (int)$db->getColumn(
            "SELECT COUNT(*) FROM mail_kuyrugu WHERE status IN ('pending', 'queued')"
        );
    }

    if (method_exists($db, 'tableExists') && $db->tableExists('sifre_sifirlama')) {
        $headerResetTokenOpen = (int)$db->getColumn(
            "SELECT COUNT(*) FROM sifre_sifirlama WHERE used = 0 AND expires_at >= NOW()"
        );
    }
} catch (Throwable $e) {
    $headerResetRequestPending = 0;
    $headerResetQueuePending = 0;
    $headerResetTokenOpen = 0;
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <base href="<?= htmlspecialchars($__panelBase, ENT_QUOTES, 'UTF-8') ?>" />
    <title>Prolyn ERP</title>
    <meta charset="utf-8" />
    <?= csrfTokenMeta() ?>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="tr_TR" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Prolyn ERP" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <link rel="canonical" href="" />
    <link rel="shortcut icon" href="dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="dist/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <link href="dist/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
    <link href="dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="dist/assets/css/custom/dashboard_custom.css" rel="stylesheet" type="text/css" />
    <script>
    // Click-jacking önlemi: Sitenin başka bir frame içinde yüklenmesini engeller. if (window.top != window.self) { window.top.location.replace(window.self.location.href); }
    </script>
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">

    <script>
    var defaultThemeMode = "light";
    var themeMode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }
        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
    </script>

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <div id="kt_app_header" class="app-header" data-kt-sticky="true"
                data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize"
                data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
                <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
                    id="kt_app_header_container">
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px"
                            id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>


                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="index.php" class="d-lg-none">
                            <img alt="Logo" src="dist/assets/media/logos/default-small.svg" class="h-30px" />
                        </a>
                    </div>
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1"
                        id="kt_app_header_wrapper">
                        <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true"
                            data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}"
                            data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end"
                            data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true"
                            data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                            data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                            <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0"
                                id="kt_app_header_menu" data-kt-menu="true">

<!-- <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
data-kt-menu-placement="bottom-start"
class="menu-item here show menu-here-bg menu-lg-down-accordion me-0 me-lg-2">
<span class="menu-link">
<span class="menu-title">Dashboards</span>
<span class="menu-arrow d-lg-none"></span>
</span>

<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown p-0 w-100 w-lg-850px">
<div class="menu-state-bg menu-extended overflow-hidden overflow-lg-visible"
data-kt-menu-dismiss="true">
<div class="row">
<div class="col-lg-8 mb-3 mb-lg-0 py-3 px-3 py-lg-6 px-lg-6">
<div class="row">
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="index.php" class="menu-link active">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i
class="ki-duotone ki-element-11 text-primary fs-1">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
</i>
</span>
<span class="d-flex flex-column">
<span
class="fs-6 fw-bold text-gray-800">Default</span>
<span
class="fs-7 fw-semibold text-muted">Reports
& statistics</span>
</span>
</a>
</div>
</div>
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/ecommerce.html"
class="menu-link">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i
class="ki-duotone ki-basket text-danger fs-1">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
</i>
</span>
<span class="d-flex flex-column">
<span
class="fs-6 fw-bold text-gray-800">eCommerce</span>
<span class="fs-7 fw-semibold text-muted">Sales
reports</span>
</span>
</a>
</div>
</div>
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/projects.html"
class="menu-link">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i
class="ki-duotone ki-abstract-44 text-info fs-1">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="d-flex flex-column">
<span
class="fs-6 fw-bold text-gray-800">Projects</span>
<span class="fs-7 fw-semibold text-muted">Tasts,
graphs & charts</span>
</span>
</a>
</div>
</div>
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/online-courses.html"
class="menu-link">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i
class="ki-duotone ki-color-swatch text-success fs-1">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
<span class="path5"></span>
<span class="path6"></span>
<span class="path7"></span>
<span class="path8"></span>
<span class="path9"></span>
<span class="path10"></span>
<span class="path11"></span>
<span class="path12"></span>
<span class="path13"></span>
<span class="path14"></span>
<span class="path15"></span>
<span class="path16"></span>
<span class="path17"></span>
<span class="path18"></span>
<span class="path19"></span>
<span class="path20"></span>
<span class="path21"></span>
</i>
</span>
<span class="d-flex flex-column">
<span class="fs-6 fw-bold text-gray-800">Online
Courses</span>
<span
class="fs-7 fw-semibold text-muted">Student
progress</span>
</span>
</a>
</div>
</div>
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/marketing.html"
class="menu-link">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i
class="ki-duotone ki-chart-simple text-dark fs-1">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
</i>
</span>
<span class="d-flex flex-column">
<span
class="fs-6 fw-bold text-gray-800">Marketing</span>
<span
class="fs-7 fw-semibold text-muted">Campaings
& conversions</span>
</span>
</a>
</div>
</div>
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/bidding.html"
class="menu-link">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i
class="ki-duotone ki-switch text-warning fs-1">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="d-flex flex-column">
<span
class="fs-6 fw-bold text-gray-800">Bidding</span>
<span
class="fs-7 fw-semibold text-muted">Campaings
& conversions</span>
</span>
</a>
</div>
</div>
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/pos.html" class="menu-link">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i
class="ki-duotone ki-abstract-42 text-danger fs-1">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="d-flex flex-column">
<span class="fs-6 fw-bold text-gray-800">POS
System</span>
<span
class="fs-7 fw-semibold text-muted">Campaings
& conversions</span>
</span>
</a>
</div>
</div>
<div class="col-lg-6 mb-3">
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/call-center.html"
class="menu-link">
<span
class="menu-custom-icon d-flex flex-center flex-shrink-0 rounded w-40px h-40px me-3">
<i class="ki-duotone ki-call text-primary fs-1">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
<span class="path5"></span>
<span class="path6"></span>
<span class="path7"></span>
<span class="path8"></span>
</i>
</span>
<span class="d-flex flex-column">
<span class="fs-6 fw-bold text-gray-800">Call
Center</span>
<span
class="fs-7 fw-semibold text-muted">Campaings
& conversions</span>
</span>
</a>
</div>
</div>
</div>
<div class="separator separator-dashed mx-5 my-5"></div>
<div class="d-flex flex-stack flex-wrap flex-lg-nowrap gap-2 mx-5">
<div class="d-flex flex-column me-5">
<div class="fs-6 fw-bold text-gray-800">Landing Page
Template</div>
<div class="fs-7 fw-semibold text-muted">Onpe page landing
template with pricing & others</div>
</div>
<a href="dist/landing.html"
class="btn btn-sm btn-primary fw-bold">Explore</a>
</div>
</div>
<div
class="menu-more bg-light col-lg-4 py-3 px-3 py-lg-6 px-lg-6 rounded-end">
<h4 class="fs-6 fs-lg-4 text-gray-800 fw-bold mt-3 mb-3 ms-4">More
Dashboards</h4>
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/logistics.html" class="menu-link py-2">
<span class="menu-title">Logistics</span>
</a>
</div>
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/website-analytics.html"
class="menu-link py-2">
<span class="menu-title">Website Analytics</span>
</a>
</div>
<div class="menu-item p-0 m-0">
<a href="dist/dashboards/finance-performance.html"
class="menu-link py-2">
<span class="menu-title">Finance Performance</span>
</a>
</div>

<div class="menu-item p-0 m-0">
<a href="dist/dashboards/store-analytics.html"
class="menu-link py-2">
<span class="menu-title">Store Analytics</span>
</a>
</div>

<div class="menu-item p-0 m-0">
<a href="dist/dashboards/social.html" class="menu-link py-2">
<span class="menu-title">Social</span>
</a>
</div>

<div class="menu-item p-0 m-0">

<a href="dist/dashboards/delivery.html" class="menu-link py-2">
<span class="menu-title">Delivery</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/dashboards/crypto.html" class="menu-link py-2">
<span class="menu-title">Crypto</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/dashboards/school.html" class="menu-link py-2">
<span class="menu-title">School</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/dashboards/podcast.html" class="menu-link py-2">
<span class="menu-title">Podcast</span>
</a>

</div>

</div>

</div>

</div>

</div> 
</div> -->

<!-- <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
data-kt-menu-placement="bottom-start"
class="menu-item menu-lg-down-accordion me-0 me-lg-2">

<span class="menu-link">
<span class="menu-title">Pages</span>
<span class="menu-arrow d-lg-none"></span>
</span>
<div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown p-0">

<div class="menu-active-bg px-4 px-lg-0">

<div class="d-flex w-100 overflow-auto">
<ul
class="nav nav-stretch nav-line-tabs fw-bold fs-6 p-0 p-lg-10 flex-nowrap flex-grow-1">

<li class="nav-item mx-lg-1">
<a class="nav-link py-3 py-lg-6 active text-active-primary"
href="#" data-bs-toggle="tab"
data-bs-target="#kt_app_header_menu_pages_pages">General</a>
</li>

<li class="nav-item mx-lg-1">
<a class="nav-link py-3 py-lg-6 text-active-primary" href="#"
data-bs-toggle="tab"
data-bs-target="#kt_app_header_menu_pages_account">Account</a>
</li>

<li class="nav-item mx-lg-1">
<a class="nav-link py-3 py-lg-6 text-active-primary" href="#"
data-bs-toggle="tab"
data-bs-target="#kt_app_header_menu_pages_authentication">Authentication</a>
</li>

<li class="nav-item mx-lg-1">
<a class="nav-link py-3 py-lg-6 text-active-primary" href="#"
data-bs-toggle="tab"
data-bs-target="#kt_app_header_menu_pages_utilities">Utilities</a>
</li>

<li class="nav-item mx-lg-1">
<a class="nav-link py-3 py-lg-6 text-active-primary" href="#"
data-bs-toggle="tab"
data-bs-target="#kt_app_header_menu_pages_widgets">Widgets</a>
</li>

</ul>
</div>

<div class="tab-content py-4 py-lg-8 px-lg-7">

<div class="tab-pane active w-lg-1000px"
id="kt_app_header_menu_pages_pages">

<div class="row">

<div class="col-lg-8">

<div class="row">

<div class="col-lg-3 mb-6 mb-lg-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">User
Profile</h4>


<div class="menu-item p-0 m-0">

<a href="dist/pages/user-profile/overview.html"
class="menu-link">
<span class="menu-title">Overview</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/user-profile/projects.html"
class="menu-link">
<span class="menu-title">Projects</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/user-profile/campaigns.html"
class="menu-link">
<span class="menu-title">Campaigns</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/user-profile/documents.html"
class="menu-link">
<span class="menu-title">Documents</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/user-profile/followers.html"
class="menu-link">
<span class="menu-title">Followers</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/user-profile/activity.html"
class="menu-link">
<span class="menu-title">Activity</span>
</a>

</div>

</div>

<div class="col-lg-3 mb-6 mb-lg-0">

<div class="mb-6">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
Corporate</h4>


<div class="menu-item p-0 m-0">

<a href="dist/pages/about.html"
class="menu-link">
<span class="menu-title">About</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/team.html"
class="menu-link">
<span class="menu-title">Our Team</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/contact.html"
class="menu-link">
<span class="menu-title">Contact
Us</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/licenses.html"
class="menu-link">
<span class="menu-title">Licenses</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/sitemap.html"
class="menu-link">
<span class="menu-title">Sitemap</span>
</a>

</div>

</div>


<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
Careers</h4>


<div class="menu-item p-0 m-0">

<a href="dist/pages/careers/list.html"
class="menu-link">
<span class="menu-title">Careers
List</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/careers/apply.html"
class="menu-link">
<span class="menu-title">Careers
Apply</span>
</a>

</div>

</div>

</div>

<div class="col-lg-3 mb-6 mb-lg-0">

<div class="mb-6">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">FAQ
</h4>


<div class="menu-item p-0 m-0">

<a href="dist/pages/faq/classic.html"
class="menu-link">
<span class="menu-title">FAQ
Classic</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/faq/extended.html"
class="menu-link">
<span class="menu-title">FAQ
Extended</span>
</a>

</div>

</div>


<div class="mb-6">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">Blog
</h4>


<div class="menu-item p-0 m-0">

<a href="dist/pages/blog/home.html"
class="menu-link">
<span class="menu-title">Blog
Home</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/blog/post.html"
class="menu-link">
<span class="menu-title">Blog
Post</span>
</a>

</div>

</div>


<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
Pricing</h4>


<div class="menu-item p-0 m-0">

<a href="dist/pages/pricing.html"
class="menu-link">
<span class="menu-title">Column
Pricing</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/pricing/table.html"
class="menu-link">
<span class="menu-title">Table
Pricing</span>
</a>

</div>

</div>

</div>

<div class="col-lg-3 mb-6 mb-lg-0">

<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
Social</h4>


<div class="menu-item p-0 m-0">

<a href="dist/pages/social/feeds.html"
class="menu-link">
<span class="menu-title">Feeds</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/social/activity.html"
class="menu-link">
<span class="menu-title">Activty</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/social/followers.html"
class="menu-link">
<span
class="menu-title">Followers</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/pages/social/settings.html"
class="menu-link">
<span class="menu-title">Settings</span>
</a>

</div>

</div>

</div>

</div>

</div>

<div class="col-lg-4">
<img src="dist/assets/media/stock/600x600/img-82.jpg"
class="rounded mw-100" alt="" />
</div>

</div>

</div>


<div class="tab-pane w-lg-600px" id="kt_app_header_menu_pages_account">

<div class="row">

<div class="col-lg-5 mb-6 mb-lg-0">

<div class="row">

<div class="col-lg-6">

<div class="menu-item p-0 m-0">

<a href="dist/account/overview.html"
class="menu-link">
<span class="menu-title">Overview</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/account/settings.html"
class="menu-link">
<span class="menu-title">Settings</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/account/security.html"
class="menu-link">
<span class="menu-title">Security</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/account/activity.html"
class="menu-link">
<span class="menu-title">Activity</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/account/billing.html"
class="menu-link">
<span class="menu-title">Billing</span>
</a>

</div>

</div>

<div class="col-lg-6">

<div class="menu-item p-0 m-0">

<a href="dist/account/statements.html"
class="menu-link">
<span class="menu-title">Statements</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/account/referrals.html"
class="menu-link">
<span class="menu-title">Referrals</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/account/api-keys.html"
class="menu-link">
<span class="menu-title">API Keys</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/account/logs.html"
class="menu-link">
<span class="menu-title">Logs</span>
</a>

</div>

</div>

</div>

</div>

<div class="col-lg-7">
<img src="dist/assets/media/stock/900x600/46.jpg"
class="rounded mw-100" alt="" />
</div>

</div>

</div>

<div class="tab-pane w-lg-1000px"
id="kt_app_header_menu_pages_authentication">

<div class="row">

<div class="col-lg-3 mb-6 mb-lg-0">

<div class="mb-6">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">Corporate Layout</h4>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/corporate/sign-in.html"
class="menu-link">
<span class="menu-title">Sign-In</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/corporate/sign-up.html"
class="menu-link">
<span class="menu-title">Sign-Up</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/corporate/two-factor.html"
class="menu-link">
<span class="menu-title">Two-Factor</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/corporate/reset-password.html"
class="menu-link">
<span class="menu-title">Reset Password</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/corporate/new-password.html"
class="menu-link">
<span class="menu-title">New Password</span>
</a>

</div>

</div>

<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">Overlay Layout</h4>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/overlay/sign-in.html"
class="menu-link">
<span class="menu-title">Sign-In</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/overlay/sign-up.html"
class="menu-link">
<span class="menu-title">Sign-Up</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/overlay/two-factor.html"
class="menu-link">
<span class="menu-title">Two-Factor</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/overlay/reset-password.html"
class="menu-link">
<span class="menu-title">Reset Password</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/overlay/new-password.html"
class="menu-link">
<span class="menu-title">New Password</span>
</a>

</div>

</div>

</div>

<div class="col-lg-3 mb-6 mb-lg-0">

<div class="mb-6">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">Creative Layout</h4>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/creative/sign-in.html"
class="menu-link">
<span class="menu-title">Sign-in</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/creative/sign-up.html"
class="menu-link">
<span class="menu-title">Sign-up</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/creative/two-factor.html"
class="menu-link">
<span class="menu-title">Two-Factor</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/creative/reset-password.html"
class="menu-link">
<span class="menu-title">Reset Password</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/creative/new-password.html"
class="menu-link">
<span class="menu-title">New Password</span>
</a>

</div>

</div>


<div class="mb-6">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">Fancy Layout</h4>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/fancy/sign-in.html"
class="menu-link">
<span class="menu-title">Sign-In</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/fancy/sign-up.html"
class="menu-link">
<span class="menu-title">Sign-Up</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/fancy/two-factor.html"
class="menu-link">
<span class="menu-title">Two-Factor</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/fancy/reset-password.html"
class="menu-link">
<span class="menu-title">Reset Password</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/layouts/fancy/new-password.html"
class="menu-link">
<span class="menu-title">New Password</span>
</a>

</div>

</div>

</div>

<div class="col-lg-3 mb-6 mb-lg-0">

<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">General</h4>


<div class="menu-item p-0 m-0">

<a href="dist/authentication/extended/multi-steps-sign-up.html"
class="menu-link">
<span class="menu-title">Multi-Steps
Sign-Up</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/general/welcome.html"
class="menu-link">
<span class="menu-title">--</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/general/verify-email.html"
class="menu-link">
<span class="menu-title">Verify Email</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/general/coming-soon.html"
class="menu-link">
<span class="menu-title">Coming Soon</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/general/password-confirmation.html"
class="menu-link">
<span class="menu-title">Password
Confirmation</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/general/account-deactivated.html"
class="menu-link">
<span class="menu-title">Account
Deactivation</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/general/error-404.html"
class="menu-link">
<span class="menu-title">Error 404</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/general/error-500.html"
class="menu-link">
<span class="menu-title">Error 500</span>
</a>

</div>

</div>

</div>

<div class="col-lg-3 mb-6 mb-lg-0">

<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">Email Templates</h4>


<div class="menu-item p-0 m-0">

<a href="dist/authentication/email/welcome-message.html"
class="menu-link">
<span class="menu-title">--</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/email/reset-password.html"
class="menu-link">
<span class="menu-title">Reset Password</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/email/subscription-confirmed.html"
class="menu-link">
<span class="menu-title">Subscription
Confirmed</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/email/card-declined.html"
class="menu-link">
<span class="menu-title">Credit Card
Declined</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/email/promo-1.html"
class="menu-link">
<span class="menu-title">Promo 1</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/email/promo-2.html"
class="menu-link">
<span class="menu-title">Promo 2</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/authentication/email/promo-3.html"
class="menu-link">
<span class="menu-title">Promo 3</span>
</a>

</div>

</div>

</div>

</div>

</div>

<div class="tab-pane w-lg-1000px"
id="kt_app_header_menu_pages_utilities">

<div class="row">

<div class="col-lg-7">

<div class="row">

<div class="col-lg-4 mb-6 mb-lg-0">

<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
General Modals</h4>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/general/invite-friends.html"
class="menu-link">
<span class="menu-title">Invite
Friends</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/general/view-users.html"
class="menu-link">
<span class="menu-title">View
Users</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/general/select-users.html"
class="menu-link">
<span class="menu-title">Select
Users</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/general/upgrade-plan.html"
class="menu-link">
<span class="menu-title">Upgrade
Plan</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/general/share-earn.html"
class="menu-link">
<span class="menu-title">Share &
Earn</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/forms/new-target.html"
class="menu-link">
<span class="menu-title">New
Target</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/forms/new-card.html"
class="menu-link">
<span class="menu-title">New Card</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/forms/new-address.html"
class="menu-link">
<span class="menu-title">New
Address</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/forms/create-api-key.html"
class="menu-link">
<span class="menu-title">Create API
Key</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/forms/bidding.html"
class="menu-link">
<span class="menu-title">Bidding</span>
</a>

</div>

</div>

</div>

<div class="col-lg-4 mb-6 mb-lg-0">

<div class="mb-6">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
Advanced Modals</h4>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/create-app.html"
class="menu-link">
<span class="menu-title">Create
App</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/create-campaign.html"
class="menu-link">
<span class="menu-title">Create
Campaign</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/create-account.html"
class="menu-link">
<span class="menu-title">Create Business
Acc</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/create-project.html"
class="menu-link">
<span class="menu-title">Create
Project</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/top-up-wallet.html"
class="menu-link">
<span class="menu-title">Top Up
Wallet</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/offer-a-deal.html"
class="menu-link">
<span class="menu-title">Offer a
Deal</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/two-factor-authentication.html"
class="menu-link">
<span class="menu-title">Two Factor
Auth</span>
</a>

</div>

</div>


<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
Search</h4>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/search/horizontal.html"
class="menu-link">
<span
class="menu-title">Horizontal</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/search/vertical.html"
class="menu-link">
<span class="menu-title">Vertical</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/search/users.html"
class="menu-link">
<span class="menu-title">Users</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/search/select-location.html"
class="menu-link">
<span class="menu-title">Select
Location</span>
</a>

</div>

</div>

</div>

<div class="col-lg-4 mb-6 mb-lg-0">

<div class="mb-0">

<h4 class="fs-6 fs-lg-4 fw-bold mb-3 ms-4">
Wizards</h4>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/horizontal.html"
class="menu-link">
<span
class="menu-title">Horizontal</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/vertical.html"
class="menu-link">
<span class="menu-title">Vertical</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/two-factor-authentication.html"
class="menu-link">
<span class="menu-title">Two Factor
Auth</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/create-app.html"
class="menu-link">
<span class="menu-title">Create
App</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/create-campaign.html"
class="menu-link">
<span class="menu-title">Create
Campaign</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/create-account.html"
class="menu-link">
<span class="menu-title">Create
Account</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/create-project.html"
class="menu-link">
<span class="menu-title">Create
Project</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/modals/wizards/top-up-wallet.html"
class="menu-link">
<span class="menu-title">Top Up
Wallet</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/utilities/wizards/offer-a-deal.html"
class="menu-link">
<span class="menu-title">Offer a
Deal</span>
</a>

</div>

</div>

</div>

</div>

</div>

<div class="col-lg-5 pe-lg-5">
<img src="dist/assets/media/stock/600x600/img-84.jpg"
class="rounded mw-100" alt="" />
</div>

</div>

</div>

<div class="tab-pane w-lg-500px" id="kt_app_header_menu_pages_widgets">

<div class="row">

<div class="col-lg-4 mb-6 mb-lg-0">

<div class="menu-item p-0 m-0">

<a href="dist/widgets/lists.html" class="menu-link">
<span class="menu-title">Lists</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/widgets/statistics.html"
class="menu-link">
<span class="menu-title">Statistics</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/widgets/charts.html" class="menu-link">
<span class="menu-title">Charts</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/widgets/mixed.html" class="menu-link">
<span class="menu-title">Mixed</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/widgets/tables.html" class="menu-link">
<span class="menu-title">Tables</span>
</a>

</div>

<div class="menu-item p-0 m-0">

<a href="dist/widgets/feeds.html" class="menu-link">
<span class="menu-title">Feeds</span>
</a>

</div>

</div>

<div class="col-lg-8">
<img src="dist/assets/media/stock/900x600/44.jpg"
class="rounded mw-100" alt="" />
</div>

</div>

</div>

</div>
</div>
</div>
</div> -->

<!-- <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
data-kt-menu-placement="bottom-start"
class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">

<span class="menu-link">
<span class="menu-title">Apps</span>
<span class="menu-arrow d-lg-none"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-250px">

<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-rocket fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Projects</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/list.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">My Projects</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/project.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">View Project</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/targets.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Targets</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/budget.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Budget</span>
</a>

</div>

<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/users.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Users</span>
</a>

</div>

<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/files.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Files</span>
</a>

</div>

<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/activity.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Activity</span>
</a>

</div>

<div class="menu-item">

<a class="menu-link" href="dist/apps/projects/settings.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Settings</span>
</a>

</div>

</div>

</div>

<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-handcart fs-2"></i>
</span>
<span class="menu-title">eCommerce</span>
<span class="menu-arrow"></span>
</span>

<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Catalog</span>
<span class="menu-arrow"></span>
</span>

<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/catalog/products.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Products</span>
</a>

</div>

<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/catalog/categories.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Categories</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/catalog/add-product.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Add Product</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/catalog/edit-product.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Edit Product</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/catalog/add-category.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Add Category</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/catalog/edit-category.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Edit Category</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="click"
class="menu-item menu-accordion menu-sub-indention">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Sales</span>
<span class="menu-arrow"></span>
</span>


<div class="menu-sub menu-sub-accordion">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/sales/listing.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Orders Listing</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/sales/details.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Order Details</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/sales/add-order.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Add Order</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/sales/edit-order.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Edit Order</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="click"
class="menu-item menu-accordion menu-sub-indention">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Customers</span>
<span class="menu-arrow"></span>
</span>


<div class="menu-sub menu-sub-accordion">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/customers/listing.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Customers Listing</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/customers/details.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Customers Details</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="click"
class="menu-item menu-accordion menu-sub-indention">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Reports</span>
<span class="menu-arrow"></span>
</span>


<div class="menu-sub menu-sub-accordion">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/reports/view.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Products Viewed</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/reports/sales.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Sales</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/reports/returns.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Returns</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/reports/customer-orders.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Customer Orders</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/ecommerce/reports/shipping.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Shipping</span>
</a>

</div>

</div>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/ecommerce/settings.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Settings</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-chart fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Support Center</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link" href="dist/apps/support-center/overview.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Overview</span>
</a>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Tickets</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/support-center/tickets/list.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Ticket List</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/support-center/tickets/view.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Ticket View</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Tutorials</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/support-center/tutorials/list.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Tutorials List</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/support-center/tutorials/post.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Tutorials Post</span>
</a>

</div>

</div>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/support-center/faq.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">FAQ</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/support-center/licenses.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Licenses</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/support-center/contact.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Contact Us</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-shield-tick fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">User Management</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Users</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/user-management/users/list.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Users List</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/user-management/users/view.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">View User</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Roles</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/user-management/roles/list.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Roles List</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/user-management/roles/view.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">View Roles</span>
</a>

</div>

</div>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/user-management/permissions.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Permissions</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-phone fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Contacts</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link" href="dist/apps/contacts/getting-started.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Getting Started</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/contacts/add-contact.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Add Contact</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/contacts/edit-contact.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Edit Contact</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/contacts/view-contact.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">View Contact</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-basket fs-2">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
</i>
</span>
<span class="menu-title">Subscriptions</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/subscriptions/getting-started.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Getting Started</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/subscriptions/list.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Subscription List</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/subscriptions/add.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Add Subscription</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/subscriptions/view.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">View Subscription</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-briefcase fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Customers</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/customers/getting-started.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Getting Started</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/customers/list.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Customer Listing</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/customers/view.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Customer Details</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-credit-cart fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Invoice Management</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Profile</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link"
href="dist/apps/invoices/view/invoice-1.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Invoice 1</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/invoices/view/invoice-2.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Invoice 2</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="dist/apps/invoices/view/invoice-3.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Invoice 3</span>
</a>

</div>

</div>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/invoices/create.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Create Invoice</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-file-added fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">File Manager</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link" href="dist/apps/file-manager/folders.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Folders</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/file-manager/files.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Files</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/file-manager/blank.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Blank Directory</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/file-manager/settings.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Settings</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-sms fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Inbox</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link" href="dist/apps/inbox/listing.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Messages</span>
<span class="menu-badge">
<span class="badge badge-light-success">3</span>
</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/inbox/compose.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Compose</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/inbox/reply.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">View & Reply</span>
</a>

</div>

</div>

</div>


<div data-kt-menu-trigger="{default:'click', lg: 'hover'}"
data-kt-menu-placement="right-start"
class="menu-item menu-lg-down-accordion">

<span class="menu-link">
<span class="menu-icon">
<i class="ki-duotone ki-message-text-2 fs-2">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
</i>
</span>
<span class="menu-title">Chat</span>
<span class="menu-arrow"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">

<div class="menu-item">

<a class="menu-link" href="dist/apps/chat/private.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Private Chat</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/chat/group.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Group Chat</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/chat/drawer.html">
<span class="menu-bullet">
<span class="bullet bullet-dot"></span>
</span>
<span class="menu-title">Drawer Chat</span>
</a>

</div>

</div>

</div>


<div class="menu-item">

<a class="menu-link" href="dist/apps/calendar.html">
<span class="menu-icon">
<i class="ki-duotone ki-calendar-8 fs-2">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
<span class="path5"></span>
<span class="path6"></span>
</i>
</span>
<span class="menu-title">Calendar</span>
</a>

</div>

</div>

</div> -->

<!-- <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
data-kt-menu-placement="bottom-start"
class="menu-item menu-lg-down-accordion me-0 me-lg-2">

<span class="menu-link">
<span class="menu-title">Layouts</span>
<span class="menu-arrow d-lg-none"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown p-0 w-100 w-lg-900px w-xxl-950px">

<div class="menu-active-bg pt-1 pb-3 px-3 py-lg-6 px-lg-6"
data-kt-menu-dismiss="true">

<div class="row">

<div class="col-lg-7">

<div class="row">

<div class="col-lg-4 mb-3">

<h4
class="fs-6 fs-lg-4 text-gray-800 fw-bold mt-3 mb-3 ms-4">
Layouts</h4>


<div class="menu-item p-0 m-0">

<a href="dist/layouts/light-sidebar.html"
class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Light Sidebar</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/layouts/dark-sidebar.html"
class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Dark Sidebar</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/layouts/light-header.html"
class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Light Header</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/layouts/dark-header.html"
class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Dark Header</span>
</a>

</div>

</div>


<div class="col-lg-4 mb-3">

<h4
class="fs-6 fs-lg-4 text-gray-800 fw-bold mt-3 mb-3 ms-4">
Toolbars</h4>


<div class="menu-item p-0 m-0">

<a href="dist/toolbars/classic.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Classic</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/toolbars/saas.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">SaaS</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/toolbars/accounting.html"
class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Accounting</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/toolbars/extended.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Extended</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/toolbars/reports.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Reports</span>
</a>

</div>

</div>


<div class="col-lg-4 mb-3">

<h4
class="fs-6 fs-lg-4 text-gray-800 fw-bold mt-3 mb-3 ms-4">
Asides</h4>


<div class="menu-item p-0 m-0">

<a href="dist/asides/aside-1.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Filters</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/asides/aside-2.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Segments</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/asides/aside-3.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Shipment History</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/asides/aside-4.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Courier Activity</span>
</a>

</div>


<div class="menu-item p-0 m-0">

<a href="dist/asides/aside-5.html" class="menu-link">
<span class="menu-bullet">
<span
class="bullet bullet-dot bg-gray-300i h-6px w-6px"></span>
</span>
<span class="menu-title">Calendar</span>
</a>

</div>

</div>

</div>

<div class="separator separator-dashed mx-lg-5 mt-2 mb-6"></div>

<div
class="d-flex flex-stack flex-wrap flex-lg-nowrap gap-2 mb-5 mb-lg-0 mx-lg-5">
<div class="d-flex flex-column me-5">
<div class="fs-6 fw-bold text-gray-800">Layout Builder</div>
<div class="fs-7 fw-semibold text-muted">Customize, preview
and export</div>
</div>
<a href="https://preview.keenthemes.com/metronic8/demo1/layout-builder.html"
class="btn btn-sm btn-primary fw-bold">Try Builder</a>
</div>

</div>

<div class="col-lg-5 mb-3 py-lg-3 pe-lg-8 d-flex align-items-center">
<img src="dist/assets/media/stock/900x600/45.jpg"
class="rounded mw-100" alt="" />
</div>

</div>

</div>

</div>

</div> -->

<!-- <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
data-kt-menu-placement="bottom-start"
class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">

<span class="menu-link">
<span class="menu-title">Help</span>
<span class="menu-arrow d-lg-none"></span>
</span>


<div
class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-200px">

<div class="menu-item">

<a class="menu-link"
href="https://preview.keenthemes.com/html/metronic/docs/base/utilities"
target="_blank"
title="Check out over 200 in-house components, plugins and ready for use solutions"
data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click"
data-bs-placement="right">
<span class="menu-icon">
<i class="ki-duotone ki-rocket fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Components</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="https://preview.keenthemes.com/html/metronic/docs" target="_blank"
title="Check out the complete documentation" data-bs-toggle="tooltip"
data-bs-trigger="hover" data-bs-dismiss="click"
data-bs-placement="right">
<span class="menu-icon">
<i class="ki-duotone ki-abstract-26 fs-2">
<span class="path1"></span>
<span class="path2"></span>
</i>
</span>
<span class="menu-title">Documentation</span>
</a>

</div>


<div class="menu-item">

<a class="menu-link"
href="https://preview.keenthemes.com/html/metronic/docs/getting-started/changelog"
target="_blank">
<span class="menu-icon">
<i class="ki-duotone ki-code fs-2">
<span class="path1"></span>
<span class="path2"></span>
<span class="path3"></span>
<span class="path4"></span>
</i>
</span>
<span class="menu-title">Changelog v8.2.0</span>
</a>

</div>

</div>

</div> -->
                            </div>
                        </div>
                        <div class="app-navbar flex-shrink-0">
                            <!-- arama alanı -->
                            <div class="app-navbar-item align-items-stretch ms-1 ms-md-4">
                                <div id="kt_header_search" class="header-search d-flex align-items-stretch"
                                    data-kt-search-keypress="true" data-kt-search-min-length="2"
                                    data-kt-search-enter="enter" data-kt-search-layout="menu"
                                    data-kt-menu-trigger="auto" data-kt-menu-overflow="false"
                                    data-kt-menu-permanent="true" data-kt-menu-placement="bottom-end">
                                    <div class="d-flex align-items-center" data-kt-search-element="toggle"
                                        id="kt_header_search_toggle">
                                        <div
                                            class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px">
                                            <i class="ki-duotone ki-magnifier fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <div data-kt-search-element="content"
                                        class="menu menu-sub menu-sub-dropdown p-7 w-325px w-md-375px">
                                        <div data-kt-search-element="wrapper">
                                            <form data-kt-search-element="form" class="w-100 position-relative mb-5"
                                                autocomplete="off">
                                                <i
                                                    class="ki-duotone ki-magnifier fs-2 text-gray-500 position-absolute top-50 translate-middle-y ms-0">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <input type="text"
                                                    class="search-input form-control form-control-flush ps-10"
                                                    name="search" value="" placeholder="Arama..."
                                                    data-kt-search-element="input" />
                                                <span
                                                    class="search-spinner position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-1"
                                                    data-kt-search-element="spinner">
                                                    <span
                                                        class="spinner-border h-15px w-15px align-middle text-gray-400"></span>
                                                </span>
                                            </form>
                                            
                                            <!-- Hızlı Bağlantılar -->
                                            <div class="mb-2">
                                                <div class="fs-7 text-muted fw-semibold mb-3">Hızlı Bağlantılar</div>
                                                <div class="d-flex flex-column gap-2">
                                                    <a href="crm/tenants.php" class="d-flex align-items-center text-gray-700 text-hover-primary py-2 fs-6">
                                                        <i class="ki-duotone ki-briefcase fs-4 me-3 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                                        Müşteri Listesi
                                                    </a>
                                                    <a href="dist/toolbars/db_control.php" class="d-flex align-items-center text-gray-700 text-hover-primary py-2 fs-6">
                                                        <i class="ki-duotone ki-shield-search fs-4 me-3 text-success"><span class="path1"></span><span class="path2"></span></i>
                                                        Veritabanı Kontrolü
                                                    </a>
                                                    <a href="settings/password_reset.php" class="d-flex align-items-center justify-content-between text-gray-700 text-hover-primary py-2 fs-6">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ki-duotone ki-key fs-4 me-3 text-danger"><span class="path1"></span><span class="path2"></span></i>
                                                            Şifre Talep Havuzu
                                                        </span>
                                                        <span class="badge badge-light-warning"><?= (int)$headerResetRequestPending ?></span>
                                                    </a>
                                                    <a href="logs/password_resets.php" class="d-flex align-items-center justify-content-between text-gray-700 text-hover-primary py-2 fs-6">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ki-duotone ki-shield-tick fs-4 me-3 text-success"><span class="path1"></span><span class="path2"></span></i>
                                                            Açık Sıfırlama Tokenları
                                                        </span>
                                                        <span class="badge badge-light-success"><?= (int)$headerResetTokenOpen ?></span>
                                                    </a>
                                                    <a href="logs/notification_queue.php" class="d-flex align-items-center justify-content-between text-gray-700 text-hover-primary py-2 fs-6">
                                                        <span class="d-flex align-items-center">
                                                            <i class="ki-duotone ki-sms fs-4 me-3 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                                            Mail Kuyruğu (Reset)
                                                        </span>
                                                        <span class="badge badge-light-primary"><?= (int)$headerResetQueuePending ?></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Destek Talepleri -->
                            <div class="app-navbar-item ms-1 ms-md-4">
                                <a href="crm/tickets.php" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px position-relative" title="Destek Talepleri">
                                    <i class="ki-duotone ki-message-text-2 fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <span class="badge badge-circle badge-warning position-absolute top-0 start-100 translate-middle"
                                          style="<?= $headerOpenTickets > 0 ? 'display: inline-flex;' : 'display: none;' ?>"><?= $headerOpenTickets ?></span>
                                </a>
                            </div>

                            <!-- Notification Dropdown -->
                            <div class="app-navbar-item ms-1 ms-md-4">
                                <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px position-relative"
                                     data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                     data-kt-menu-attach="parent"
                                     data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-notification-on fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <span class="badge badge-circle badge-danger position-absolute top-0 start-100 translate-middle" 
                                          id="notification-badge" 
                                          style="<?= $headerUnreadCount > 0 ? 'display: inline-flex;' : 'display: none;' ?>"><?= $headerUnreadCount ?></span>
                                </div>
                                
                                <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true">
                                    <div class="d-flex flex-column bgi-no-repeat rounded-top" 
                                         style="background-image:url('dist/assets/media/misc/menu-header-bg.jpg')">
                                        <h3 class="text-white fw-semibold px-9 mt-10 mb-6">
                                            Bildirimler
                                            <span class="fs-8 opacity-75 ps-3" id="notification-count-text"><?= $headerUnreadCount ?> yeni</span>
                                        </h3>
                                        <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-semibold px-9">
                                            <li class="nav-item">
                                                <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" data-bs-toggle="tab" href="#kt_topbar_notifications_1">Tümü</a>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="kt_topbar_notifications_1" role="tabpanel">
                                            <div class="scroll-y mh-325px my-5 px-8" id="notification-list">
                                                <?php if ($headerUnreadCount === 0): ?>
                                                    <div class="text-center text-muted py-8" id="notification-empty">Yeni bildirim yok.</div>
                                                <?php else: ?>
                                                    <?php
                                                        usort($headerNotifications, function ($a, $b) {
                                                            return strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? ''));
                                                        });
                                                    ?>
                                                    <?php foreach ($headerNotifications as $notif): ?>
                                                        <?php
                                                            $notifType = $notif['type'] ?? 'info';
                                                            $notifBadgeClass = 'badge-light-primary';
                                                            if ($notifType === 'warning') {
                                                                $notifBadgeClass = 'badge-light-warning';
                                                            } elseif ($notifType === 'error') {
                                                                $notifBadgeClass = 'badge-light-danger';
                                                            } elseif ($notifType === 'success') {
                                                                $notifBadgeClass = 'badge-light-success';
                                                            }
                                                        ?>
                                                        <?php $notifCanli = (int)($notif['notification_id'] ?? 0) === 0; ?>
                                                        <div class="d-flex flex-column border-bottom border-gray-200 py-4" data-notification-id="<?= (int)($notif['notification_id'] ?? 0) ?>">
                                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                                <span class="fw-bold text-gray-800"><?= htmlspecialchars((string)$notif['title']) ?></span>
                                                                <span class="badge <?= $notifBadgeClass ?>"><?= htmlspecialchars(['info' => 'Bilgi', 'warning' => 'Uyarı', 'error' => 'Hata', 'success' => 'Başarılı'][$notifType] ?? (string)$notifType) ?></span>
                                                            </div>
                                                            <span class="text-muted fs-7 mb-2"><?= htmlspecialchars((string)$notif['message']) ?></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <span class="text-muted fs-8"><?= date('d.m.Y H:i', strtotime((string)$notif['created_at'])) ?></span>
                                                                <?php if ($notifCanli): ?>
                                                                    <a href="<?= htmlspecialchars((string)($notif['action_url'] ?? '#')) ?>" class="btn btn-sm btn-light-primary py-1 px-2">Görüntüle</a>
                                                                <?php else: ?>
                                                                    <span class="d-flex gap-2">
                                                                        <?php if (!empty($notif['action_url'])): ?><a href="<?= htmlspecialchars((string)$notif['action_url']) ?>" class="btn btn-sm btn-light py-1 px-2">Görüntüle</a><?php endif; ?>
                                                                        <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 mark-notification-read-btn">Okundu</button>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <div class="text-center text-muted py-8" id="notification-empty" style="display: none;">Yeni bildirim yok.</div>
                                            </div>
                                            
                                            <div class="py-3 text-center border-top">
                                                <a href="#" class="btn btn-color-gray-600 btn-active-color-primary" id="mark-all-read-btn">
                                                    Tümünü Okundu İşaretle
                                                    <i class="ki-duotone ki-arrow-right fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                            (function () {
                                const badge = document.getElementById('notification-badge');
                                const countText = document.getElementById('notification-count-text');
                                const list = document.getElementById('notification-list');
                                const empty = document.getElementById('notification-empty');
                                const markAllBtn = document.getElementById('mark-all-read-btn');
                                if (!badge || !countText || !list || !markAllBtn) return;

                                function escapeHtml(text) {
                                    const div = document.createElement('div');
                                    div.textContent = text == null ? '' : String(text);
                                    return div.innerHTML;
                                }

                                function typeBadgeClass(type) {
                                    if (type === 'warning') return 'badge-light-warning';
                                    if (type === 'error') return 'badge-light-danger';
                                    if (type === 'success') return 'badge-light-success';
                                    return 'badge-light-primary';
                                }

                                function formatDate(value) {
                                    const date = new Date(value);
                                    if (Number.isNaN(date.getTime())) return '';
                                    const pad = (n) => String(n).padStart(2, '0');
                                    return pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear() + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
                                }

                                function updateBadge(count) {
                                    if (count > 0) {
                                        badge.style.display = 'inline-flex';
                                        badge.textContent = String(count);
                                    } else {
                                        badge.style.display = 'none';
                                        badge.textContent = '0';
                                    }
                                    countText.textContent = String(count) + ' yeni';
                                    if (empty) {
                                        empty.style.display = count > 0 ? 'none' : 'block';
                                    }
                                }

                                function renderNotifications(items) {
                                    const rows = items.map((item) => {
                                        const type = item.type || 'info';
                                        const canli = Number(item.notification_id || 0) === 0;
                                        const actionHtml = canli
                                            ? '<a href="' + escapeHtml(item.action_url || '#') + '" class="btn btn-sm btn-light-primary py-1 px-2">Görüntüle</a>'
                                            : '<button type="button" class="btn btn-sm btn-light-primary py-1 px-2 mark-notification-read-btn">Okundu</button>';
                                        return ''
                                            + '<div class="d-flex flex-column border-bottom border-gray-200 py-4" data-notification-id="' + Number(item.notification_id || 0) + '">'
                                            + '  <div class="d-flex align-items-center justify-content-between mb-1">'
                                            + '    <span class="fw-bold text-gray-800">' + escapeHtml(item.title || '') + '</span>'
                                            + '    <span class="badge ' + typeBadgeClass(type) + '">' + escapeHtml(String(type).toUpperCase()) + '</span>'
                                            + '  </div>'
                                            + '  <span class="text-muted fs-7 mb-2">' + escapeHtml(item.message || '') + '</span>'
                                            + '  <div class="d-flex align-items-center justify-content-between">'
                                            + '    <span class="text-muted fs-8">' + escapeHtml(formatDate(item.created_at)) + '</span>'
                                            + '    ' + actionHtml
                                            + '  </div>'
                                            + '</div>';
                                    });

                                    list.querySelectorAll('[data-notification-id]').forEach((el) => el.remove());
                                    if (rows.length > 0) {
                                        list.insertAdjacentHTML('afterbegin', rows.join(''));
                                    }
                                    updateBadge(rows.length);
                                }

                                async function fetchNotifications() {
                                    try {
                                        const response = await fetch('dist/api/notifications_api.php?action=get_unread', {
                                            method: 'GET',
                                            credentials: 'same-origin'
                                        });
                                        if (!response.ok) return;
                                        const data = await response.json();
                                        if (Array.isArray(data)) {
                                            renderNotifications(data);
                                        }
                                    } catch (e) {
                                        // Sessiz gec
                                    }
                                }

                                async function markNotificationRead(notificationId) {
                                    const form = new URLSearchParams();
                                    form.append('action', 'mark_read');
                                    form.append('notification_id', String(notificationId));

                                    const response = await fetch('dist/api/notifications_api.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                                        },
                                        credentials: 'same-origin',
                                        body: form.toString()
                                    });

                                    if (!response.ok) return false;
                                    const data = await response.json();
                                    return !!(data && data.success);
                                }

                                list.addEventListener('click', async function (event) {
                                    const btn = event.target.closest('.mark-notification-read-btn');
                                    if (!btn) return;
                                    const row = btn.closest('[data-notification-id]');
                                    if (!row) return;

                                    const id = Number(row.getAttribute('data-notification-id') || '0');
                                    if (!id) return;

                                    btn.disabled = true;
                                    const ok = await markNotificationRead(id);
                                    if (ok) {
                                        row.remove();
                                        updateBadge(list.querySelectorAll('[data-notification-id]').length);
                                    } else {
                                        btn.disabled = false;
                                    }
                                });

                                markAllBtn.addEventListener('click', async function (event) {
                                    event.preventDefault();
                                    const ids = Array.from(list.querySelectorAll('[data-notification-id]'))
                                        .map((el) => Number(el.getAttribute('data-notification-id') || '0'))
                                        .filter((n) => n > 0);

                                    if (ids.length === 0) {
                                        updateBadge(0);
                                        return;
                                    }

                                    markAllBtn.classList.add('disabled');
                                    await Promise.all(ids.map((id) => markNotificationRead(id)));
                                    list.querySelectorAll('[data-notification-id]').forEach((el) => el.remove());
                                    updateBadge(0);
                                    markAllBtn.classList.remove('disabled');
                                });

                                setInterval(fetchNotifications, 30000);
                            })();
                            </script>

                            <!-- kullanıcı menüsü  -->
                            <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                                <div class="cursor-pointer symbol symbol-35px"
                                    data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                                    data-kt-menu-placement="bottom-end">
                                    <?php if (!empty($_SESSION['admin_pic'])): ?>
                                        <img src="<?php echo htmlspecialchars($_SESSION['admin_pic'], ENT_QUOTES, 'UTF-8'); ?>" class="rounded-3" alt="user" />
                                    <?php else: ?>
                                        <div class="symbol-label fs-3 fw-bold bg-light-primary text-primary">
                                            <?= mb_substr($_SESSION['full_name'] ?? 'A', 0, 1, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                    data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <div class="symbol symbol-50px me-5">
                                                <?php if (!empty($_SESSION['admin_pic'])): ?>
                                                    <img alt="Logo" src="<?php echo htmlspecialchars($_SESSION['admin_pic'], ENT_QUOTES, 'UTF-8'); ?>" />
                                                <?php else: ?>
                                                    <div class="symbol-label fs-2 fw-bold bg-light-primary text-primary">
                                                        <?= mb_substr($_SESSION['full_name'] ?? 'A', 0, 1, 'UTF-8') ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold d-flex align-items-center fs-5"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Yönetici', ENT_QUOTES, 'UTF-8'); ?></div>
                                                <a href="#" class="fw-semibold text-muted text-hover-primary fs-7"><?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@prolyn.net', ENT_QUOTES, 'UTF-8'); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-5">
                                        <a href="dist/account/overview.php" class="menu-link px-5">Profilim</a>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                        data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                                        <a href="#" class="menu-link px-5">
                                            <span class="menu-title position-relative">Mode
                                                <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                                    <i class="ki-duotone ki-night-day theme-light-show fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                        <span class="path6"></span>
                                                        <span class="path7"></span>
                                                        <span class="path8"></span>
                                                        <span class="path9"></span>
                                                        <span class="path10"></span>
                                                    </i>
                                                    <i class="ki-duotone ki-moon theme-dark-show fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </span></span>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                            data-kt-menu="true" data-kt-element="theme-mode-menu">
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                                    data-kt-value="light">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-duotone ki-night-day fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                            <span class="path5"></span>
                                                            <span class="path6"></span>
                                                            <span class="path7"></span>
                                                            <span class="path8"></span>
                                                            <span class="path9"></span>
                                                            <span class="path10"></span>
                                                        </i>
                                                    </span>
                                                    <span class="menu-title">Light</span>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                                    data-kt-value="dark">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-duotone ki-moon fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </span>
                                                    <span class="menu-title">Dark</span>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                                    data-kt-value="system">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-duotone ki-screen fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                        </i>
                                                    </span>
                                                    <span class="menu-title">System</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="menu-item px-5 my-1">
                                        <a href="#" class="menu-link px-5">Ayarlar</a>
                                    </div>
                                    <div class="menu-item px-5">
                                        <a href="logout.php" class="menu-link px-5">Çıkış Yap</a>
                                    </div>
                                </div>
                            </div>
                            <div class="app-navbar-item d-lg-none ms-2 me-n2" title="Show header menu">
                                <div class="btn btn-flex btn-icon btn-active-color-primary w-30px h-30px"
                                    id="kt_app_header_menu_toggle">
                                    <i class="ki-duotone ki-element-4 fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true"
                    data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}"
                    data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start"
                    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
                    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
                        <a href="index.php">
                            <img alt="Logo" src="dist/assets/media/logos/default-dark.svg"
                                class="h-25px app-sidebar-logo-default" />
                            <img alt="Logo" src="dist/assets/media/logos/default-small.svg"
                                class="h-20px app-sidebar-logo-minimize" />
                        </a>

                        <div id="kt_app_sidebar_toggle"
                            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
                            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
                            data-kt-toggle-name="app-sidebar-minimize">
                            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>