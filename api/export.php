<?php
// =============================================
// api/export.php
// Exports data as CSV or PDF
// Called when user clicks export button on reports page
// =============================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Loads the standard database connection (No admin restrictions in this file!)
require_once '../includes/db.php'; 

$type   = $_GET['type']   ?? 'csv';   // csv or pdf
$report = $_GET['report'] ?? 'donations'; // donations, campaigns, members, analytics

// =============================================
// Fetch the right data based on report type
// =============================================
$rows    = [];
$headers = [];
$title   = '';

if ($report === 'donations') {
    $title   = 'Donations Report';
    $headers = ['ID', 'Donor Name', 'Campaign', 'Amount (PHP)', 'Payment Method', 'Status', 'Date'];

    $stmt = $pdo->query("
        SELECT
            d.donation_id,
            CONCAT(up.first_name, ' ', up.last_name) AS donor_name,
            c.title AS campaign,
            d.amount,
            d.payment_method,
            d.payment_status,
            DATE_FORMAT(d.created_at, '%Y-%m-%d') AS date
        FROM donations d
        JOIN user_profiles up ON d.user_id = up.user_id
        JOIN campaigns c ON d.campaign_id = c.campaign_id
        ORDER BY d.created_at DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
}

if ($report === 'campaigns') {
    $title   = 'Campaigns Report';
    $headers = ['ID', 'Title', 'Category', 'Goal (PHP)', 'Raised (PHP)', 'Progress %', 'Status', 'Start Date', 'End Date'];

    $stmt = $pdo->query("
        SELECT
            c.campaign_id,
            c.title,
            c.category,
            c.goal_amount,
            COALESCE(SUM(CASE WHEN d.payment_status = 'Completed' THEN d.amount ELSE 0 END), 0) AS raised,
            ROUND(COALESCE(SUM(CASE WHEN d.payment_status = 'Completed' THEN d.amount ELSE 0 END), 0) / NULLIF(c.goal_amount, 0) * 100, 2) AS pct,
            c.campaign_status,
            c.start_date,
            c.end_date
        FROM campaigns c
        LEFT JOIN donations d ON c.campaign_id = d.campaign_id
        WHERE c.is_deleted = 0
        GROUP BY c.campaign_id
        ORDER BY raised DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
}

if ($report === 'members') {
    $title   = 'Members Report';
    $headers = ['ID', 'Full Name', 'Username', 'Email', 'Role', 'Total Donated (PHP)', 'Donations', 'Status', 'Joined'];

    $stmt = $pdo->query("
        SELECT
            u.user_id,
            CONCAT(up.first_name, ' ', up.last_name) AS full_name,
            u.username,
            u.email,
            u.user_role,
            up.total_donated_cache,
            COUNT(d.donation_id) AS donation_count,
            u.account_status,
            DATE_FORMAT(u.created_at, '%Y-%m-%d') AS joined
        FROM users u
        JOIN user_profiles up ON u.user_id = up.user_id
        LEFT JOIN donations d ON u.user_id = d.user_id AND d.payment_status = 'Completed'
        WHERE u.is_deleted = 0
        GROUP BY u.user_id
        ORDER BY up.total_donated_cache DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
}

if ($report === 'analytics') {
    // ✨ THE FIX: We parse your .env file here manually. 
    // This allows donors to download analytics WITHOUT loading dw_db.php or triggering requireAdmin()!
    $env_path = __DIR__ . '/../.env';
    if (!file_exists($env_path)) {
        die('Environment configuration file (.env) is missing.');
    }

    $env_vars = [];
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $env_vars[trim($parts[0])] = trim($parts[1]);
        }
    }

    $dw_host = $env_vars['DW_HOST'] ?? 'localhost';
    $dw_db   = $env_vars['DW_NAME'] ?? '';
    $dw_user = $env_vars['DW_USER'] ?? '';
    $dw_pass = $env_vars['DW_PASS'] ?? '';
    
    try {
        $dw_pdo = new PDO("mysql:host=$dw_host;dbname=$dw_db;charset=utf8mb4", $dw_user, $dw_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, 
        ]);
    } catch (\PDOException $e) {
        die('Data Warehouse analytics engine is currently offline.');
    }

    $title   = 'OLAP Monthly Summary';
    $headers = ['Year', 'Month', 'Total Raised (PHP)', 'Donations', 'Unique Donors', 'Avg Donation (PHP)'];

    $stmt = $dw_pdo->query("
        SELECT
            dt.year,
            dt.month_name,
            SUM(fd.donation_amount)          AS total_raised,
            COUNT(fd.fact_id)                AS donation_count,
            COUNT(DISTINCT fd.donor_sk)      AS unique_donors,
            ROUND(AVG(fd.donation_amount),2) AS avg_donation
        FROM fact_donations fd
        JOIN dim_time dt ON fd.time_id = dt.time_id
        GROUP BY dt.year, dt.month_num, dt.month_name
        ORDER BY dt.year, dt.month_num
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
}

// =============================================
// CSV EXPORT ENGINE
// =============================================
if ($type === 'csv') {
    $filename = $report . '_report_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM flag for Excel layout

    fputcsv($output, [$title]);
    fputcsv($output, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($output, []); 

    fputcsv($output, $headers);

    foreach ($rows as $row) {
        fputcsv($output, $row);
    }

    fputcsv($output, []);
    fputcsv($output, ['Total Records:', count($rows)]);

    fclose($output);
    exit;
}

// =============================================
// PDF EXPORT ENGINE (FPDF Integration)
// =============================================
if ($type === 'pdf') {
    $fpdfPath = __DIR__ . '/../fpdf/fpdf.php';

    if (!file_exists($fpdfPath)) {
        header('Location: export.php?type=csv&report=' . $report);
        exit;
    }

    require_once $fpdfPath;

    // Clears background output buffers to prevent corrupt PDF files
    if (ob_get_length()) ob_end_clean();

    $pdf = new FPDF('L', 'mm', 'A4'); // Landscape layout
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 20);

    // Title Block
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 6, 'Generated: ' . date('Y-m-d H:i:s'), 0, 1, 'R');
    $pdf->Ln(3);

    // Calculate columns layout widths
    $colW = ($pdf->GetPageWidth() - 20) / count($headers);

    // Render Table Headers
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(200, 220, 200);
    foreach ($headers as $h) {
        $pdf->Cell($colW, 8, $h, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Render Table Body Rows
    $pdf->SetFont('Arial', '', 8);
    foreach ($rows as $row) {
        foreach ($row as $cell) {
            $pdf->Cell($colW, 7, (string)$cell, 1, 0, 'L');
        }
        $pdf->Ln();
    }

    $pdf->Output('D', $report . '_report_' . date('Y-m-d') . '.pdf');
    exit;
}
?>