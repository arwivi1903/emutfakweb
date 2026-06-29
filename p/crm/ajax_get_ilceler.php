<?php
require_once __DIR__ . '/../classes/allClass.php';

// JSON header
header('Content-Type: application/json; charset=utf-8');

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
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz İl ID']);
}
