<?php
/**
 * Prolyn Admin Panel - Dashboard
 * prolynweb/p/index.php
 */

// Header ve sidebar dahil et
require_once 'config/header.php';
require_once 'config/sidebar.php';

// Kullanıcı adını çek (yoneticiler tablosundan)
$admin_id = $_SESSION['admin_id'] ?? 0;
$adminRow = $db->getRow("SELECT username FROM yoneticiler WHERE admin_id = ?", [$admin_id]);
$username = $adminRow ? $adminRow->username : 'Yönetici';

?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar (Boş bırakıyoruz) -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <div class="card shadow-sm border-0">
                    <div class="card-body p-12 text-center">
                        <i class="ki-duotone ki-user fs-5x text-primary mb-5"><span class="path1"></span><span class="path2"></span></i>
                        <h1 class="fw-bolder text-dark mb-3" style="font-size: 2.5rem;">Hoş geldiniz, <?= htmlspecialchars($username) ?></h1>
                        <p class="text-muted fs-4">Prolyn Master Admin Paneli'ne giriş yaptınız.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<?php require_once 'config/footer.php'; ?>
