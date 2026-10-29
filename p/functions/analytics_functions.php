<?php
/**
 * Analytics Functions for Dashboard
 * Provides data fetching methods for charts, metrics, and monitoring
 */

require_once __DIR__ . '/../classes/database.class.php';

/**
 * Get revenue chart data for specified period
 * @param string $period - '7days', '30days', '90days', '12months'
 * @return array - Chart data with labels and values
 */
function getRevenueChartData($period = '30days') {
    $db = new Database();
    
    $dateFormat = '%Y-%m-%d';
    $interval = 30;
    $groupBy = 'DATE(payment_date)';
    
    switch($period) {
        case '7days':
            $interval = 7;
            break;
        case '90days':
            $interval = 90;
            break;
        case '12months':
            $interval = 365;
            $dateFormat = '%Y-%m';
            $groupBy = 'DATE_FORMAT(payment_date, "%Y-%m")';
            break;
        default:
            $interval = 30;
    }
    
    $query = "
        SELECT 
            DATE_FORMAT(payment_date, '$dateFormat') as date_label,
            SUM(amount) as total_revenue,
            COUNT(*) as payment_count
        FROM payments
        WHERE payment_status = 'completed'
        AND payment_date >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)
        GROUP BY $groupBy
        ORDER BY payment_date ASC
    ";
    
    $results = $db->getRows($query);
    
    $labels = [];
    $data = [];
    $counts = [];
    
    foreach($results as $row) {
        $labels[] = $row->date_label;
        $data[] = (float)$row->total_revenue;
        $counts[] = (int)$row->payment_count;
    }
    
    return [
        'labels' => $labels,
        'revenue' => $data,
        'counts' => $counts,
        'total' => array_sum($data),
        'average' => count($data) > 0 ? array_sum($data) / count($data) : 0
    ];
}

/**
 * Get customer growth data
 * @param string $period - '6months', '12months', '24months'
 * @return array - Customer growth chart data
 */
function getCustomerGrowthData($period = '12months') {
    $db = new Database();
    
    $months = 12;
    switch($period) {
        case '6months':
            $months = 6;
            break;
        case '24months':
            $months = 24;
            break;
    }
    
    $query = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month_label,
            COUNT(*) as new_customers,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_customers
        FROM customers
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
        AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at ASC
    ";
    
    $results = $db->getRows($query);
    
    $labels = [];
    $newCustomers = [];
    $activeCustomers = [];
    
    foreach($results as $row) {
        $labels[] = $row->month_label;
        $newCustomers[] = (int)$row->new_customers;
        $activeCustomers[] = (int)$row->active_customers;
    }
    
    return [
        'labels' => $labels,
        'new_customers' => $newCustomers,
        'active_customers' => $activeCustomers,
        'total_new' => array_sum($newCustomers)
    ];
}

/**
 * Get Monthly Recurring Revenue (MRR) metrics
 * @return array - MRR data and trends
 */
