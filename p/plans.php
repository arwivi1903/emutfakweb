<?php
/**
 * Prolyn Admin Panel - Packages & Modules Matrix
 * Prolyn/p/plans.php
 */

require_once 'config/header.php';
require_once 'config/sidebar.php';

/**
 * Modül listesi.
 * Öncelik app_modules tablosundadır; tablo yoksa/boşsa aşağıdaki liste kullanılır.
 * DİKKAT: Bu liste prolyn/classes/Auth.class.php içindeki $allModules ile birebir
 * aynı olmalıdır. Burada eksik kalan bir anahtar matriste hiç gösterilemez ve
 * matris kaydedildiğinde pakete ait o modül sessizce silinir.
 */
$allModules = [
    'core'            => 'Sistem ve Kullanıcı Yönetimi',
    'customer'        => 'Müşteri Yönetimi',
    'commercial'      => 'Ticari Müşteri Takip',
    'appointment'     => 'Randevu ve Takip',
    'current_account' => 'Cari Takip',
    'inventory'       => 'Stok ve Ürün',
    'accounting'      => 'Ön Muhasebe / Finans',
    'invoice'         => 'Fatura',
    'contract'        => 'Sözleşme',
    'reports'         => 'Raporlar ve İstatistik',
    'planning'        => 'Planlama ve İmalat',
    'bank'            => 'Banka İşlemleri',
    'statistics'      => 'İstatistik ve Analiz'
];

try {
    $dbModules = $db->getRowsAssoc("SELECT module_key, module_name FROM app_modules WHERE status = 1 ORDER BY id ASC");
    if (!empty($dbModules)) {
        $tmp = [];
        foreach ($dbModules as $m) {
            $tmp[$m['module_key']] = $m['module_name'];
        }
        $allModules = $tmp;
    }
} catch (Throwable $e) {
    // app_modules yoksa statik liste kullanilir
}

/**
 * Sema guvencesi: paket limitleri icin gereken kolonlar yoksa eklenir.
 * (max_branches sonradan eklendi; eski kurulumlarda bulunmuyor.)
 * Kolon eksikken UPDATE sorgusunun tamami patliyor ve fiyat/modul de
 * yazilamiyordu — bu yuzden kolon kontrolu KAYITTAN ONCE yapilir.
 */
