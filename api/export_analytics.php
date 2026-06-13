<?php
// api/export_analytics.php
require_once 'admin_protect.php';
require_once '../includes/db.php';
require_once '../fpdf/fpdf.php';

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

$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(amount), 0) AS total_raised,
        COUNT(DISTINCT user_id) AS unique_donors
    FROM donations
    WHERE payment_status = 'Completed' AND is_deleted = 0 {$dateFilterDonations}
");
$stmt->execute($params);
$kpis = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users WHERE account_status='Active' AND is_deleted=0 {$dateFilterUsers}");
$stmt->execute($params);
$activeMembers = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users WHERE is_deleted=0 {$dateFilterUsers}");
$stmt->execute($params);
$monthlyMembers = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM collections WHERE is_deleted = 0 {$dateFilterCollections}");
$stmt->execute($params);
$totalCollections = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM solicitations WHERE is_deleted = 0 {$dateFilterSolicitations}");
$stmt->execute($params);
$totalSolicitations = $stmt->fetch()['total'];

$pdf = new FPDF();
$pdf->AddPage();

// Document Header
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'Platform Performance Analytics Report', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');

if (!empty($startDate) && !empty($endDate)) {
    $pdf->Cell(0, 10, "Date Range: {$startDate} to {$endDate}", 0, 1, 'C');
} else {
    $pdf->Cell(0, 10, "Date Range: All-Time Summary", 0, 1, 'C');
}
$pdf->Ln(15);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(110, 12, 'System Metric / Category', 1, 0, 'L');
$pdf->Cell(50, 12, 'Aggregated Value', 1, 1, 'C');

// Rows
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(110, 12, 'Gross Total Raised', 1, 0, 'L');
$pdf->Cell(50, 12, 'PHP ' . number_format($kpis['total_raised'], 2), 1, 1, 'C');

$pdf->Cell(110, 12, 'Unique Donors', 1, 0, 'L');
$pdf->Cell(50, 12, $kpis['unique_donors'], 1, 1, 'C');

$pdf->Cell(110, 12, 'Active Members', 1, 0, 'L');
$pdf->Cell(50, 12, $activeMembers, 1, 1, 'C');

$pdf->Cell(110, 12, 'Total Registered (Filtered)', 1, 0, 'L');
$pdf->Cell(50, 12, $monthlyMembers, 1, 1, 'C');

$pdf->Cell(110, 12, 'Total Collections', 1, 0, 'L');
$pdf->Cell(50, 12, $totalCollections, 1, 1, 'C');

$pdf->Cell(110, 12, 'Total Solicitations', 1, 0, 'L');
$pdf->Cell(50, 12, $totalSolicitations, 1, 1, 'C');

$pdf->Output('I', 'Analytics_Report.pdf');
exit();
?>