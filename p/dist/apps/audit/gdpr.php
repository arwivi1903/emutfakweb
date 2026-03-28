<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                 <div class="card card-flush">
                    <div class="card-header pt-7">
                        <div class="card-title"><h3>GDPR ve Veri Uyumluluğu</h3></div>
                    </div>
                    <div class="card-body">
                         <div class="alert alert-primary">
                            <i class="ki-duotone ki-file-sheet fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span></i>
                            Bu bölüm veri silme talepleri ve veri taşınabilirliği raporları içindir.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
