<?php
/**
 * Dashboard API Endpoint
 * Provides AJAX endpoints for dashboard data
 */

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../functions/analytics_functions.php';

$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'get_metrics':
            // Get all dashboard metrics
            $response = [
                'success' => true,
                'data' => [
                    'mrr' => getMRRMetrics(),
                    'arr' => getARRMetrics(),
                    'churn' => getChurnRate('month'),
                    'conversion' => getConversionMetrics(),
                    'comparative' => getComparativeMetrics()
                ]
            ];
            break;
            
        case 'get_revenue_chart':
            $period = $_GET['period'] ?? '30days';
            $response = [
                'success' => true,
                'data' => getRevenueChartData($period)
            ];
            break;
            
        case 'get_customer_growth':
            $period = $_GET['period'] ?? '12months';
            $response = [
                'success' => true,
                'data' => getCustomerGrowthData($period)
            ];
            break;
            
        case 'get_system_health':
            $response = [
                'success' => true,
                'data' => getSystemHealthMetrics()
            ];
            break;
            
        case 'get_ticket_analytics':
            $response = [
                'success' => true,
                'data' => getTicketAnalytics()
            ];
            break;
            
        case 'get_email_queue':
            $response = [
                'success' => true,
                'data' => getEmailQueueStatus()
            ];
            break;
            
        case 'get_webhook_stats':
            $response = [
                'success' => true,
                'data' => getWebhookStats()
            ];
            break;
            
        case 'get_customer_segmentation':
            $response = [
                'success' => true,
                'data' => getCustomerSegmentation()
            ];
            break;
            
        case 'get_payment_methods':
            $response = [
                'success' => true,
                'data' => getPaymentMethodDistribution()
            ];
            break;
            
        case 'get_top_customers':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $response = [
                'success' => true,
                'data' => getTopCustomersByRevenue($limit)
            ];
            break;
            
        case 'get_coupon_stats':
            $response = [
                'success' => true,
                'data' => getCouponUsageStats()
            ];
            break;
            
        default:
            http_response_code(400);
            $response = [
                'success' => false,
                'error' => 'Invalid action'
            ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
