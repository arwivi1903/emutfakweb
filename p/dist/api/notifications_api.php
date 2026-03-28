<?php
/**
 * Notifications API Endpoint
 * Handles notification fetching and marking as read
 */

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../classes/database.class.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$admin_id = $_SESSION['admin_id'];

$db = new Database();

try {
    switch($action) {
        case 'get_unread':
            // Get unread notifications for current admin
            $notifications = $db->getRows("
                SELECT *
                FROM notifications
                WHERE (admin_id = ? OR admin_id IS NULL)
                AND is_read = 0
                ORDER BY created_at DESC
                LIMIT 20
            ", [$admin_id]);
            
            $unread_count = $db->getColumn("
                SELECT COUNT(*)
                FROM notifications
                WHERE (admin_id = ? OR admin_id IS NULL)
                AND is_read = 0
            ", [$admin_id]) ?? 0;
            
            echo json_encode([
                'success' => true,
                'data' => $notifications,
                'unread_count' => (int)$unread_count
            ]);
            break;
            
        case 'get_all':
            // Get all notifications (read and unread)
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $notifications = $db->Limit("
                SELECT *
                FROM notifications
                WHERE (admin_id = ? OR admin_id IS NULL)
                ORDER BY created_at DESC
                LIMIT ?
            ", $admin_id, $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $notifications
            ]);
            break;
            
        case 'mark_read':
            // Mark specific notification as read
            $notification_id = $_POST['notification_id'] ?? 0;
            
            if ($notification_id) {
                $db->Update("
                    UPDATE notifications
                    SET is_read = 1, read_at = NOW()
                    WHERE notification_id = ?
                    AND (admin_id = ? OR admin_id IS NULL)
                ", [$notification_id, $admin_id]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Missing notification_id'
                ]);
            }
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read for current admin
            $db->Update("
                UPDATE notifications
                SET is_read = 1, read_at = NOW()
                WHERE (admin_id = ? OR admin_id IS NULL)
                AND is_read = 0
            ", [$admin_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
            break;
            
        case 'create':
            // Create new notification (for testing or system use)
            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $type = $_POST['type'] ?? 'info';
            $target_admin = $_POST['admin_id'] ?? null;
            
            if ($title && $message) {
                $notification_id = $db->Insert("
                    INSERT INTO notifications (admin_id, title, message, type, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ", [$target_admin, $title, $message, $type]);
                
                echo json_encode([
                    'success' => true,
                    'notification_id' => $notification_id
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Missing required fields'
                ]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
