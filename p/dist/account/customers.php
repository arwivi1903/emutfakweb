<?php 
// Silme işlemi (Soft Delete - Status'u suspended yap) - Header'dan ÖNCE yapılmalı
if (isset($_POST["customer_id"]) && is_numeric($_POST["customer_id"])) {
    require_once __DIR__ . '/../../classes/database.class.php';
    $db = new Database();
    
    $sil = intval($_POST["customer_id"]);
    if ($sil > 0) {
        // Önce müşterinin mevcut durumunu kontrol et
        $customer = $db->getRow('SELECT status FROM customers WHERE customer_id = ?', array($sil));
        
        if ($customer && $customer->status === 'suspended') {
            // Zaten pasif durumda
            header('Location: customers.php?already_passive=1');
            exit;
        }
        
        if ($customer) {
            $musterisil = $db->Update('UPDATE customers SET status = ? WHERE customer_id = ?', array('suspended', $sil));
            if ($musterisil) {
                header('Location: customers.php?deleted=1');
                exit;
            }else{
                header('Location: customers.php?error=1');
                exit;
            }
        } else {
            header('Location: customers.php?notfound=1');
            exit;
        }
    }
}

require_once __DIR__ . '/../../config/header.php';
require_once __DIR__ . '/../../config/sidebar.php';


// Filtreler
$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$industry = trim($_GET['industry'] ?? '');
$letter = trim($_GET['letter'] ?? '');

// Sıralama
$sortable = [
    'id' => 'customer_id',
    'code' => 'customer_code',
    'name' => 'company_name',
    'phone' => 'company_phone',
    'email' => 'company_email',
    'status' => 'status',
    'trial_start' => 'trial_start_date',
    'trial_end' => 'trial_end_date',
    'created' => 'created_at',
];
$sort = $_GET['sort'] ?? 'created';
if (!isset($sortable[$sort])) $sort = 'created';
$dir = strtolower($_GET['dir'] ?? 'desc');
if (!in_array($dir, ['asc','desc'], true)) $dir = 'desc';

// Sayfalama
$perAllowed = [10,25,50,100,500];
$per_page = (int)($_GET['per_page'] ?? 25);
if (!in_array($per_page, $perAllowed, true)) $per_page = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// WHERE ve parametreler
$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(company_name LIKE ? OR company_email LIKE ? OR company_phone LIKE ? OR customer_code LIKE ? OR contact_name LIKE ?)";
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($industry !== '') {
    $where[] = "industry = ?";
    $params[] = $industry;
}

if ($letter !== '' && $letter !== 'all') {
    $letterLower = mb_strtolower($letter, 'UTF-8');
    $letterUpper = mb_strtoupper($letter, 'UTF-8');
    $where[] = "(BINARY LEFT(company_name, 1) = ? OR BINARY LEFT(company_name, 1) = ?)";
    $params[] = $letterUpper;
    $params[] = $letterLower;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Toplam kayıt
$totalRow = $db->getRow("SELECT COUNT(*) AS total FROM customers $whereSql", $params) ?? (object)['total' => 0];
$total = (int)($totalRow->total ?? 0);
$totalPages = max(1, (int)ceil($total / $per_page));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $per_page; }

// Liste sorgusu
$orderBy = $sortable[$sort] . ' ' . strtoupper($dir);
$sql = "SELECT * FROM customers $whereSql ORDER BY $orderBy LIMIT $per_page OFFSET $offset";
$customers = $db->getRows($sql, $params) ?? [];

