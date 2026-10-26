<?php
/**
 * Herkese açık ilçe listesi (kayıt formu için). Yalnızca okuma; tek parametre: il_id.
 * Yönetim panelindeki p/crm/ajax_get_ilceler.php yönetici oturumu istediği için
 * ziyaretçiler ondan ilçe alamıyordu.
 */
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false]);
    exit;
}

$ilId = (int)($_GET['city_id'] ?? 0);
if ($ilId <= 0 || $ilId > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geçersiz il.']);
    exit;
}

try {
    $st = $pdo->prepare("SELECT ilce_id, ilceadi FROM `" . prolynweb_template_db() . "`.ilceler WHERE iller_id = ? ORDER BY ilceadi ASC");
    $st->execute([$ilId]);
    header('Cache-Control: public, max-age=86400');
    echo json_encode(['success' => true, 'data' => $st->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[ilceler] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'İlçeler alınamadı.']);
}
