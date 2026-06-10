<?php
// 1. DATABASE CONNECTION CONFIGURATION
$host    = 'localhost';
$db      = 'fundraising_db'; 
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     header('Content-Type: application/json');
     echo json_encode(['error' => "Database connection failed: " . $e->getMessage()]);
     exit;
}

// ==========================================================================
// CSV EXPORT ENGINE PIPELINE (Triggers on ?action=export_csv)
// ==========================================================================
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    try {
        // Fetch rows specifically for the spreadsheet layout
        $stmtExport = $pdo->query("
            SELECT d.donation_id AS `Transaction ID`, 
                   DATE_FORMAT(d.created_at, '%Y-%m-%d %H:%i') AS `Date & Time`, 
                   u.username AS `Donor Name`, 
                   c.title AS `Campaign Assignment`, 
                   d.amount AS `Amount ($)`, 
                   d.payment_method AS `Payment Method`, 
                   d.payment_status AS `Status`
            FROM donations d
            JOIN users u ON d.user_id = u.user_id
            JOIN campaigns c ON d.campaign_id = c.campaign_id
            ORDER BY d.created_at DESC
        ");
        $rows = $stmtExport->fetchAll();

        // Clear any previous output buffers to avoid corrupting the CSV file formatting
        if (ob_get_level()) ob_end_clean();

        // Set browser download parameters
        $filename = "donations_export_" . date('Y-m-d_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Open the PHP output stream
        $output = fopen('php://output', 'w');

        // Output UTF-8 BOM if opening in Excel to fix potential accented character bugs
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Insert column headers if records exist
        if (!empty($rows)) {
            fputcsv($output, array_keys($rows[0]));
            
            // Loop and print database rows out to the file buffer
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
        } else {
            fputcsv($output, ['No donation records found in database registry.']);
        }

        fclose($output);
        exit;

    } catch (\Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'CSV Generation Pipeline Error: ' . $e->getMessage()]);
        exit;
    }
}

// ==========================================================================
// STANDARD READ OPERATIONS (JSON API Endpoint)
// ==========================================================================
header('Content-Type: application/json');

try {
    // Live Telemetry Metrics
    $stmtAvg = $pdo->query("SELECT AVG(amount) FROM donations WHERE payment_status = 'Completed'");
    $avgDonationSize = (float)($stmtAvg->fetchColumn() ?: 0.00);

    $stmtCount = $pdo->query("SELECT COUNT(donation_id) FROM donations");
    $totalRecordsCount = (int)($stmtCount->fetchColumn() ?: 0);

    // Detailed Ledger Transaction Entries
    $donationsStmt = $pdo->query("
        SELECT d.amount, d.payment_status, DATE_FORMAT(d.created_at, '%b %d, %Y') as formatted_date, 
               u.username, c.title AS campaign_title 
        FROM donations d
        JOIN users u ON d.user_id = u.user_id
        JOIN campaigns c ON d.campaign_id = c.campaign_id
        ORDER BY d.created_at DESC
    ");
    $allDonations = $donationsStmt->fetchAll();

    // Monthly Top 3 Donors Leaderboard
    $topDonorsStmt = $pdo->query("
        SELECT u.username, SUM(d.amount) AS total_contributed
        FROM donations d
        JOIN users u ON d.user_id = u.user_id
        WHERE d.payment_status = 'Completed' 
          AND MONTH(d.created_at) = MONTH(CURRENT_DATE())
          AND YEAR(d.created_at) = YEAR(CURRENT_DATE())
        GROUP BY d.user_id
        ORDER BY total_contributed DESC
        LIMIT 3
    ");
    $topDonors = $topDonorsStmt->fetchAll();

    echo json_encode([
        'insights' => [
            'avgDonation' => $avgDonationSize,
            'totalCount'  => $totalRecordsCount
        ],
        'donations' => $allDonations,
        'topDonors' => $topDonors
    ]);
    exit;

} catch (\Exception $e) {
    echo json_encode(['error' => 'Data pipeline execution error: ' . $e->getMessage()]);
    exit;
}
?>