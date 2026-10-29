<?php
/**
 * Prolyn Admin Panel - Calculate Proration
 */
require_once __DIR__ . '/../../functions/auth.php';

// Oturumu, güvenli çerez parametreleri devreye girmeden hiçbir kodun
// (ör. combine.php'nin otomatik CSRF kontrolü) başlatmadığından emin olmak için
// combine.php'den ÖNCE açılır.
start_secure_session();

require_once __DIR__ . '/../../classes/allClass.php';
require_once __DIR__ . '/../../functions/combine.php';

$db = new Database();
oturumkontrol();

Auth::requireModule('manage_tenants');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

$tenant_id = (int)($_POST['tenant_id'] ?? 0);
$new_paket = trim($_POST['new_paket'] ?? '');

if ($tenant_id === 0 || empty($new_paket)) {
    echo json_encode(['success' => false, 'message' => 'Eksik parametre.']);
    exit;
}

// Map frontend names to DB plan names
$plan_map = [
    'Başlangıç' => 'basic',
    'Pro' => 'pro',
    'Kurumsal' => 'enterprise'
];
$new_plan_name = $plan_map[$new_paket] ?? 'basic';

// Get Tenant Info
$tenant = $db->getRowAssoc("SELECT * FROM kurumlar WHERE customer_id = ?", [$tenant_id]);
if (!$tenant) {
    echo json_encode(['success' => false, 'message' => 'Kurum bulunamadı.']);
    exit;
}

$old_paket = $tenant['musteri_paketi'];
$old_plan_name = $plan_map[$old_paket] ?? 'basic';

if ($old_plan_name === $new_plan_name) {
    echo json_encode(['success' => false, 'message' => 'Kurum zaten bu pakette.']);
    exit;
}

// Get Prices
$prices = $db->getRowsAssoc("SELECT plan_name, price FROM paket_ozellikleri");
$price_map = [];
foreach ($prices as $p) {
    $price_map[$p['plan_name']] = (float)$p['price'];
}

$old_monthly_price = $price_map[$old_plan_name] ?? 0;
$new_monthly_price = $price_map[$new_plan_name] ?? 0;

$old_annual_price = $old_monthly_price * 12;
$new_annual_price = $new_monthly_price * 12;

$sozlesme_baslangic = $tenant['sozlesme_baslangic_tarihi'];
$sozlesme_bitis = $tenant['sozlesme_bitis_tarihi'];

if (empty($sozlesme_baslangic) || empty($sozlesme_bitis) || $tenant['subscription_type'] !== 'contract') {
    echo json_encode(['success' => false, 'message' => 'Kurumun aktif bir sözleşmesi yok. Sadece sözleşmeli kurumlar paket değiştirebilir.']);
    exit;
}

$start_time = strtotime($sozlesme_baslangic);
$end_time = strtotime($sozlesme_bitis);
$now = time();

if ($end_time <= $now) {
    echo json_encode(['success' => false, 'message' => 'Sözleşme süresi dolmuş. Lütfen sözleşmeyi yenileyin.']);
    exit;
}

// Remaining Days in Contract
$remaining_days = max(0, floor(($end_time - $now) / (60 * 60 * 24)));

// Calculate Daily Prices
$old_daily_price = $old_annual_price / 365;
$new_daily_price = $new_annual_price / 365;

// Remaining Value of Old Package
$remaining_old_value = $remaining_days * $old_daily_price;

// Cost of New Package for Remaining Days
$remaining_new_cost = $remaining_days * $new_daily_price;

// Prorated Difference
$difference = $remaining_new_cost - $remaining_old_value;

$is_downgrade = false;
if ($difference < 0) {
    $difference = 0;
    $is_downgrade = true;
}

echo json_encode([
    'success' => true,
    'remaining_days' => $remaining_days,
    'old_annual_price' => $old_annual_price,
    'new_annual_price' => $new_annual_price,
    'remaining_old_value' => round($remaining_old_value, 2),
    'remaining_new_cost' => round($remaining_new_cost, 2),
    'upgrade_fee' => round($difference, 2),
    'is_downgrade' => $is_downgrade
]);
