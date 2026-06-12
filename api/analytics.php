<?php
// =============================================
// api/analytics.php
// OLAP Engine - Corrected Syntax Version
// =============================================

require_once '../includes/session.php';
require_once '../includes/db.php';

// Try to load data warehouse connection, but handle gracefully if it fails
$dw_pdo = null;
try {
    require_once '../includes/dw_db.php';
} catch (Exception $e) {
    // Data warehouse not available, will use operational database fallback
    error_log("Data warehouse connection failed: " . $e->getMessage());
    $dw_pdo = null;
}

header('Content-Type: application/json');

// Public dashboard JSON for charts/widgets
$action = $_GET['action'] ?? 'dashboard';

// --- DASHBOARD ACTION ---
if ($action === 'dashboard') {
    // Standardize on OLTP database for consistency across all APIs
    $kpis = $pdo->query("
        SELECT
            COUNT(donation_id)           AS total_donations,
            COALESCE(SUM(amount), 0)     AS total_raised,
            COUNT(DISTINCT user_id)      AS unique_donors,
            COALESCE(AVG(amount), 0)     AS avg_donation,
            COUNT(DISTINCT campaign_id)  AS active_campaigns
        FROM donations
        WHERE payment_status = 'Completed' AND is_deleted = 0
    ")->fetch();

    $trend = $pdo->query("
        SELECT
            YEAR(created_at) AS year,
            MONTH(created_at) AS month_num,
            MONTHNAME(created_at) AS month_name,
            SUM(amount) AS monthly_total
        FROM donations
        WHERE payment_status = 'Completed' AND is_deleted = 0
        GROUP BY YEAR(created_at), MONTH(created_at), MONTHNAME(created_at)
        ORDER BY YEAR(created_at), MONTH(created_at)
    ")->fetchAll();
    
    // Active Members
$activeMembers = $pdo->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE account_status='Active'
    AND is_deleted=0
")->fetch();

// Members This Month
$monthlyMembers = $pdo->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE MONTH(created_at)=MONTH(CURRENT_DATE())
    AND YEAR(created_at)=YEAR(CURRENT_DATE())
")->fetch();

// Campaign Performance
$campaignPerformance = $pdo->query("
    SELECT
        title,
        goal_amount,
        current_raised_cache,
        ROUND(
            (current_raised_cache / NULLIF(goal_amount, 0)) * 100,
            2
        ) AS progress_pct
    FROM campaigns
    WHERE campaign_status='Active' AND is_deleted=0
")->fetchAll();

// Top Donors (used as Top Solicitors)
$topSolicitors = $pdo->query("
    SELECT
        CONCAT(up.first_name,' ',up.last_name) AS full_name,
        SUM(d.amount) AS total_funds
    FROM donations d
    JOIN user_profiles up
        ON d.user_id = up.user_id
    WHERE d.payment_status='Completed' AND d.is_deleted = 0
    GROUP BY d.user_id
    ORDER BY total_funds DESC
    LIMIT 5
")->fetchAll();

   echo json_encode([
    'success' => true,
    'kpis' => $kpis,
    'trend' => $trend,
    'activeMembers' => $activeMembers['total'],
    'monthlyMembers' => $monthlyMembers['total'],
    'campaignPerformance' => $campaignPerformance,
    'topSolicitors' => $topSolicitors
]);
    exit;
}

// --- DECISION ENGINE ACTION ---
if ($action === 'decision') {
    // Standardize on OLTP database for consistency across all APIs
    $stmt = $pdo->query("
        SELECT
            c.campaign_id, c.title, c.goal_amount, c.end_date,
            DATEDIFF(c.end_date, CURDATE()) AS days_remaining,
            COALESCE(SUM(CASE WHEN d.payment_status = 'Completed' THEN d.amount ELSE 0 END), 0) AS total_raised,
            ROUND(COALESCE(SUM(CASE WHEN d.payment_status = 'Completed' THEN d.amount ELSE 0 END), 0) / NULLIF(c.goal_amount, 0) * 100, 2) AS progress_pct
        FROM campaigns c
        LEFT JOIN donations d ON c.campaign_id = d.campaign_id AND d.is_deleted = 0
        WHERE c.campaign_status = 'Active' AND c.is_deleted = 0
        GROUP BY c.campaign_id, c.title, c.goal_amount, c.end_date
    ");
    $campaigns = $stmt->fetchAll();
    
    foreach ($campaigns as &$c) {
        $pct = (float)$c['progress_pct'];
        $days = (int)$c['days_remaining'];

        if ($pct >= 100) {
            $c['message'] = 'Goal achieved!';
        } elseif ($days <= 3 && $pct < 50) {
            $c['message'] = 'Needs urgent promotion!';
        } else {
            $c['message'] = 'Keep up the momentum.';
        }
    }

    echo json_encode(['success' => true, 'decisions' => $campaigns]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
