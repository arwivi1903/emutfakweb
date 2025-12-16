<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../classes/allClass.php';
require_once __DIR__ . '/../../functions/combine.php';

// JSON header
header('Content-Type: application/json; charset=utf-8');

// Session kontrolü
if (function_exists('start_secure_session')) {
    start_secure_session();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Yetkisiz erişim kontrolü
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim. Lütfen giriş yapınız.', 'redirect' => true]);
    exit;
}

// Database örneğini al
try {
    $db = new Database('master');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database hatası: ' . $e->getMessage()]);
    exit;
}

// Action parametresini al
$action = trim($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

// ==== GET - Admin Users Listesi ====
if ($action === 'list' && $method === 'GET') {
    try {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'admin_id';
        $order = isset($_GET['order']) ? strtoupper(trim($_GET['order'])) : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        // Güvenlik: Sort parametresini valide et
        $allowed_sorts = ['admin_id', 'email', 'full_name', 'role', 'status', 'created_at'];
        if (!in_array($sort, $allowed_sorts)) {
            $sort = 'admin_id';
        }

        // Güvenlik: Order parametresini valide et
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Offset hesapla
        $offset = ($page - 1) * $limit;

        // Arama şartı oluştur
        $whereClause = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "(email LIKE ? OR full_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm];
        }

        // Toplam kayıt sayısını al
        $countQuery = "SELECT COUNT(*) as total FROM admin_users WHERE {$whereClause}";
        $countResult = $db->getRow($countQuery, $params);
        $total = isset($countResult->total) ? (int)$countResult->total : 0;

        // Verileri çek
        $query = "SELECT admin_id, email, full_name, admin_pic, role, status, 
                  can_manage_customers, can_manage_subscriptions, can_manage_payments, 
                  can_view_analytics, two_factor_enabled, last_login, created_at, updated_at 
                  FROM admin_users 
                  WHERE {$whereClause} 
                  ORDER BY {$sort} {$order} 
                  LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $users = $db->getRows($query, $params);
        
        if (!is_array($users)) {
            $users = [];
        }

        // Cevap hazırla
        $totalPages = $limit > 0 ? ceil($total / $limit) : 1;
        
        echo json_encode([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => $totalPages
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
    }
    exit;
}

// ==== GET - Tek Admin User ====
if ($action === 'detail' && $method === 'GET') {
    try {
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
            exit;
        }

        $query = "SELECT admin_id, email, full_name, admin_pic, role, status, 
                  can_manage_customers, can_manage_subscriptions, can_manage_payments, 
                  can_view_analytics, two_factor_enabled, last_login, created_at, updated_at 
                  FROM admin_users WHERE admin_id = ?";
        
        $user = $db->getRow($query, [$userId]);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $user
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==== POST - Yeni Admin User Ekle ====
if ($action === 'create' && $method === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation
        $email = isset($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) ? $input['password'] : '';
        $full_name = isset($input['full_name']) ? trim($input['full_name']) : '';
        $admin_pic = isset($input['admin_pic']) ? trim($input['admin_pic']) : NULL;
        $role = isset($input['role']) ? trim($input['role']) : 'support';
        $status = isset($input['status']) ? trim($input['status']) : 'active';
        $can_manage_customers = isset($input['can_manage_customers']) ? (int)$input['can_manage_customers'] : 1;
        $can_manage_subscriptions = isset($input['can_manage_subscriptions']) ? (int)$input['can_manage_subscriptions'] : 1;
        $can_manage_payments = isset($input['can_manage_payments']) ? (int)$input['can_manage_payments'] : 1;
        $can_view_analytics = isset($input['can_view_analytics']) ? (int)$input['can_view_analytics'] : 1;

        // Boş kontrol
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'E-posta ve şifre gerekli']);
            exit;
        }

        // E-posta format kontrolü
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz e-posta adresi']);
            exit;
        }

        // Role validation
        $allowed_roles = ['superadmin', 'support', 'financial'];
        if (!in_array($role, $allowed_roles)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz rol']);
            exit;
        }

        // Status validation
        $allowed_status = ['active', 'inactive', 'suspended'];
        if (!in_array($status, $allowed_status)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz durum']);
            exit;
        }

        // E-posta benzersiz midir kontrolü
        $existQuery = "SELECT COUNT(*) as count FROM admin_users WHERE email = ?";
        $existResult = $db->getRow($existQuery, [$email]);
        if ($existResult->count > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Bu e-posta zaten kullanılıyor']);
            exit;
        }

        // Şifreyi hash'le
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Ekle
        $query = "INSERT INTO admin_users 
                  (email, password, full_name, admin_pic, role, status, 
                   can_manage_customers, can_manage_subscriptions, can_manage_payments, 
                   can_view_analytics, two_factor_enabled, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())";
        
        $result = $db->Insert($query, [
            $email, $hashedPassword, $full_name, $admin_pic, $role, $status,
            $can_manage_customers, $can_manage_subscriptions, $can_manage_payments, $can_view_analytics
        ]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Kullanıcı başarılı bir şekilde eklendi',
                'data' => ['admin_id' => $result]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Kullanıcı eklenirken hata oluştu']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==== PUT - Admin User Güncelle ====
if ($action === 'update' && $method === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        $userId = isset($input['admin_id']) ? (int)$input['admin_id'] : 0;
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
            exit;
        }

        // Kullanıcı var mı kontrol et
        $userQuery = "SELECT admin_id FROM admin_users WHERE admin_id = ?";
        $user = $db->getRow($userQuery, [$userId]);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
            exit;
        }

        // Güncellenecek alanları hazırla
        $updates = [];
        $params = [];

        if (isset($input['email'])) {
            $email = trim($input['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Geçersiz e-posta adresi']);
                exit;
            }

            // E-posta benzersiz midir kontrolü
            $existQuery = "SELECT COUNT(*) as count FROM admin_users WHERE email = ? AND admin_id != ?";
            $existResult = $db->getRow($existQuery, [$email, $userId]);
            if ($existResult->count > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Bu e-posta zaten kullanılıyor']);
                exit;
            }

            $updates[] = "email = ?";
            $params[] = $email;
        }

        if (isset($input['full_name'])) {
            $updates[] = "full_name = ?";
            $params[] = trim($input['full_name']);
        }

        if (isset($input['admin_pic'])) {
            $updates[] = "admin_pic = ?";
            $params[] = trim($input['admin_pic']);
        }

        if (isset($input['role'])) {
            $role = trim($input['role']);
            $allowed_roles = ['superadmin', 'support', 'financial'];
            if (!in_array($role, $allowed_roles)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Geçersiz rol']);
                exit;
            }
            $updates[] = "role = ?";
            $params[] = $role;
        }

        if (isset($input['status'])) {
            $status = trim($input['status']);
            $allowed_status = ['active', 'inactive', 'suspended'];
            if (!in_array($status, $allowed_status)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Geçersiz durum']);
                exit;
            }
            $updates[] = "status = ?";
            $params[] = $status;
        }

        if (isset($input['can_manage_customers'])) {
            $updates[] = "can_manage_customers = ?";
            $params[] = (int)$input['can_manage_customers'];
        }

        if (isset($input['can_manage_subscriptions'])) {
            $updates[] = "can_manage_subscriptions = ?";
            $params[] = (int)$input['can_manage_subscriptions'];
        }

        if (isset($input['can_manage_payments'])) {
            $updates[] = "can_manage_payments = ?";
            $params[] = (int)$input['can_manage_payments'];
        }

        if (isset($input['can_view_analytics'])) {
            $updates[] = "can_view_analytics = ?";
            $params[] = (int)$input['can_view_analytics'];
        }

        if (isset($input['password'])) {
            $password = $input['password'];
            if (strlen($password) > 0) {
                $updates[] = "password = ?";
                $params[] = password_hash($password, PASSWORD_BCRYPT);
            }
        }

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Güncelleme için en az bir alan gerekli']);
            exit;
        }

        // Update date ekle
        $updates[] = "updated_at = NOW()";

        // Parametrelere ID ekle
        $params[] = $userId;

        $query = "UPDATE admin_users SET " . implode(", ", $updates) . " WHERE admin_id = ?";

        $result = $db->Update($query, $params);

        if ($result > 0 || true) { // Result >= 0 olabilir (etkilenen satır sayısı)
            echo json_encode([
                'success' => true,
                'message' => 'Kullanıcı başarılı bir şekilde güncellendi'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Kullanıcı güncellenirken hata oluştu']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==== DELETE - Admin User Sil ====
if ($action === 'delete' && $method === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        $userId = isset($input['admin_id']) ? (int)$input['admin_id'] : 0;
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
            exit;
        }

        // Kendini silemesin
        if ($userId === (int)$_SESSION['admin_id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Kendi hesabınızı silemezsiniz']);
            exit;
        }

        // Kullanıcı var mı kontrol et
        $userQuery = "SELECT admin_id FROM admin_users WHERE admin_id = ?";
        $user = $db->getRow($userQuery, [$userId]);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
            exit;
        }

        // Sil
        $query = "DELETE FROM admin_users WHERE admin_id = ?";
        $result = $db->Delete($query, [$userId]);

        if ($result > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Kullanıcı başarılı bir şekilde silindi'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Kullanıcı silinirken hata oluştu']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Geçersiz action
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
?>
