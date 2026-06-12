<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ==========================================================================
// POST METHODS - Donation Submission
// ==========================================================================
if ($method === 'POST') {
    if ($action === 'donate') {
        requireLogin();

        $campaign_id = (int)($_POST['campaign_id'] ?? 0);
        $amount = (float)($_POST['donation_amount'] ?? 0);
        $donor_name = htmlspecialchars(trim($_POST['donor_name'] ?? ''));
        $donor_message = htmlspecialchars(trim($_POST['donor_message'] ?? ''));
        $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

        if (!$campaign_id || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid campaign or amount.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Generate transaction reference
            $transactionRef = 'TXN-' . date('Ymd-His') . '-' . rand(1000, 9999);

            // Insert donation record with Pending status
            $stmt = $pdo->prepare("
                INSERT INTO donations (user_id, campaign_id, amount, currency, payment_status, payment_method, transaction_reference)
                VALUES (?, ?, ?, 'PHP', 'Pending', 'Manual', ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $campaign_id, $amount, $transactionRef]);

            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Donation submitted successfully. It will be reviewed by an administrator.']);
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Donation submission failed: ' . $e->getMessage()]);
            exit;
        }
    }
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
    $stmtAvg = $pdo->query("SELECT AVG(amount) FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0");
    $avgDonationSize = (float)($stmtAvg->fetchColumn() ?: 0.00);

    $stmtCount = $pdo->query("SELECT COUNT(donation_id) FROM donations WHERE is_deleted = 0");
    $totalRecordsCount = (int)($stmtCount->fetchColumn() ?: 0);

    // Detailed Ledger Transaction Entries
    $donationsStmt = $pdo->query("
        SELECT d.*, u.username, up.first_name, up.last_name, c.title AS campaign_title 
        FROM donations d
        JOIN users u ON d.user_id = u.user_id
        JOIN user_profiles up ON u.user_id = up.user_id
        JOIN campaigns c ON d.campaign_id = c.campaign_id
        WHERE d.is_deleted = 0
        ORDER BY d.created_at DESC
    ");
    $allDonations = $donationsStmt->fetchAll();

    // ALL-TIME Top 5 Donors Leaderboard (Monthly restriction removed)
    $topDonorsStmt = $pdo->query("
        SELECT u.username, SUM(d.amount) AS total_contributed
        FROM donations d
        JOIN users u ON d.user_id = u.user_id
        WHERE d.payment_status = 'Completed' AND d.is_deleted = 0
        GROUP BY d.user_id
        ORDER BY total_contributed DESC
        LIMIT 5
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