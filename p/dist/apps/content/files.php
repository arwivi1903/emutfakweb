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
                        <div class="card-title"><h3>Dosya Yöneticisi</h3></div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            Bu modül için dosya sistemi entegrasyonu (S3/Local) gereklidir. Şimdilik pasif.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
