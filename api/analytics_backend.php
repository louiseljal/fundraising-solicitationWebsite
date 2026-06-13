<?php
// =============================================
// api/analytics_backend.php
// Pure Operational Database Analytics Engine
// =============================================

require_once '../includes/session.php';
require_once '../includes/db.php';

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
        // Append time to ensure the entire day is covered
        $startD = $startDate . ' 00:00:00';
        $endD = $endDate . ' 23:59:59';
        
        $dateFilterDonations = " AND created_at >= :start_date AND created_at <= :end_date";
        $dateFilterUsers = " AND created_at >= :start_date AND created_at <= :end_date";
        $dateFilterCollections = " AND created_at >= :start_date AND created_at <= :end_date";
        $dateFilterSolicitations = " AND created_at >= :start_date AND created_at <= :end_date";
        $dateFilterCampaigns = " AND updated_at >= :start_date AND updated_at <= :end_date";
        
        $params[':start_date'] = $startD;
        $params[':end_date'] = $endD;
    }

    // 1. KPIs
    $stmt = $pdo->prepare("
        SELECT
            COUNT(donation_id)           AS total_donations,
            COALESCE(SUM(amount), 0)     AS total_raised,
            COUNT(DISTINCT user_id)      AS unique_donors,
            COALESCE(AVG(amount), 0)     AS avg_donation,
            COUNT(DISTINCT campaign_id)  AS active_campaigns
        FROM donations
        WHERE payment_status = 'Completed' AND is_deleted = 0 {$dateFilterDonations}
    ");
    $stmt->execute($params);
    $kpis = $stmt->fetch();

    // 2. Donation Trend Chart Data (Dynamic aggregation based on Group By filter)
    if ($groupBy === 'daily') {
        $trendSql = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS log_date, SUM(amount) AS period_total 
                     FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0 {$dateFilterDonations}
                     GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d') ORDER BY log_date ASC";
    } elseif ($groupBy === 'yearly') {
        $trendSql = "SELECT YEAR(created_at) AS log_date, SUM(amount) AS period_total 
                     FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0 {$dateFilterDonations}
                     GROUP BY YEAR(created_at) ORDER BY log_date ASC";
    } else { // monthly
        $trendSql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS log_date, SUM(amount) AS period_total 
                     FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0 {$dateFilterDonations}
                     GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY log_date ASC";
    }

    $stmt = $pdo->prepare($trendSql);
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
        $userTrendSql = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS log_date, COUNT(*) AS period_total 
                         FROM users WHERE is_deleted = 0 {$dateFilterUsers} 
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d') ORDER BY log_date ASC";
    } elseif ($groupBy === 'yearly') {
        $userTrendSql = "SELECT YEAR(created_at) AS log_date, COUNT(*) AS period_total 
                         FROM users WHERE is_deleted = 0 {$dateFilterUsers} 
                         GROUP BY YEAR(created_at) ORDER BY log_date ASC";
    } else { // monthly
        $userTrendSql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS log_date, COUNT(*) AS period_total 
                         FROM users WHERE is_deleted = 0 {$dateFilterUsers} 
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY log_date ASC";
    }

    $stmt = $pdo->prepare($userTrendSql);
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
    $donationJoinFilter = $dateFilterDonations ? str_replace('created_at', 'd.created_at', $dateFilterDonations) : "";
    $stmt = $pdo->prepare("
        SELECT
            c.category,
            COALESCE(SUM(d.amount), 0) AS total_raised
        FROM campaigns c
        LEFT JOIN donations d
            ON c.campaign_id = d.campaign_id
            AND d.payment_status = 'Completed'
            AND d.is_deleted = 0 {$donationJoinFilter}
        GROUP BY c.category
        ORDER BY total_raised DESC
        LIMIT 4
    ");
    $stmt->execute($params);
    $categoriesRaw = $stmt->fetchAll();

    $categoryLabels = [];
    $categoryValues = [];
    foreach ($categoriesRaw as $cat) {
        $categoryLabels[] = $cat['category'];
        $categoryValues[] = (float)$cat['total_raised'];
    }
    
    // 5. Active Members
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM users
        WHERE account_status='Active'
        AND is_deleted=0 {$dateFilterUsers}
    ");
    $stmt->execute($params);
    $activeMembers = $stmt->fetch();

    // 6. Registered Users in Filter Period
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM users
        WHERE is_deleted=0 {$dateFilterUsers}
    ");
    $stmt->execute($params);
    $monthlyMembers = $stmt->fetch();

    // 7. Campaign Performance (Unfiltered to preserve active campaign contexts)
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

    // 8. Top Solicitors
    $stmt = $pdo->prepare("
        SELECT
            CONCAT(up.first_name,' ',up.last_name) AS full_name,
            SUM(d.amount) AS total_funds
        FROM donations d
        JOIN user_profiles up
            ON d.user_id = up.user_id
        WHERE d.payment_status='Completed' AND d.is_deleted = 0 {$dateFilterDonations}
        GROUP BY d.user_id, up.first_name, up.last_name
        ORDER BY total_funds DESC
        LIMIT 5
    ");
    $stmt->execute($params);
    $topSolicitors = $stmt->fetchAll();

    // 9. Total Collections
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total FROM collections WHERE is_deleted = 0 {$dateFilterCollections}
    ");
    $stmt->execute($params);
    $totalCollections = $stmt->fetch()['total'];

    // 10. Total Solicitations
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total FROM solicitations WHERE is_deleted = 0 {$dateFilterSolicitations}
    ");
    $stmt->execute($params);
    $totalSolicitations = $stmt->fetch()['total'];

    // 11. Payment Method Breakdown
    $stmt = $pdo->prepare("
        SELECT 
            payment_method, 
            COALESCE(SUM(amount), 0) AS total_amount, 
            COUNT(*) AS transaction_count
        FROM donations
        WHERE payment_status = 'Completed' AND is_deleted = 0 {$dateFilterDonations}
        GROUP BY payment_method
    ");
    $stmt->execute($params);
    $paymentMethodBreakdown = $stmt->fetchAll();

    // 12. Completed Campaigns Trend Chart Data (Connected seamlessly to filters)
    if ($groupBy === 'daily') {
        $compTrendSql = "SELECT DATE_FORMAT(updated_at, '%Y-%m-%d') AS log_date, COUNT(*) AS period_total 
                         FROM campaigns WHERE campaign_status = 'Completed' AND is_deleted = 0 {$dateFilterCampaigns}
                         GROUP BY DATE_FORMAT(updated_at, '%Y-%m-%d') ORDER BY log_date ASC";
    } elseif ($groupBy === 'yearly') {
        $compTrendSql = "SELECT YEAR(updated_at) AS log_date, COUNT(*) AS period_total 
                         FROM campaigns WHERE campaign_status = 'Completed' AND is_deleted = 0 {$dateFilterCampaigns}
                         GROUP BY YEAR(updated_at) ORDER BY log_date ASC";
    } else { // monthly
        $compTrendSql = "SELECT DATE_FORMAT(updated_at, '%Y-%m') AS log_date, COUNT(*) AS period_total 
                         FROM campaigns WHERE campaign_status = 'Completed' AND is_deleted = 0 {$dateFilterCampaigns}
                         GROUP BY DATE_FORMAT(updated_at, '%Y-%m') ORDER BY log_date ASC";
    }

    $stmt = $pdo->prepare($compTrendSql);
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

echo json_encode(['success' => false, 'message' => 'Invalid action endpoint.']);