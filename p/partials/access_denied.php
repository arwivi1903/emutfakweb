<?php
require_once __DIR__ . '/../config/header.php';
?>
<!--begin::Main-->
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card card-flush text-center p-10">
                    <div class="card-body py-15 py-lg-20">
                        <h1 class="fw-bolder fs-2hx text-gray-900 mb-4">
                            Erişim Reddedildi
                        </h1>
                        <div class="fw-semibold fs-6 text-gray-500 mb-7">
                            Bu sayfayı görüntülemek için yeterli yetkiniz bulunmuyor. Lütfen sistem yöneticinizle iletişime geçin.
                        </div>
                        <div class="mb-3">
                            <img src="assets/media/auth/404-error.png" class="mw-100 mh-300px theme-light-show" alt=""/>
                            <img src="assets/media/auth/404-error-dark.png" class="mw-100 mh-300px theme-dark-show" alt=""/>
                        </div>
                        <div class="mb-0">
                            <a href="index.php" class="btn btn-sm btn-primary">Ana Sayfaya Dön</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Main-->
<?php require_once __DIR__ . '/../config/footer.php'; ?>
