<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
            data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">
                
                <div data-kt-menu-trigger="click" class="menu-item here show menu-accordion">
                    
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-element-11 fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                        </span>
                        <span class="menu-title">Dashboards</span>
                        <span class="menu-arrow"></span>
                    </span>
                    
                    
                    <div class="menu-sub menu-sub-accordion">
                        
                        <div class="menu-item">
                            <a class="menu-link active" href="index.php">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Default</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="dist/account/customers.php">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Müşteriler</span>
                            </a>
                        </div>

                        <div class="menu-inner flex-column collapse" id="kt_app_sidebar_menu_dashboards_collapse">
                            
                            <div class="menu-item">
                                <a class="menu-link" href="dist/dashboards/bidding.html">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Bidding</span>
                                </a>
                            </div>
                        </div>

                        <div class="menu-item">
                            <div class="menu-content">
                                <a class="btn btn-flex btn-color-primary d-flex flex-stack fs-base p-0 ms-2 mb-2 toggle collapsible collapsed"
                                    data-bs-toggle="collapse" href="#kt_app_sidebar_menu_dashboards_collapse"
                                    data-kt-toggle-text="Gizle">
                                    <span data-kt-toggle-text-target="true">Diğer Sayfalar</span>
                                    <i class="ki-duotone ki-minus-square toggle-on fs-2 me-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <i class="ki-duotone ki-plus-square toggle-off fs-2 me-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Sayfalar</span>
                    </div>
                </div>           
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-address-book fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                        <span class="menu-title">User Profile</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link" href="dist/account/overview.php">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Overview</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="dist/toolbars/userslist.php">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Users</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="dist/toolbars/db_control.php">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">DB Kontrol</span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
    <a href="https://www.emutfak.net/docs"
        target="_blank"
        class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100"
        data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click"
        title="Tüm dokümanlar ve bileşenler için tıklayın">
        <span class="btn-label">Dokümanlar & Bileşenler</span>
        <i class="ki-duotone ki-document btn-icon fs-2 m-0">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
</div>
</div>