// Sıralama linki
function createSortLink($key, $label, $currentSort, $currentDir) {
    $nextDir = ($currentSort === $key && $currentDir === 'asc') ? 'desc' : 'asc';
    $icon = '';
    if ($currentSort === $key) {
        $icon = $currentDir === 'asc' ? ' <i class="ki-duotone ki-arrow-up fs-5"></i>' : ' <i class="ki-duotone ki-arrow-down fs-5"></i>';
    } else {
        $icon = ' <i class="ki-duotone ki-sort fs-5 text-muted"></i>';
    }
    $href = 'customers.php?sort=' . $key . '&dir=' . $nextDir . '&page=1';
    if (!empty($_GET['q'])) $href .= '&q=' . urlencode($_GET['q']);
    if (!empty($_GET['status'])) $href .= '&status=' . urlencode($_GET['status']);
    if (!empty($_GET['industry'])) $href .= '&industry=' . urlencode($_GET['industry']);
    if (!empty($_GET['letter'])) $href .= '&letter=' . urlencode($_GET['letter']);
    if (!empty($_GET['per_page'])) $href .= '&per_page=' . urlencode($_GET['per_page']);
    return '<a href="' . $href . '" class="text-dark text-hover-primary">' . $label . $icon . '</a>';
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- Mesajlar -->
                <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Başarılı!</strong> Müşteri pasif duruma alındı.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['already_passive'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Uyarı!</strong> Bu müşteri zaten pasif durumda.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Hata!</strong> İşlem sırasında bir hata oluştu.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- İstatistikler -->
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-3">
                        <div class="card card-flush h-xl-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="svg-icon svg-icon-primary svg-icon-3x ms-n1">
                                        <i class="ki-duotone ki-people fs-3x text-primary">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <div class="ms-5">
                                        <div class="fs-2hx fw-bold text-gray-800"><?php echo $total; ?></div>
                                        <div class="fs-7 fw-semibold text-gray-500">Toplam Müşteri</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush h-xl-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="svg-icon svg-icon-success svg-icon-3x ms-n1">
                                        <i class="ki-duotone ki-user fs-3x text-success">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <div class="ms-5">
                                        <div class="fs-2hx fw-bold text-gray-800">
                                            <?php 
                                            $activeCount = $db->getRow("SELECT COUNT(*) as cnt FROM customers WHERE status = 'active'");
                                            echo $activeCount->cnt ?? 0;
                                            ?>
                                        </div>
                                        <div class="fs-7 fw-semibold text-gray-500">Aktif</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush h-xl-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="svg-icon svg-icon-info svg-icon-3x ms-n1">
                                        <i class="ki-duotone ki-office-bag fs-3x text-info">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <div class="ms-5">
                                        <div class="fs-2hx fw-bold text-gray-800">
                                            <?php 
                                            $trialCount = $db->getRow("SELECT COUNT(*) as cnt FROM customers WHERE status = 'trial'");
                                            echo $trialCount->cnt ?? 0;
                                            ?>
                                        </div>
                                        <div class="fs-7 fw-semibold text-gray-500">Deneme</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush h-xl-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="svg-icon svg-icon-warning svg-icon-3x ms-n1">
                                        <i class="ki-duotone ki-calendar fs-3x text-warning">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <div class="ms-5">
                                        <div class="fs-2hx fw-bold text-gray-800">
                                            <?php 
                                            $suspendedCount = $db->getRow("SELECT COUNT(*) as cnt FROM customers WHERE status = 'suspended'");
                                            echo $suspendedCount->cnt ?? 0;
                                            ?>
                                        </div>
                                        <div class="fs-7 fw-semibold text-gray-500">Askıda</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ana Kart -->
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Müşteri Listesi</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Tüm müşterilerinizi görüntüleyin ve yönetin</span>
                        </h3>
                        <div class="card-toolbar">
                            <a href="customeradd.php" class="btn btn-sm btn-light-primary">
                                <i class="ki-duotone ki-plus fs-2"></i>
                                Yeni Müşteri
                            </a>
                        </div>
                    </div>

                    <!-- Filtreler -->
                    <div class="card-body border-top">
                        <form method="get" action="">
                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" name="q" class="form-control" placeholder="Ara (şirket, email, telefon, kod)..." value="<?php echo htmlspecialchars($q); ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ki-duotone ki-magnifier fs-2"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="">Tüm Durumlar</option>
                                        <option value="trial" <?php echo $status==='trial'?'selected':''; ?>>🔄 Deneme</option>
                                        <option value="active" <?php echo $status==='active'?'selected':''; ?>>✅ Aktif</option>
                                        <option value="suspended" <?php echo $status==='suspended'?'selected':''; ?>>⏸️ Askıda</option>
                                        <option value="expired" <?php echo $status==='expired'?'selected':''; ?>>❌ Süresi Dolmuş</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ([10,25,50,100,500] as $pp): ?>
                                            <option value="<?php echo $pp; ?>" <?php echo $per_page===$pp?'selected':''; ?>><?php echo $pp; ?> Kayıt</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 text-end">
                                    <?php if ($q || $status || $industry || $letter): ?>
                                    <a href="customers.php" class="btn btn-light-secondary">
                                        <i class="ki-duotone ki-arrows-circle fs-2"></i>
                                        Filtreleri Temizle
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Harf Filtresi -->
                            <div class="letter-filter-modern mb-3">
                                <?php
                                // Harf filtresi için link oluştur (redeklerasyonu önlemek için koşullu)
                                if (!function_exists('buildLetterLink')) {
                                    function buildLetterLink($letterValue) {
                                        $params = [];
                                        if (!empty($_GET['q'])) $params['q'] = $_GET['q'];
                                        if (!empty($_GET['status'])) $params['status'] = $_GET['status'];
                                        if (!empty($_GET['industry'])) $params['industry'] = $_GET['industry'];
                                        if (!empty($_GET['sort'])) $params['sort'] = $_GET['sort'];
                                        if (!empty($_GET['dir'])) $params['dir'] = $_GET['dir'];
                                        if (!empty($_GET['per_page'])) $params['per_page'] = $_GET['per_page'];
                                        if ($letterValue !== '') $params['letter'] = $letterValue;
                                        return 'dist/account/customers.php?' . http_build_query($params);
                                    }
                                }
                                $letters = ['A', 'B', 'C', 'Ç', 'D', 'E', 'F', 'G', 'Ğ', 'H', 'I', 'İ', 'J', 'K', 'L', 'M', 
                                            'N', 'O', 'Ö', 'P', 'R', 'S', 'Ş', 'T', 'U', 'Ü', 'V', 'Y', 'Z'];
                                ?>
                                <a href="<?php echo buildLetterLink(''); ?>" class="btn btn-sm btn-active-primary <?php echo $letter===''?'active':''; ?>">Tümü</a>
                                <?php
                                foreach ($letters as $l) {
                                    $active = ($letter === $l) ? 'active' : '';
                                    echo "<a href='" . buildLetterLink($l) . "' class='btn btn-sm btn-light btn-active-primary $active'>$l</a> ";
                                }
                                ?>
                            </div>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($dir); ?>">
                        </form>

                        <style>
                        .letter-filter-modern {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 4px;
                        }
                        .letter-filter-modern .btn {
                            min-width: 38px;
                            padding: 6px 10px;
                        }
                        </style>
                    </div>

                    <!-- Tablo -->
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">ID</th>
                                        <th class="min-w-100px">Kod</th>
                                        <th class="min-w-150px"><?php echo createSortLink('name', 'Şirket Adı', $sort, $dir); ?></th>
                                        <th class="min-w-120px">Telefon</th>
                                        <th class="min-w-150px">E-mail</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="min-w-110px">Başlangıç</th>
                                        <th class="min-w-110px">Bitiş</th>
                                        <th class="min-w-100px">Kalan Süre</th>
                                        <th class="min-w-120px">Kayıt Tarihi</th>
                                        <th class="text-center min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (empty($customers)): ?>
                                        <tr>
                                            <td colspan="11" class="text-center py-10">
                                                <div class="text-center">
                                                    <i class="ki-duotone ki-search-list fs-3x text-muted mb-3">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    <h5 class="text-muted">Kayıt Bulunamadı</h5>
                                                    <p class="text-muted small">Arama kriterlerinize uygun müşteri bulunamadı.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-primary"><?php echo htmlspecialchars($customer->customer_id ?? ''); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($customer->customer_code ?? '-'); ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <?php if (!empty($customer->logo_url)): ?>
                                                            <img src="<?php echo htmlspecialchars($customer->logo_url); ?>" alt="Logo" class="w-100" />
                                                        <?php else: ?>
                                                            <div class="symbol-label bg-light-primary">
                                                                <span class="text-primary fw-bold"><?php echo mb_substr($customer->company_name ?? '', 0, 1); ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <a href="customer-detail.php?id=<?php echo (int)($customer->customer_id ?? 0); ?>" class="text-gray-900 fw-bold text-hover-primary"><?php echo htmlspecialchars($customer->company_name ?? ''); ?></a>
                                                        <?php if (!empty($customer->contact_name)): ?>
                                                            <span class="text-muted fs-7"><?php echo htmlspecialchars($customer->contact_name); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="ki-duotone ki-phone fs-5 text-muted me-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <?php echo htmlspecialchars($customer->company_phone ?? '-'); ?>
                                            </td>
                                            <td>
                                                <i class="ki-duotone ki-sms fs-5 text-muted me-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <?php echo htmlspecialchars($customer->company_email ?? '-'); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $st = strtolower($customer->status ?? 'trial');
                                                $sBadge = ($st==='active') ? 'success' : (($st==='suspended') ? 'danger' : (($st==='expired') ? 'secondary' : 'warning'));
                                                $sIcon = ($st==='active') ? '✅' : (($st==='suspended') ? '⏸️' : (($st==='expired') ? '❌' : '🔄'));
                                                $sText = ($st==='active') ? 'Aktif' : (($st==='suspended') ? 'Askıda' : (($st==='expired') ? 'Süresi Dolmuş' : 'Deneme'));
                                                ?>
                                                <span class="badge badge-light-<?php echo $sBadge; ?>">
                                                    <?php echo $sIcon . ' ' . htmlspecialchars($sText); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <?php 
                                                    $trialStart = $customer->trial_start_date ?? '';
                                                    echo $trialStart ? date('d.m.Y', strtotime($trialStart)) : '-';
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <?php 
                                                    $trialEnd = $customer->trial_end_date ?? '';
                                                    echo $trialEnd ? date('d.m.Y', strtotime($trialEnd)) : '-';
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($trialEnd && strtotime($trialEnd) > 0) {
                                                    $today = new DateTime();
                                                    $endDate = new DateTime($trialEnd);
                                                    $diff = $today->diff($endDate);
                                                    $daysLeft = (int)$diff->format('%r%a');
                                                    
                                                    if ($daysLeft > 0) {
                                                        $badgeClass = $daysLeft > 7 ? 'success' : ($daysLeft > 3 ? 'warning' : 'danger');
                                                        echo '<span class="badge badge-light-' . $badgeClass . '">' . $daysLeft . ' gün</span>';
                                                    } elseif ($daysLeft === 0) {
                                                        echo '<span class="badge badge-danger">Bugün Bitiyor</span>';
                                                    } else {
                                                        echo '<span class="badge badge-secondary">Süresi Doldu (' . abs($daysLeft) . ' gün)</span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <?php 
                                                    $created = $customer->created_at ?? '';
                                                    echo $created ? date('d.m.Y', strtotime($created)) : '-';
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="customer-edit.php?id=<?php echo (int)($customer->customer_id ?? 0); ?>" 
                                                       class="btn btn-icon btn-sm btn-primary" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Düzenle">
                                                        <i class="ki-duotone ki-pencil fs-4">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <?php if ($st !== 'suspended'): ?>
                                                    <button type="button" 
                                                            class="btn btn-icon btn-sm btn-danger" 
                                                            data-bs-toggle="tooltip" 
                                                            title="Pasif Yap"
                                                            onclick="deleteCustomer(<?php echo (int)($customer->customer_id ?? 0); ?>, '<?php echo htmlspecialchars($customer->company_name ?? ''); ?>')">
                                                        <i class="ki-duotone ki-trash fs-4">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                            <span class="path5"></span>
                                                        </i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-icon btn-sm btn-light-secondary" 
                                                            data-bs-toggle="tooltip" 
                                                            title="Zaten Pasif"
                                                            disabled>
                                                        <i class="ki-duotone ki-lock fs-4 text-gray-600">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Sayfalama -->
                        <?php if ($total > 0): ?>
                        <div class="d-flex flex-stack flex-wrap pt-5">
                            <div class="text-muted fw-semibold">
                                Toplam <strong><?php echo $total; ?></strong> kayıttan 
                                <strong><?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total); ?></strong> 
                                arası gösteriliyor
                            </div>
                            <ul class="pagination">
                                <?php
                                // Sayfalama linki oluşturma fonksiyonu (redeklerasyonu önlemek için koşullu)
                                if (!function_exists('buildPageLink')) {
                                    function buildPageLink($pageNum, $sort, $dir) {
                                        $params = ['page' => $pageNum, 'sort' => $sort, 'dir' => $dir];
                                        if (!empty($_GET['q'])) $params['q'] = $_GET['q'];
                                        if (!empty($_GET['status'])) $params['status'] = $_GET['status'];
                                        if (!empty($_GET['industry'])) $params['industry'] = $_GET['industry'];
                                        if (!empty($_GET['letter'])) $params['letter'] = $_GET['letter'];
                                        if (!empty($_GET['per_page'])) $params['per_page'] = $_GET['per_page'];
                                        return 'customers.php?' . http_build_query($params);
                                    }
                                }
                                
                                if ($page > 3) {
                                    echo '<li class="page-item"><a class="page-link" href="' . buildPageLink(1, $sort, $dir) . '">1</a></li>';
                                    if ($page > 4) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                
                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);
                                for ($i=$start; $i<=$end; $i++) {
                                    $active = ($i === $page) ? 'active' : '';
                                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . buildPageLink($i, $sort, $dir) . '">' . $i . '</a></li>';
                                }
                                
                                if ($page < $totalPages - 2) {
                                    if ($page < $totalPages - 3) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="' . buildPageLink($totalPages, $sort, $dir) . '">' . $totalPages . '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../config/footer.php'; ?>
</div>

<script>
$(document).ready(function() {
    // Tooltip başlat
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Enter tuşu ile arama
    $('input[name="q"]').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});

function deleteCustomer(id, name) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: '"' + name + '" adlı müşteriyi pasif duruma almak istediğinize emin misiniz?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, Pasif Yap',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // POST isteği için form oluştur ve gönder
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'dist/account/customers.php';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'customer_id';
            input.value = id;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
