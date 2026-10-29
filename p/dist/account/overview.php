<?php
require_once __DIR__ . '/../../config/header.php';
require_once __DIR__ . '/../../config/sidebar.php';

$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <?php require_once __DIR__ . '/account_header.php'; ?>

                <?php
                // account_header.php kullanıcı adını yükler; özet için buradan okunur
                $ozet = [
                    ['Tam Adı', $_SESSION['full_name'] ?? '-'],
                    ['Kullanıcı Adı', ($_SESSION['username'] ?? '') !== '' ? '@' . $_SESSION['username'] : 'Henüz belirlenmedi'],
                    ['E-posta', $_SESSION['email'] ?? '-'],
                    ['Rol', ucfirst((string)($_SESSION['role'] ?? 'Admin'))],
                ];
                $aktif = (($_SESSION['status'] ?? '') === 'active');
                ?>

                <div class="row g-5 g-xl-8">
                    <!-- Profil bilgileri -->
                    <div class="col-xl-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="card-title"><h3 class="fw-bold m-0">Profil Detayları</h3></div>
                                <div class="card-toolbar">
                                    <a href="dist/account/settings.php" class="btn btn-sm btn-primary">Profili Düzenle</a>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <?php foreach ($ozet as $i => [$etiket, $deger]): ?>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center py-4 <?= $i > 0 ? 'border-top border-gray-200' : '' ?>">
                                        <span class="text-muted fw-semibold"><?= $e($etiket) ?></span>
                                        <span class="fw-bold text-gray-800 fs-6"><?= $e($deger) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Hesap durumu -->
                    <div class="col-xl-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="card-title"><h3 class="fw-bold m-0">Hesap Durumu</h3></div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="d-flex align-items-center py-4">
                                    <span class="symbol symbol-45px me-4">
                                        <span class="symbol-label <?= $aktif ? 'bg-light-success' : 'bg-light-danger' ?>">
                                            <i class="ki-duotone ki-shield-tick fs-2x <?= $aktif ? 'text-success' : 'text-danger' ?>"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </span>
                                    <div>
                                        <div class="fw-bold fs-6 text-gray-800"><?= $aktif ? 'Hesabınız aktif' : 'Hesabınız pasif' ?></div>
                                        <div class="text-muted fs-7">Yönetici paneline erişim <?= $aktif ? 'açık' : 'kapalı' ?></div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center py-4 border-top border-gray-200">
                                    <span class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="ki-duotone ki-time fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </span>
                                    <div>
                                        <div class="fw-bold fs-6 text-gray-800"><?= !empty($_SESSION['last_login']) ? $e(tr_datetime($_SESSION['last_login'])) : '-' ?></div>
                                        <div class="text-muted fs-7">Son giriş zamanı</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center py-4 border-top border-gray-200">
                                    <span class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-info">
                                            <i class="ki-duotone ki-geolocation fs-2x text-info"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </span>
                                    <div>
                                        <div class="fw-bold fs-6 text-gray-800"><?= $e($_SESSION['last_ip'] ?? 'Bilinmiyor') ?></div>
                                        <div class="text-muted fs-7">Son giriş IP adresi</div>
                                    </div>
                                </div>
                                <div class="text-muted fs-8 mt-4">Tanımadığınız bir giriş görürseniz <a href="dist/account/logs.php">Kayıtlar</a> sekmesinden inceleyin.</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php require_once __DIR__ . '/../../config/footer.php'; ?>
