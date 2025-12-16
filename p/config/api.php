<?php

require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';
// require_once __DIR__ . '/../functions/auth.php';
// require_once __DIR__ . '/../functions/logtut.php';

// JSON header
header('Content-Type: application/json; charset=utf-8');

// Session kontrolü
start_secure_session();

// Oturumda giriş yapılmış mı kontrol et
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    error_log("Session ERROR: admin_id not found. Session keys: " . json_encode(array_keys($_SESSION)));
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim. Lütfen giriş yapınız.']);
    exit;
}

// Action parametresini al
$action = trim($_GET['action'] ?? '');

error_log("API Request - Action: '{$action}', Method: {$_SERVER['REQUEST_METHOD']}");

// Database örneğini al
$db = new Database();

// Deactivate account action
if ($action === 'deactivate_account' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // JSON gövdesini al
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['deactivate']) || !$input['deactivate']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
            exit;
        }
        
        // Admin ID ve tablo bilgisini session'dan al
        $adminId = (int)$_SESSION['admin_id'];
        $adminTable = $_SESSION['admin_table'] ?? 'admin_users';
        $adminIdCol = $_SESSION['admin_id_col'] ?? 'admin_id';
        
        // Session bilgisi kontrol et
        if (!$adminId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Admin ID bulunamadı. Lütfen oturumu yenileyiniz.']);
            exit;
        }
        
        // Debug bilgisi
        error_log("Deactivate Request - AdminId: {$adminId}, Table: {$adminTable}, IdCol: {$adminIdCol}");
        
        // Hesabı pasif yap (status = 'inactive')
        // İlk önce sadece status güncelleyelim
        $updateQuery = "UPDATE {$adminTable} SET status = 'inactive' WHERE {$adminIdCol} = ?";
        
        error_log("Update Query: {$updateQuery}");
        
        try {
            $result = $db->Update($updateQuery, [$adminId]);
            error_log("Update Result: {$result}");
        } catch (Exception $updateError) {
            // updated_at sütunu yoksa hata aldığımız için tekrar deneyelim
            throw new Exception('Update başarısız: ' . $updateError->getMessage());
        }
        
        // İşlemi logla
        error_log("HESAP_PASIF_YAPILDI - Admin ID: {$adminId}");
        
        // Başarılı yanıt gönder
        // Not: Session logout.php'de temizlenecek
        echo json_encode([
            'success' => true,
            'message' => 'Hesabınız başarıyla pasif hale getirilmiştir.'
        ]);
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        error_log("Exception in deactivate_account: " . $errorMsg);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Hesap pasifleştirirken bir hata oluştu',
            'error_debug' => $errorMsg
        ]);
    }
    exit;
}

// Bilinmeyen action
http_response_code(400);
error_log("Unknown action: '{$action}'");
echo json_encode(['success' => false, 'message' => 'Bilinmeyen işlem: ' . $action]);
exit;

?>
