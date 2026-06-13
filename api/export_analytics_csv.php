<?php
// api/export_analytics_csv.php
require_once 'admin_protect.php';
require_once '../includes/db.php';

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$dateFilterDonations = "";
$dateFilterUsers = "";
$dateFilterCollections = "";
$dateFilterSolicitations = "";
$params = [];

if (!empty($startDate) && !empty($endDate)) {
    $startD = $startDate . ' 00:00:00';
    $endD = $endDate . ' 23:59:59';
    
    $dateFilterDonations = " AND created_at >= :start_date AND created_at <= :end_date";
    $dateFilterUsers = " AND created_at >= :start_date AND created_at <= :end_date";
    $dateFilterCollections = " AND created_at >= :start_date AND created_at <= :end_date";
    $dateFilterSolicitations = " AND created_at >= :start_date AND created_at <= :end_date";
    
    $params[':start_date'] = $startD;
    $params[':end_date'] = $endD;
}

// 1. KPIs Donors & Raised
$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(amount), 0) AS total_raised,
        COUNT(DISTINCT user_id) AS unique_donors
    FROM donations
    WHERE payment_status = 'Completed' AND is_deleted = 0 {$dateFilterDonations}
");
$stmt->execute($params);
$kpis = $stmt->fetch();

// 2. Active Members
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users WHERE account_status='Active' AND is_deleted=0 {$dateFilterUsers}");
$stmt->execute($params);
$activeMembers = $stmt->fetch()['total'];

// 3. Registered Users
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users WHERE is_deleted=0 {$dateFilterUsers}");
$stmt->execute($params);
$monthlyMembers = $stmt->fetch()['total'];

// 4. Collections
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM collections WHERE is_deleted = 0 {$dateFilterCollections}");
$stmt->execute($params);
$totalCollections = $stmt->fetch()['total'];

// 5. Solicitations
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM solicitations WHERE is_deleted = 0 {$dateFilterSolicitations}");
$stmt->execute($params);
$totalSolicitations = $stmt->fetch()['total'];

// Stream CSV Headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Analytics_Report_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');
fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

fputcsv($output, ['Platform Performance Analytics Report']);
fputcsv($output, ['Date Range: ' . ($startDate && $endDate ? "$startDate to $endDate" : "All-Time Summary")]);
fputcsv($output, ['Generated On: ' . date('Y-m-d H:i:s')]);
fputcsv($output, []); // Empty row

fputcsv($output, ['System Metric / Category', 'Recorded Aggregates']);
fputcsv($output, ['Gross Total Raised', 'PHP ' . number_format($kpis['total_raised'], 2)]);
fputcsv($output, ['Unique Donors', $kpis['unique_donors']]);
fputcsv($output, ['Active Members', $activeMembers]);
fputcsv($output, ['Total Registered (Filtered)', $monthlyMembers]);
fputcsv($output, ['Total Collections', $totalCollections]);
fputcsv($output, ['Total Solicitations', $totalSolicitations]);

fclose($output);
exit();
?>