$semaUyarilari = [];
foreach ([
    'max_total_users' => "INT NOT NULL DEFAULT 0 COMMENT '0 = sinirsiz'",
    'max_branches'    => "INT NOT NULL DEFAULT 0 COMMENT '0 = sinirsiz'",
    'yillik_indirim'  => "INT NOT NULL DEFAULT 10 COMMENT 'Yillik odemede uygulanacak indirim yuzdesi'",
] as $kolon => $tanim) {
    try {
        $var = $db->getRowsAssoc("SHOW COLUMNS FROM paket_ozellikleri LIKE '{$kolon}'");
        if (empty($var)) {
            $db->Update("ALTER TABLE paket_ozellikleri ADD COLUMN `{$kolon}` {$tanim}");
            $kontrol = $db->getRowsAssoc("SHOW COLUMNS FROM paket_ozellikleri LIKE '{$kolon}'");
            if (empty($kontrol)) {
                $semaUyarilari[] = "`{$kolon}` kolonu eklenemedi — bu alan kaydedilemez.";
            }
        }
    } catch (Throwable $e) {
        $semaUyarilari[] = "`{$kolon}` kolonu kontrol edilemedi: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_package') {
        checkCSRF();
        $plan_name = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['plan_name']));
        $plan_display_name = trim($_POST['plan_display_name']);
        $price = (float)$_POST['price'];
        
        if ($plan_name && $plan_display_name) {
            $db->Insert("INSERT IGNORE INTO paket_ozellikleri (plan_name, plan_display_name, price, allowed_modules) VALUES (?, ?, ?, '[]')", [$plan_name, $plan_display_name, $price]);
            echo "<script>window.location.href='plans.php?success=added';</script>";
            exit;
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_matrix') {
        checkCSRF();

        $prices       = $_POST['price'] ?? [];
        $max_users    = $_POST['max_users'] ?? [];
        $max_branches = $_POST['max_branches'] ?? [];
        $yil_indirim  = $_POST['yillik_indirim'] ?? [];
        $modules      = $_POST['modules'] ?? [];

        /**
         * NOT: $db->Update() istisnalari yutup 0 dondurur. Eskiden bu blok
         * sonucu hic kontrol etmeden "Basarili" mesajina yonlendiriyordu;
         * bir kolon eksikse veya sorgu patlasa bile kullanici basarili
         * oldugunu saniyordu. Artik YAZDIKTAN SONRA GERI OKUYUP dogruluyoruz.
         */
        $istenen = [];
        $all_plans = $db->getRowsAssoc("SELECT plan_name FROM paket_ozellikleri");
        foreach($all_plans as $p) {
            $p_name   = $p['plan_name'];
            $p_price  = (float)($prices[$p_name] ?? 0);
            $p_max    = (int)($max_users[$p_name] ?? 0);
            $p_branch = (int)($max_branches[$p_name] ?? 0);
            $p_yil    = (int)($yil_indirim[$p_name] ?? 0);
            if ($p_max < 0)    { $p_max = 0; }
            if ($p_branch < 0) { $p_branch = 0; }
            if ($p_yil < 0)    { $p_yil = 0; }
            if ($p_yil > 100)  { $p_yil = 100; }
            $p_mods  = $modules[$p_name] ?? [];
            if (!is_array($p_mods)) { $p_mods = []; }
            $json_mods = json_encode(array_values($p_mods), JSON_UNESCAPED_UNICODE);

            $istenen[$p_name] = [
                'price'    => $p_price,
                'max'      => $p_max,
                'branches' => $p_branch,
                'yil'      => $p_yil,
                'modules'  => $json_mods,
            ];

            $db->Update(
                "UPDATE paket_ozellikleri SET price = ?, max_total_users = ?, max_branches = ?, yillik_indirim = ?, allowed_modules = ? WHERE plan_name = ?",
                [$p_price, $p_max, $p_branch, $p_yil, $json_mods, $p_name]
            );
        }

        // --- Dogrulama: gercekten yazildi mi? ---
        $hatalar = [];
        $sonrasi = $db->getRowsAssoc("SELECT plan_name, price, max_total_users, max_branches, yillik_indirim, allowed_modules FROM paket_ozellikleri");
        foreach ($sonrasi as $row) {
            $ad = $row['plan_name'];
            if (!isset($istenen[$ad])) { continue; }
            $bekl = $istenen[$ad];

            if (abs((float)$row['price'] - $bekl['price']) > 0.001) {
                $hatalar[] = "{$ad}: fiyat yazilamadi (istenen {$bekl['price']}, kayitli {$row['price']})";
            }
            if ((int)$row['max_total_users'] !== $bekl['max']) {
                $hatalar[] = "{$ad}: maksimum kullanici yazilamadi (istenen {$bekl['max']}, kayitli {$row['max_total_users']})";
            }
            if ((int)$row['max_branches'] !== $bekl['branches']) {
                $hatalar[] = "{$ad}: maksimum sube yazilamadi (istenen {$bekl['branches']}, kayitli {$row['max_branches']})";
            }
            if ((int)$row['yillik_indirim'] !== $bekl['yil']) {
                $hatalar[] = "{$ad}: yillik indirim yazilamadi (istenen {$bekl['yil']}, kayitli {$row['yillik_indirim']})";
            }
            $kayitli = json_decode((string)$row['allowed_modules'], true);
            $beklMod = json_decode($bekl['modules'], true);
            if (!is_array($kayitli)) { $kayitli = []; }
            if (!is_array($beklMod)) { $beklMod = []; }
            sort($kayitli); sort($beklMod);
            if ($kayitli !== $beklMod) {
                $hatalar[] = "{$ad}: modul listesi yazilamadi";
            }
        }

        if ($hatalar) {
            $_SESSION['plans_error'] = $hatalar;
            $son = $db->getLastError();
            if (!empty($son)) {
                $_SESSION['plans_error'][] = 'Veritabani: ' . (is_string($son) ? $son : json_encode($son));
            }
            // NOT: config/header.php zaten cikti bastigi icin header('Location: ...')
            // kullanilamaz ("headers already sent"). Yonlendirme JS ile yapilir.
            echo "<script>window.location.href='plans.php?success=partial';</script>";
            exit;
        }

        echo "<script>window.location.href='plans.php?success=updated';</script>";
        exit;
    }
}

