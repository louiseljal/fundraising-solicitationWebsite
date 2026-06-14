<?php
// =============================================
// api/analytics_backend.php
// Pure Operational Database Analytics Engine
// =============================================

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/dw_db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_log(1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'dashboard';

// --- DASHBOARD ACTION ---
if ($action === 'dashboard') {
    // Read optional date filters
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;
    $groupBy = $_GET['group_by'] ?? 'monthly'; // daily, monthly, yearly

    $dateFilterDonations = "";
    $dateFilterUsers = "";
    $dateFilterCollections = "";
    $dateFilterSolicitations = "";
    $dateFilterCampaigns = "";
    $params = [];

    if (!empty($startDate) && !empty($endDate)) {
        // OLAP warehouse uses dim_time for date filtering
        $dateFilterDonations = " AND dt.full_date >= :start_date AND dt.full_date <= :end_date";
        $dateFilterUsers = " AND dd.joined_date >= :start_date AND dd.joined_date <= :end_date";
        $dateFilterCollections = " AND created_at >= :start_date AND created_at <= :end_date";
        $dateFilterSolicitations = " AND created_at >= :start_date AND created_at <= :end_date";
        $dateFilterCampaigns = " AND dc.start_date >= :start_date AND dc.start_date <= :end_date";
        
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }

    // 1. KPIs (Fully converted to data warehouse schema)
    $kpiSql = "
        SELECT
            COUNT(*)                          AS total_donations,
            COALESCE(SUM(fd.donation_amount), 0) AS total_raised,
            COUNT(DISTINCT fd.donor_sk)          AS unique_donors,
            COALESCE(AVG(fd.donation_amount), 0) AS avg_donation,
            COUNT(DISTINCT fd.campaign_sk)       AS active_campaigns
        FROM fact_donations fd
        LEFT JOIN dim_time dt ON fd.time_id = dt.time_id
        WHERE 1=1 {$dateFilterDonations}
    ";
    $stmt = $dw_pdo->prepare($kpiSql);
    $stmt->execute($params);
    $kpis = $stmt->fetch();
    
    // 2. Donation Trend Chart Data (Dynamic aggregation based on Group By filter)
   if ($groupBy === 'daily') {
    $trendSql = "SELECT dt.full_date AS log_date, SUM(fd.donation_amount) AS period_total 
                 FROM fact_donations fd
                 LEFT JOIN dim_time dt ON fd.time_id = dt.time_id
                 WHERE 1=1 {$dateFilterDonations}
                 GROUP BY dt.full_date ORDER BY dt.full_date ASC";
} elseif ($groupBy === 'yearly') {
    $trendSql = "SELECT dt.year AS log_date, SUM(fd.donation_amount) AS period_total 
                 FROM fact_donations fd
                 LEFT JOIN dim_time dt ON fd.time_id = dt.time_id
                 WHERE 1=1 {$dateFilterDonations}
                 GROUP BY dt.year ORDER BY dt.year ASC";
} else { // monthly
    $trendSql = "SELECT CONCAT(dt.year, '-', LPAD(dt.month_num, 2, '0')) AS log_date, SUM(fd.donation_amount) AS period_total 
                 FROM fact_donations fd
                 LEFT JOIN dim_time dt ON fd.time_id = dt.time_id
                 WHERE 1=1 {$dateFilterDonations}
                 GROUP BY dt.year, dt.month_num ORDER BY dt.year, dt.month_num ASC";
}

$stmt = $dw_pdo->prepare($trendSql);
    $stmt->execute($params);
    $trendRaw = $stmt->fetchAll();

    $trendLabels = [];
    $trendValues = [];
    foreach ($trendRaw as $t) {
        if ($groupBy === 'daily') {
            $trendLabels[] = date('M d, Y', strtotime($t['log_date']));
        } elseif ($groupBy === 'yearly') {
            $trendLabels[] = (string)$t['log_date'];
        } else {
            $trendLabels[] = date('F Y', strtotime($t['log_date'] . '-01'));
        }
        $trendValues[] = (float)$t['period_total'];
    }

    // 3. User Registration Trend Chart Data
   if ($groupBy === 'daily') {
    $userTrendSql = "SELECT dd.joined_date AS log_date, COUNT(*) AS period_total 
                     FROM dim_donor dd WHERE 1=1 {$dateFilterUsers} 
                     GROUP BY dd.joined_date ORDER BY dd.joined_date ASC";
} elseif ($groupBy === 'yearly') {
    $userTrendSql = "SELECT YEAR(dd.joined_date) AS log_date, COUNT(*) AS period_total 
                     FROM dim_donor dd WHERE 1=1 {$dateFilterUsers} 
                     GROUP BY YEAR(dd.joined_date) ORDER BY log_date ASC";
} else { // monthly
    $userTrendSql = "SELECT DATE_FORMAT(dd.joined_date, '%Y-%m') AS log_date, COUNT(*) AS period_total 
                     FROM dim_donor dd WHERE 1=1 {$dateFilterUsers} 
                     GROUP BY DATE_FORMAT(dd.joined_date, '%Y-%m') ORDER BY log_date ASC";
}

$stmt = $dw_pdo->prepare($userTrendSql); 
    $stmt->execute($params);
    $userTrendRaw = $stmt->fetchAll();

    $userTrendLabels = [];
    $userTrendValues = [];
    foreach ($userTrendRaw as $t) {
        if ($groupBy === 'daily') {
            $userTrendLabels[] = date('M d, Y', strtotime($t['log_date']));
        } elseif ($groupBy === 'yearly') {
            $userTrendLabels[] = (string)$t['log_date'];
        } else {
            $userTrendLabels[] = date('F Y', strtotime($t['log_date'] . '-01'));
        }
        $userTrendValues[] = (int)$t['period_total'];
    }

    // 4. Category Distribution Chart
    $categorySql = "
    SELECT
        dc.category,
        COALESCE(SUM(fd.donation_amount), 0) AS total_raised
    FROM dim_campaign dc
    LEFT JOIN fact_donations fd ON dc.campaign_sk = fd.campaign_sk
    LEFT JOIN dim_time dt ON fd.time_id = dt.time_id
    WHERE 1=1 {$dateFilterDonations}
    GROUP BY dc.category
    ORDER BY total_raised DESC
    LIMIT 4
";
$stmt = $dw_pdo->prepare($categorySql);
    $stmt->execute($params);
    $categoriesRaw = $stmt->fetchAll();

    $categoryLabels = [];
    $categoryValues = [];
    foreach ($categoriesRaw as $cat) {
        $categoryLabels[] = $cat['category'];
        $categoryValues[] = (float)$cat['total_raised'];
    }
    
    // 5. Active Members (Converted to OLAP data warehouse)
    $stmt = $dw_pdo->prepare("
        SELECT COUNT(*) AS total
        FROM dim_donor
        WHERE user_role='Active' {$dateFilterUsers}
    ");
    $stmt->execute($params);
    $activeMembers = $stmt->fetch();

    // 6. Registered Users in Filter Period (Converted to OLAP data warehouse)
    $stmt = $dw_pdo->prepare("
        SELECT COUNT(*) AS total
        FROM dim_donor
        WHERE 1=1 {$dateFilterUsers}
    ");
    $stmt->execute($params);
    $monthlyMembers = $stmt->fetch();

    // 7. Campaign Performance (Pivoted to utilize your summary table: fact_campaign_performance)
    $campaignPerformance = $dw_pdo->query("
        SELECT
            dc.title,
            dc.goal_amount,
            fcp.total_raised AS current_raised_cache,
            fcp.progress_pct
        FROM fact_campaign_performance fcp
        JOIN dim_campaign dc ON fcp.campaign_sk = dc.campaign_sk
    ")->fetchAll();

    // 8. Top Solicitors (Pivoted to pull from fact_donations and dim_donor)
    $topSolicitorSql = "
        SELECT
            dd.full_name,
            SUM(fd.donation_amount) AS total_funds
        FROM fact_donations fd
        JOIN dim_donor dd ON fd.donor_sk = dd.donor_sk
        LEFT JOIN dim_time dt ON fd.time_id = dt.time_id
        WHERE 1=1 {$dateFilterDonations}
        GROUP BY fd.donor_sk, dd.full_name
        ORDER BY total_funds DESC
        LIMIT 5
    ";
    $stmt = $dw_pdo->prepare($topSolicitorSql);
    $stmt->execute($params);
    $topSolicitors = $stmt->fetchAll();

    // 9. Total Collections (Keep operational database connection $pdo since OLAP doesn't track logistics/audit logs)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total FROM collections WHERE is_deleted = 0 {$dateFilterCollections}
    ");
    $stmt->execute($params);
    $totalCollections = $stmt->fetch()['total'];

    // 10. Total Solicitations (Keep operational database connection $pdo since OLAP doesn't track workflow tickets)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total FROM solicitations WHERE is_deleted = 0 {$dateFilterSolicitations}
    ");
    $stmt->execute($params);
    $totalSolicitations = $stmt->fetch()['total'];

    // 11. Payment Method Breakdown (Converted to OLAP data warehouse)
    $paymentSql = "
        SELECT 
            dpm.method_name AS payment_method, 
            COALESCE(SUM(fd.donation_amount), 0) AS total_amount, 
            COUNT(*) AS transaction_count
        FROM fact_donations fd
        LEFT JOIN dim_time dt ON fd.time_id = dt.time_id
        JOIN dim_payment_method dpm ON fd.payment_method_id = dpm.payment_method_id
        WHERE 1=1 {$dateFilterDonations}
        GROUP BY dpm.method_name
    ";
    $stmt = $dw_pdo->prepare($paymentSql);
    $stmt->execute($params);
    $paymentMethodBreakdown = $stmt->fetchAll();

    // 12. Completed Campaigns Trend Chart Data (Pivoted to dim_campaign)
    if ($groupBy === 'daily') {
        $compTrendSql = "SELECT dc.start_date AS log_date, COUNT(*) AS period_total 
                         FROM dim_campaign dc WHERE dc.status='Completed' {$dateFilterCampaigns}
                         GROUP BY dc.start_date ORDER BY dc.start_date ASC";
    } elseif ($groupBy === 'yearly') {
        $compTrendSql = "SELECT YEAR(dc.start_date) AS log_date, COUNT(*) AS period_total 
                         FROM dim_campaign dc WHERE dc.status='Completed' {$dateFilterCampaigns}
                         GROUP BY YEAR(dc.start_date) ORDER BY log_date ASC";
    } else { // monthly
        $compTrendSql = "SELECT DATE_FORMAT(dc.start_date, '%Y-%m') AS log_date, COUNT(*) AS period_total 
                         FROM dim_campaign dc WHERE dc.status='Completed' {$dateFilterCampaigns}
                         GROUP BY DATE_FORMAT(dc.start_date, '%Y-%m') ORDER BY log_date ASC";
    }

    $stmt = $dw_pdo->prepare($compTrendSql);
    $stmt->execute($params);
    $compTrendRaw = $stmt->fetchAll();

    $compTrendLabels = [];
    $compTrendValues = [];
    foreach ($compTrendRaw as $t) {
        if ($groupBy === 'daily') {
            $compTrendLabels[] = date('M d, Y', strtotime($t['log_date']));
        } elseif ($groupBy === 'yearly') {
            $compTrendLabels[] = (string)$t['log_date'];
        } else {
            $compTrendLabels[] = date('F Y', strtotime($t['log_date'] . '-01'));
        }
        $compTrendValues[] = (int)$t['period_total'];
    }

    echo json_encode([
        'success' => true,
        'kpis' => $kpis,
        'charts' => [
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
            'categoryLabels' => $categoryLabels,
            'categoryValues' => $categoryValues,
            'userTrendLabels' => $userTrendLabels,
            'userTrendValues' => $userTrendValues,
            'completedTrendLabels' => $compTrendLabels,
            'completedTrendValues' => $compTrendValues
        ],
        'activeMembers' => $activeMembers['total'],
        'monthlyMembers' => $monthlyMembers['total'],
        'campaignPerformance' => $campaignPerformance,
        'topSolicitors' => $topSolicitors,
        'additionalData' => [
            'totalCollections' => (int)$totalCollections,
            'totalSolicitations' => (int)$totalSolicitations,
            'paymentMethodBreakdown' => $paymentMethodBreakdown
        ]
    ]);
    exit;
}

// --- DECISION ENGINE ACTION ---
if ($action === 'decision') {
    $stmt = $dw_pdo->query("
        SELECT
            dc.campaign_id, dc.title, dc.goal_amount, dc.end_date,
            DATEDIFF(dc.end_date, CURDATE()) AS days_remaining,
            fcp.total_raised,
            fcp.progress_pct
        FROM dim_campaign dc
        LEFT JOIN fact_campaign_performance fcp ON dc.campaign_sk = fcp.campaign_sk
        WHERE dc.status = 'Active'
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

echo json_encode(['success' => false, 'message' => 'Invalid action endpoint.']);