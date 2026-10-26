<?php
require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';

// JSON header
header('Content-Type: application/json; charset=utf-8');

start_secure_session();
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

try {
    $db = new Database();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Failed']);
    exit;
}

$city_id = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;

if ($city_id > 0) {
    try {
        $ilceler = $db->getRowsAssoc("SELECT ilce_id, ilceadi FROM prolyn_default.ilceler WHERE iller_id = ? ORDER BY ilceadi ASC", [$city_id]);
        echo json_encode(['success' => true, 'data' => $ilceler]);
    } catch(Exception $e) {
        error_log('[ajax_get_ilceler] ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Sorgu başarısız.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz İl ID']);
}
