<?php
// =============================================
// api/analytics.php
// OLAP Engine - Corrected Syntax Version
// =============================================

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/dw_db.php';
header('Content-Type: application/json');

// Public dashboard JSON for charts/widgets
$action = $_GET['action'] ?? 'dashboard';

// --- DASHBOARD ACTION ---
if ($action === 'dashboard') {
    $kpis = $dw_pdo->query("
        SELECT
            COUNT(fact_id)              AS total_donations,
            COALESCE(SUM(donation_amount), 0)  AS total_raised,
            COUNT(DISTINCT donor_sk)    AS unique_donors,
            COALESCE(AVG(donation_amount), 0)  AS avg_donation,
            COUNT(DISTINCT campaign_sk) AS active_campaigns
        FROM fact_donations
    ")->fetch();

    $trend = $dw_pdo->query("
        SELECT
            dt.year, dt.month_num, dt.month_name,
            SUM(fd.donation_amount)     AS monthly_total
        FROM fact_donations fd
        JOIN dim_time dt ON fd.time_id = dt.time_id
        GROUP BY dt.year, dt.month_num, dt.month_name
        ORDER BY dt.year, dt.month_num
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
    WHERE campaign_status='Active'
")->fetchAll();

// Top Donors (used as Top Solicitors)
$topSolicitors = $pdo->query("
    SELECT
        CONCAT(up.first_name,' ',up.last_name) AS full_name,
        SUM(d.amount) AS total_funds
    FROM donations d
    JOIN user_profiles up
        ON d.user_id = up.user_id
    WHERE d.payment_status='Completed'
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

// --- DECISION ENGINE ACTION (Where the error was likely hiding) ---
if ($action === 'decision') {
    $stmt = $dw_pdo->query("
        SELECT
            dc.campaign_id, dc.title, dc.goal_amount, dc.end_date,
            DATEDIFF(dc.end_date, CURDATE()) AS days_remaining,
            COALESCE(SUM(fd.donation_amount), 0) AS total_raised,
            ROUND(COALESCE(SUM(fd.donation_amount), 0) / NULLIF(dc.goal_amount, 0) * 100, 2) AS progress_pct
        FROM dim_campaign dc
        LEFT JOIN fact_donations fd ON dc.campaign_sk = fd.campaign_sk
        WHERE dc.status = 'Active'
        GROUP BY dc.campaign_sk, dc.campaign_id, dc.title, dc.goal_amount, dc.end_date
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
