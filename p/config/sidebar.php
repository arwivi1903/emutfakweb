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

            </div>
        </div>
    </div>
</div></div>
