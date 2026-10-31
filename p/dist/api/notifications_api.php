<?php
require_once __DIR__ . '/../../functions/auth.php';

// Oturumu, güvenli çerez parametreleri devreye girmeden hiçbir kodun
// (ör. combine.php'nin otomatik CSRF kontrolü) başlatmadığından emin olmak için
// combine.php'den ÖNCE açılır.
start_secure_session();

require_once __DIR__ . '/../../classes/allClass.php';
require_once __DIR__ . '/../../functions/combine.php';

header('Content-Type: application/json');

$db = new Database();

// Oturum kontrolü
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_unread') {
    $notifications = $db->getRowsAssoc(
        "SELECT notification_id, title, message, type, created_at FROM bildirimler WHERE admin_id = ? AND is_read = 0 ORDER BY notification_id DESC LIMIT 10",
        [$admin_id]
    );
    $notifications = array_merge($notifications, prolyn_canli_bildirimler($db));
    usort($notifications, function ($a, $b) {
        return strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? ''));
    });
    echo json_encode($notifications);
    exit;
}

if ($action === 'mark_read') {
    $notification_id = $_POST['notification_id'] ?? 0;
    if ($notification_id) {
        $db->Update(
            "UPDATE bildirimler SET is_read = 1, read_at = NOW() WHERE notification_id = ? AND admin_id = ?",
            [$notification_id, $admin_id]
        );
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Geçersiz bildirim ID']);
    }
    exit;
}

// Canlı (hesaplanan) bildirimleri bu yönetici için okundu işaretler
if ($action === 'mark_live_read') {
    $keys = $_POST['keys'] ?? [];
    if (!is_array($keys)) { $keys = [$keys]; }
    $adet = 0;
    foreach (array_slice($keys, 0, 50) as $k) {
        $k = (string)$k;
        if (!preg_match('/^[a-f0-9]{32}$/', $k)) { continue; }   // yalnızca md5 anahtarı
        if (!$db->tableExists('canli_bildirim_okundu')) { break; }
        $db->Insert("INSERT IGNORE INTO canli_bildirim_okundu (admin_id, anahtar) VALUES (?, ?)", [(int)$admin_id, $k]);
        $adet++;
    }
    echo json_encode(['success' => true, 'count' => $adet]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
