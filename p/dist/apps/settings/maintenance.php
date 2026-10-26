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
                        <div class="card-title"><h3>Bakım Modu</h3></div>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="" id="maintenanceMode" />
                            <label class="form-check-label" for="maintenanceMode">
                                Bakım Modunu Aktifleştir
                            </label>
                        </div>
                        <div class="mt-5 text-muted">
                            Aktif edildiğinde sadece Admin kullanıcıları sisteme erişebilir.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
