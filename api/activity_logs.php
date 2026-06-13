<?php
// =============================================
// api/activity_logs.php
// Consolidated Database Activity History Engine
// =============================================

require_once '../includes/session.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Read optional filters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$period = $_GET['period'] ?? 'all';
$activityType = $_GET['activity_type'] ?? 'all';

$whereClauses = [];
$params = [];

// Timeframe preset filter (Daily, Monthly, Yearly)
if ($period !== 'all') {
    $now = new DateTime();
    if ($period === 'daily') {
        $whereClauses[] = "created_at >= :start_date AND created_at <= :end_date";
        $params[':start_date'] = $now->format('Y-m-d 00:00:00');
        $params[':end_date'] = $now->format('Y-m-d 23:59:59');
    } elseif ($period === 'monthly') {
        $whereClauses[] = "created_at >= :start_date AND created_at <= :end_date";
        $params[':start_date'] = $now->format('Y-m-01 00:00:00');
        $params[':end_date'] = $now->format('Y-m-t 23:59:59');
    } elseif ($period === 'yearly') {
        $whereClauses[] = "created_at >= :start_date AND created_at <= :end_date";
        $params[':start_date'] = $now->format('Y-01-01 00:00:00');
        $params[':end_date'] = $now->format('Y-12-31 23:59:59');
    }
} 
// Manual Date Range Filter (Overrides preset if both are used)
elseif (!empty($startDate) && !empty($endDate)) {
    $whereClauses[] = "created_at >= :start_date AND created_at <= :end_date";
    $params[':start_date'] = $startDate . ' 00:00:00';
    $params[':end_date'] = $endDate . ' 23:59:59';
}

// Activity Type (Table/Module) Filter
if ($activityType !== 'all') {
    $whereClauses[] = "activity_type = :activity_type";
    $params[':activity_type'] = $activityType;
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Consolidate activity logs via UNION ALL across donations, campaigns, and users tables
$sql = "
    SELECT * FROM (
        SELECT 
            'Donation' AS activity_type,
            CONCAT('Ref: ', transaction_reference, ' | Amount: ₱', amount, ' | Status: ', payment_status) AS details,
            created_at
        FROM donations
        WHERE is_deleted = 0
        
        UNION ALL
        
        SELECT 
            'Campaign' AS activity_type,
            CONCAT('Title: \"', title, '\" | Goal: ₱', goal_amount, ' | Status: ', campaign_status) AS details,
            created_at
        FROM campaigns
        WHERE is_deleted = 0
        
        UNION ALL
        
        SELECT 
            'User' AS activity_type,
            CONCAT('Username: ', username, ' | Email: ', email, ' | Role: ', user_role) AS details,
            created_at
        FROM users
        WHERE is_deleted = 0
    ) AS activity_stream
    {$whereSql}
    ORDER BY created_at DESC
    LIMIT 250
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'logs' => $logs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}