function getMRRMetrics() {
    $db = new Database();
    
    // Current month MRR
    $currentMRR = $db->getColumn("
        SELECT SUM(price_per_month) 
        FROM subscriptions 
        WHERE status = 'active'
    ") ?? 0;
    
    // Previous month MRR
    $previousMRR = $db->getColumn("
        SELECT SUM(price_per_month)
        FROM subscriptions
        WHERE status = 'active'
        AND start_date < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    ") ?? 0;
    
    // Calculate growth
    $growth = $previousMRR > 0 ? (($currentMRR - $previousMRR) / $previousMRR) * 100 : 0;
    
    return [
        'current_mrr' => (float)$currentMRR,
        'previous_mrr' => (float)$previousMRR,
        'growth_percentage' => round($growth, 2),
        'growth_amount' => (float)($currentMRR - $previousMRR)
    ];
}

/**
 * Get Annual Recurring Revenue (ARR) metrics
 * @return array - ARR data
 */
function getARRMetrics() {
    $mrr = getMRRMetrics();
    return [
        'current_arr' => $mrr['current_mrr'] * 12,
        'previous_arr' => $mrr['previous_mrr'] * 12,
        'growth_percentage' => $mrr['growth_percentage']
    ];
}

/**
 * Get churn rate for specified period
 * @param string $period - 'month', 'quarter', 'year'
 * @return array - Churn rate data
 */
function getChurnRate($period = 'month') {
    $db = new Database();
    
    $interval = '1 MONTH';
    switch($period) {
        case 'quarter':
            $interval = '3 MONTH';
            break;
        case 'year':
            $interval = '12 MONTH';
            break;
    }
    
    // Customers at start of period
    $startCustomers = $db->getColumn("
        SELECT COUNT(*) 
        FROM customers 
        WHERE status = 'active'
        AND created_at < DATE_SUB(CURDATE(), INTERVAL $interval)
    ") ?? 0;
    
    // Customers who churned in period
    $churnedCustomers = $db->getColumn("
        SELECT COUNT(*)
        FROM customers
        WHERE status IN ('passive', 'suspended')
        AND updated_at >= DATE_SUB(CURDATE(), INTERVAL $interval)
    ") ?? 0;
    
    $churnRate = $startCustomers > 0 ? ($churnedCustomers / $startCustomers) * 100 : 0;
    
    return [
        'churn_rate' => round($churnRate, 2),
        'churned_count' => (int)$churnedCustomers,
        'start_count' => (int)$startCustomers,
        'period' => $period
    ];
}

/**
 * Get conversion funnel metrics
 * @return array - Conversion data
 */
function getConversionMetrics() {
    $db = new Database();
    
    // Total customers (all statuses)
    $totalCustomers = $db->getColumn("SELECT COUNT(*) FROM customers WHERE deleted_at IS NULL") ?? 0;
    
    // Trial customers
    $trialCustomers = $db->getColumn("
        SELECT COUNT(*) FROM customers 
        WHERE trial_end_date >= CURDATE() 
        AND deleted_at IS NULL
    ") ?? 0;
    
    // Paid customers (with active subscription)
    $paidCustomers = $db->getColumn("
        SELECT COUNT(DISTINCT customer_id) 
        FROM subscriptions 
        WHERE status = 'active'
    ") ?? 0;
    
    // Conversion rates
    $trialToPaidRate = $trialCustomers > 0 ? ($paidCustomers / $trialCustomers) * 100 : 0;
    
    return [
        'total_customers' => (int)$totalCustomers,
        'trial_customers' => (int)$trialCustomers,
        'paid_customers' => (int)$paidCustomers,
        'trial_to_paid_rate' => round($trialToPaidRate, 2)
    ];
}

/**
 * Get customer segmentation data
 * @return array - Segmentation by plan, status, etc.
 */
function getCustomerSegmentation() {
    $db = new Database();
    
    // By plan
    $byPlan = $db->getRows("
        SELECT 
            s.plan_name,
            COUNT(DISTINCT s.customer_id) as customer_count,
            SUM(s.price_per_month) as total_revenue
        FROM subscriptions s
        WHERE s.status = 'active'
        GROUP BY s.plan_name
    ");
    
    // By status
    $byStatus = $db->getRows("
        SELECT 
            status,
            COUNT(*) as count
        FROM customers
        WHERE deleted_at IS NULL
        GROUP BY status
    ");
    
    // By industry
    $byIndustry = $db->getRows("
        SELECT 
            industry,
            COUNT(*) as count
        FROM customers
        WHERE deleted_at IS NULL
        AND industry IS NOT NULL
        GROUP BY industry
        ORDER BY count DESC
        LIMIT 10
    ");
    
    return [
        'by_plan' => $byPlan,
        'by_status' => $byStatus,
        'by_industry' => $byIndustry
    ];
}

/**
 * Get support ticket analytics
 * @return array - Ticket metrics and stats
 */
function getTicketAnalytics() {
    $db = new Database();
    
    // Total tickets by status
    $byStatus = $db->getRows("
        SELECT 
            status,
            COUNT(*) as count
        FROM support_tickets
        GROUP BY status
    ");
    
    // By priority
    $byPriority = $db->getRows("
        SELECT 
            priority,
            COUNT(*) as count
        FROM support_tickets
        WHERE status != 'closed'
        GROUP BY priority
        ORDER BY FIELD(priority, 'high', 'medium', 'low')
    ");
    
    // Average response time (mock - would need ticket_replies table)
    $avgResponseTime = 2.5; // hours
    
    // Resolution rate
    $totalTickets = $db->getColumn("SELECT COUNT(*) FROM support_tickets") ?? 0;
    $resolvedTickets = $db->getColumn("SELECT COUNT(*) FROM support_tickets WHERE status = 'closed'") ?? 0;
    $resolutionRate = $totalTickets > 0 ? ($resolvedTickets / $totalTickets) * 100 : 0;
    
    // Recent tickets
    $recentTickets = $db->getRows("
        SELECT 
            t.*,
            c.company_name
        FROM support_tickets t
        LEFT JOIN customers c ON t.customer_id = c.customer_id
        ORDER BY t.created_at DESC
        LIMIT 10
    ");
    
    return [
        'by_status' => $byStatus,
        'by_priority' => $byPriority,
        'avg_response_time' => $avgResponseTime,
        'resolution_rate' => round($resolutionRate, 2),
        'recent_tickets' => $recentTickets,
        'total_tickets' => (int)$totalTickets,
        'resolved_tickets' => (int)$resolvedTickets
    ];
}

/**
 * Get email queue status
 * @return array - Email queue metrics
 */
function getEmailQueueStatus() {
    $db = new Database();
    
    $pending = $db->getColumn("SELECT COUNT(*) FROM email_queue WHERE status = 'pending'") ?? 0;
    $sent = $db->getColumn("SELECT COUNT(*) FROM email_queue WHERE status = 'sent' AND sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)") ?? 0;
    $failed = $db->getColumn("SELECT COUNT(*) FROM email_queue WHERE status = 'failed'") ?? 0;
    
    // Recent failed emails
    $recentFailed = $db->getRows("
        SELECT *
        FROM email_queue
        WHERE status = 'failed'
        ORDER BY updated_at DESC
        LIMIT 5
    ");
    
    return [
        'pending' => (int)$pending,
        'sent_today' => (int)$sent,
        'failed' => (int)$failed,
        'recent_failed' => $recentFailed,
        'health_status' => $failed > 10 ? 'warning' : 'healthy'
    ];
}

/**
 * Get webhook statistics
 * @return array - Webhook metrics
 */
function getWebhookStats() {
    $db = new Database();
    
    // Check if webhook_logs table exists
    if (!$db->tableExists('webhook_logs')) {
        return [
            'total_calls' => 0,
            'success_rate' => 0,
            'failed_calls' => 0,
            'avg_response_time' => 0
        ];
    }
    
    $totalCalls = $db->getColumn("
        SELECT COUNT(*) 
        FROM webhook_logs 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ") ?? 0;
    
    $successCalls = $db->getColumn("
        SELECT COUNT(*) 
        FROM webhook_logs 
        WHERE status = 'success' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ") ?? 0;
    
    $successRate = $totalCalls > 0 ? ($successCalls / $totalCalls) * 100 : 0;
    
    return [
        'total_calls' => (int)$totalCalls,
        'success_rate' => round($successRate, 2),
        'failed_calls' => (int)($totalCalls - $successCalls),
        'avg_response_time' => 0.5 // seconds (mock)
    ];
}

/**
 * Get system health metrics
 * @return array - System health data
 */
function getSystemHealthMetrics() {
    $db = new Database();
    
    // Database connection status
    $dbStatus = 'online';
    
    // Recent errors
    $errorCount = $db->getColumn("
        SELECT COUNT(*) 
        FROM system_errors 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND resolved_at IS NULL
    ") ?? 0;
    
    // Active sessions
    $activeSessions = $db->getColumn("
        SELECT COUNT(*) 
        FROM sessions 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ") ?? 0;
    
    // Email queue health
    $emailHealth = getEmailQueueStatus();
    
    // Overall health score
    $healthScore = 100;
    if ($errorCount > 5) $healthScore -= 20;
    if ($emailHealth['failed'] > 10) $healthScore -= 15;
    
    return [
        'database_status' => $dbStatus,
        'error_count_24h' => (int)$errorCount,
        'active_sessions' => (int)$activeSessions,
        'email_queue_health' => $emailHealth['health_status'],
        'overall_health_score' => $healthScore,
        'status' => $healthScore >= 80 ? 'healthy' : ($healthScore >= 60 ? 'warning' : 'critical')
    ];
}

/**
 * Get top customers by revenue
 * @param int $limit - Number of customers to return
 * @return array - Top customers
 */
function getTopCustomersByRevenue($limit = 10) {
    $db = new Database();
    
    $query = "
        SELECT 
            c.customer_id,
            c.company_name,
            c.status,
            COUNT(p.payment_id) as payment_count,
            SUM(p.amount) as total_revenue,
            MAX(p.payment_date) as last_payment_date
        FROM customers c
        LEFT JOIN payments p ON c.customer_id = p.customer_id AND p.payment_status = 'completed'
        WHERE c.deleted_at IS NULL
        GROUP BY c.customer_id
        ORDER BY total_revenue DESC
        LIMIT ?
    ";
    
    return $db->Limit($query, $limit);
}

/**
 * Get payment method distribution
 * @return array - Payment method stats
 */
function getPaymentMethodDistribution() {
    $db = new Database();
    
    $results = $db->getRows("
        SELECT 
            payment_method,
            COUNT(*) as count,
            SUM(amount) as total_amount
        FROM payments
        WHERE payment_status = 'completed'
        AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY payment_method
    ");
    
    $labels = [];
    $counts = [];
    $amounts = [];
    
    foreach($results as $row) {
        $labels[] = $row->payment_method;
        $counts[] = (int)$row->count;
        $amounts[] = (float)$row->total_amount;
    }
    
    return [
        'labels' => $labels,
        'counts' => $counts,
        'amounts' => $amounts
    ];
}

/**
 * Get coupon usage statistics
 * @return array - Coupon stats
 */
function getCouponUsageStats() {
    $db = new Database();
    
    $activeCoupons = $db->getColumn("SELECT COUNT(*) FROM coupons WHERE is_active = 1") ?? 0;
    $totalUsage = $db->getColumn("SELECT SUM(used_count) FROM coupons") ?? 0;
    
    $topCoupons = $db->getRows("
        SELECT 
            code,
            discount_type,
            discount_value,
            used_count,
            usage_limit
        FROM coupons
        WHERE is_active = 1
        ORDER BY used_count DESC
        LIMIT 5
    ");
    
    return [
        'active_coupons' => (int)$activeCoupons,
        'total_usage' => (int)$totalUsage,
        'top_coupons' => $topCoupons
    ];
}

/**
 * Get comparative metrics (current vs previous period)
 * @return array - Comparative data
 */
function getComparativeMetrics() {
    $db = new Database();
    
    // Current month
    $currentMonthRevenue = $db->getColumn("
        SELECT SUM(amount) 
        FROM payments 
        WHERE payment_status = 'completed'
        AND MONTH(payment_date) = MONTH(CURDATE())
        AND YEAR(payment_date) = YEAR(CURDATE())
    ") ?? 0;
    
    // Previous month
    $previousMonthRevenue = $db->getColumn("
        SELECT SUM(amount)
        FROM payments
        WHERE payment_status = 'completed'
        AND MONTH(payment_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        AND YEAR(payment_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    ") ?? 0;
    
    // Calculate percentage change
    $revenueChange = $previousMonthRevenue > 0 
        ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 
        : 0;
    
    // Customer growth
    $currentMonthCustomers = $db->getColumn("
        SELECT COUNT(*) 
        FROM customers 
        WHERE MONTH(created_at) = MONTH(CURDATE())
        AND YEAR(created_at) = YEAR(CURDATE())
    ") ?? 0;
    
    $previousMonthCustomers = $db->getColumn("
        SELECT COUNT(*)
        FROM customers
        WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    ") ?? 0;
    
    $customerChange = $previousMonthCustomers > 0
        ? (($currentMonthCustomers - $previousMonthCustomers) / $previousMonthCustomers) * 100
        : 0;
    
    return [
        'revenue' => [
            'current' => (float)$currentMonthRevenue,
            'previous' => (float)$previousMonthRevenue,
            'change_percentage' => round($revenueChange, 2),
            'trend' => $revenueChange >= 0 ? 'up' : 'down'
        ],
        'customers' => [
            'current' => (int)$currentMonthCustomers,
            'previous' => (int)$previousMonthCustomers,
            'change_percentage' => round($customerChange, 2),
            'trend' => $customerChange >= 0 ? 'up' : 'down'
        ]
    ];
}

?>