$plans = $db->getRowsAssoc("SELECT * FROM paket_ozellikleri ORDER BY price ASC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        SaaS Paket ve Modül Yönetimi
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Paketler</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="#" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_package">
                        Yeni Paket Ekle
                    </a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <?php if(!empty($semaUyarilari)): ?>
                <div class="alert alert-warning bg-light-warning border-warning d-flex flex-column p-5 mb-10">
                    <h4 class="fw-semibold text-warning">Tablo yapısı eksik</h4>
                    <span class="mb-3">Aşağıdaki kolonlar hazırlanamadı; o alanlar kaydedilemeyecek.</span>
                    <ul class="mb-0">
                        <?php foreach($semaUyarilari as $satir): ?>
                        <li><?= htmlspecialchars($satir) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if(!empty($_SESSION['plans_error'])): ?>
                <div class="alert alert-danger bg-light-danger border-danger d-flex flex-column p-5 mb-10">
                    <h4 class="fw-semibold text-danger">Kaydedilemedi</h4>
                    <span class="mb-3">Aşağıdaki alanlar veritabanına yazılamadı. Değerler eski hâlinde kaldı.</span>
                    <ul class="mb-0">
                        <?php foreach($_SESSION['plans_error'] as $satir): ?>
                        <li><?= htmlspecialchars($satir) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['plans_error']); ?>
                <?php elseif(isset($_GET['success'])): ?>
                <div class="alert alert-success bg-light-success border-success d-flex flex-column flex-sm-row p-5 mb-10">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h4 class="fw-semibold">Başarılı</h4>
                        <span>Paket matrisi kaydedildi ve geri okunarak doğrulandı.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="plans.php">
                            <?= csrfTokenField() ?>
                            <input type="hidden" name="action" value="update_matrix">
                            
                            <div class="table-responsive">
                                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="min-w-200px ps-4 rounded-start">Özellik / Modül</th>
                                            <?php foreach($plans as $plan): ?>
                                            <th class="min-w-150px text-center">
                                                <span class="text-dark fs-5"><?= htmlspecialchars($plan['plan_display_name']) ?></span>
                                                <br>
                                                <span class="badge badge-light-primary fw-bold px-4 py-2 mt-2"><?= htmlspecialchars($plan['plan_name']) ?></span>
                                            </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Fiyat Satırı -->
                                        <tr>
                                            <td class="ps-4 fw-semibold text-gray-800">Aylık Fiyat (TL)</td>
                                            <?php foreach($plans as $plan): ?>
                                            <td class="text-center">
                                                <input type="number" step="0.01" class="form-control form-control-sm text-center" name="price[<?= $plan['plan_name'] ?>]" value="<?= $plan['price'] ?>">
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <!-- Max Kullanıcı Satırı -->
                                        <tr>
                                            <td class="ps-4 fw-semibold text-gray-800">
                                                Maksimum Kullanıcı
                                                <span class="text-muted fw-normal d-block" style="font-size:.8rem">0 = sınırsız</span>
                                            </td>
                                            <?php foreach($plans as $plan): ?>
                                            <td class="text-center">
                                                <input type="number" min="0" class="form-control form-control-sm text-center" name="max_users[<?= $plan['plan_name'] ?>]" value="<?= (int)($plan['max_total_users'] ?? 0) ?>">
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <!-- Max Şube Satırı -->
                                        <tr>
                                            <td class="ps-4 fw-semibold text-gray-800">
                                                Maksimum Şube
                                                <span class="text-muted fw-normal d-block" style="font-size:.8rem">0 = sınırsız</span>
                                            </td>
                                            <?php foreach($plans as $plan): ?>
                                            <td class="text-center">
                                                <input type="number" min="0" class="form-control form-control-sm text-center" name="max_branches[<?= $plan['plan_name'] ?>]" value="<?= (int)($plan['max_branches'] ?? 0) ?>">
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>

                                        <!-- Yıllık İndirim Satırı -->
                                        <tr>
                                            <td class="ps-4 fw-semibold text-gray-800">
                                                Yıllık Ödeme İndirimi (%)
                                                <span class="text-muted fw-normal d-block" style="font-size:.8rem">12 ay ve üzeri sözleşmelerde uygulanır · 0 = indirim yok</span>
                                            </td>
                                            <?php foreach($plans as $plan):
                                                $yi = (int)($plan['yillik_indirim'] ?? 0);
                                                $aylik = (float)($plan['price'] ?? 0);
                                                $yillikTutar = $aylik * 12 * (1 - $yi / 100);
                                            ?>
                                            <td class="text-center">
                                                <input type="number" min="0" max="100" class="form-control form-control-sm text-center" name="yillik_indirim[<?= $plan['plan_name'] ?>]" value="<?= $yi ?>">
                                                <span class="text-muted d-block mt-1" style="font-size:.78rem">
                                                    Yıllık: <?= number_format($yillikTutar, 2, ',', '.') ?> ₺
                                                </span>
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>

                                        <!-- Ayırıcı -->
                                        <tr><td colspan="<?= count($plans) + 1 ?>" class="bg-light"></td></tr>

                                        <!-- Modüller Matrisi -->
                                        <?php foreach($allModules as $modKey => $modLabel): ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold text-gray-700">
                                                <i class="ki-duotone ki-check-circle text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                                <?= $modLabel ?>
                                            </td>
                                            <?php foreach($plans as $plan): 
                                                $allowed = json_decode($plan['allowed_modules'] ?? '[]', true);
                                                if (!is_array($allowed)) $allowed = [];
                                                $isChecked = in_array('all', $allowed) || in_array($modKey, $allowed);
                                            ?>
                                            <td class="text-center">
                                                <div class="form-check form-check-custom form-check-solid justify-content-center">
                                                    <input class="form-check-input" type="checkbox" name="modules[<?= $plan['plan_name'] ?>][]" value="<?= $modKey ?>" <?= $isChecked ? 'checked' : '' ?> />
                                                </div>
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-8">
                                <button type="submit" class="btn btn-lg btn-primary">
                                    <i class="ki-duotone ki-save-2 fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    Tüm Değişiklikleri Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: Yeni Paket Ekle -->
<div class="modal fade" id="kt_modal_add_package" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Yeni Paket Oluştur</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form method="POST" action="plans.php">
                    <?= csrfTokenField() ?>
                    <input type="hidden" name="action" value="add_package">
                    
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">Paket Kodu (İngilizce karakter, boşluksuz)</span>
                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Sistemde kullanılacak benzersiz kimlik (Örn: premium)"></i>
                        </label>
                        <input type="text" class="form-control form-control-solid" name="plan_name" placeholder="premium" required />
                    </div>
                    
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="required fs-6 fw-semibold form-label mb-2">Görünen Ad</label>
                        <input type="text" class="form-control form-control-solid" name="plan_display_name" placeholder="Premium Paket" required />
                    </div>
                    
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="required fs-6 fw-semibold form-label mb-2">Aylık Fiyat (TL)</label>
                        <input type="number" step="0.01" class="form-control form-control-solid" name="price" value="0.00" required />
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Oluştur</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'config/footer.php';
?>
