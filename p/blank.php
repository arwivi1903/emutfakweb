<?php 
require_once 'config/header.php'; 
require_once 'config/sidebar.php'; 
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- ==================== İÇERİK BURAYA ==================== -->

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Başlık</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Açıklama</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-primary">
                                <i class="ki-duotone ki-plus fs-2"></i>
                                Yeni Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        
                        <!-- İçerik buraya -->
                        
                    </div>
                </div>

                <!-- ==================== İÇERİK SONU ==================== -->

            </div>
        </div>
    </div>
    <?php 
require_once 'config/footer.php'; 
?>
</div>
