<?php
header('Content-Type: application/json');

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
     echo json_encode(['error' => "Database connection failed: " . $e->getMessage()]);
     exit;
}

// ==========================================================================
// WRITE/MUTATION OPERATIONS (POST Requests)
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input values safely if a raw body payload arrives
    $rawInput = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? $rawInput['action'] ?? '';

    try {
        // 1. CREATE OR UPDATE CAMPAIGN
        if ($action === 'save_campaign') {
            $campaign_id = !empty($rawInput['campaign_id']) ? intval($rawInput['campaign_id']) : null;
            $title = trim($rawInput['title'] ?? '');
            $goal_amount = floatval($rawInput['goal_amount'] ?? 0);

            if (empty($title) || $goal_amount <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid title or goal parameters.']);
                exit;
            }

            if ($campaign_id) {
                // Update Entity
                $stmt = $pdo->prepare("UPDATE campaigns SET title = ?, goal_amount = ? WHERE campaign_id = ?");
                $stmt->execute([$title, $goal_amount, $campaign_id]);
                echo json_encode(['success' => true, 'message' => 'Campaign record updated successfully.']);
            } else {
                // Create Entity
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
                $stmt = $pdo->prepare("INSERT INTO campaigns (title, slug, description, goal_amount, current_raised_cache) VALUES (?, ?, '', ?, 0.00)");
                $stmt->execute([$title, $slug, $goal_amount]);
                echo json_encode(['success' => true, 'message' => 'Campaign record created successfully.']);
            }
            exit;
        }

        // 2. SOFT DELETE / ARCHIVE CAMPAIGN
        if ($action === 'delete_campaign') {
            $campaign_id = intval($rawInput['campaign_id'] ?? 0);
            
            $stmt = $pdo->prepare("UPDATE campaigns SET is_deleted = 1 WHERE campaign_id = ?");
            $stmt->execute([$campaign_id]);
            
            echo json_encode(['success' => true, 'message' => 'Campaign marked as deleted.']);
            exit;
        }

        // 3. TRANSACTION LEDGER ROUTING (APPROVE / REJECT)
        if ($action === 'process_transaction') {
            $donation_id = intval($rawInput['donation_id'] ?? 0);
            $status_action = trim($rawInput['status_action'] ?? ''); // 'Approve' or 'Reject'
            $payment_status = ($status_action === 'Approve') ? 'Completed' : 'Failed';

            // Find details about the transaction before acting
            $stmtTx = $pdo->prepare("SELECT campaign_id, amount, payment_status FROM donations WHERE donation_id = ?");
            $stmtTx->execute([$donation_id]);
            $tx = $stmtTx->fetch();

            if (!$tx || $tx['payment_status'] !== 'Pending') {
                echo json_encode(['success' => false, 'error' => 'Transaction not found or already processed.']);
                exit;
            }

            // Start transaction wrapper block to verify data execution bounds
            $pdo->beginTransaction();

            // Update donation status parameters
            $stmtUpdateTx = $pdo->prepare("UPDATE donations SET payment_status = ? WHERE donation_id = ?");
            $stmtUpdateTx->execute([$payment_status, $donation_id]);

            // If approved, update cached campaign values automatically
            if ($payment_status === 'Completed') {
                $stmtUpdateCampaign = $pdo->prepare("UPDATE campaigns SET current_raised_cache = current_raised_cache + ? WHERE campaign_id = ?");
                $stmtUpdateCampaign->execute([$tx['amount'], $tx['campaign_id']]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "Transaction reference #$donation_id processed as $payment_status."]);
            exit;
        }

    } catch (\Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// ==========================================================================
// READ/READ-ONLY OPERATIONS (GET Requests)
// ==========================================================================
// 1. Live Performance Metrics
$stmtFunds = $pdo->query("SELECT SUM(amount) FROM donations WHERE payment_status = 'Completed'");
$totalFundsRaised = (float)($stmtFunds->fetchColumn() ?: 0.00);

$stmtDonors = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM donations WHERE payment_status = 'Completed'");
$totalUniqueDonors = (int)($stmtDonors->fetchColumn() ?: 0);

$stmtAvg = $pdo->query("SELECT AVG(amount) FROM donations WHERE payment_status = 'Completed'");
$avgDonationSize = (float)($stmtAvg->fetchColumn() ?: 0.00);

// 2. Campaigns Database Collection
$campaignsStmt = $pdo->query("SELECT campaign_id, title, description, goal_amount, current_raised_cache FROM campaigns WHERE is_deleted = 0 ORDER BY campaign_id DESC");
$allCampaigns = $campaignsStmt->fetchAll();

// 3. Verification ledger records
$queueStmt = $pdo->query("
    SELECT d.donation_id, d.transaction_reference, d.amount, u.username, c.title AS campaign_title 
    FROM donations d
    JOIN users u ON d.user_id = u.user_id
    JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.payment_status = 'Pending'
    ORDER BY d.created_at DESC
");
$pendingTransactions = $queueStmt->fetchAll();

// 4. Trend Analysis Metadata Compilation
$trendsData = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%b') AS month_label, SUM(amount) AS total_amount 
    FROM donations 
    WHERE payment_status = 'Completed'
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY created_at ASC LIMIT 6
")->fetchAll();

echo json_encode([
    'metrics' => [
        'totalFundsRaised'  => $totalFundsRaised,
        'totalUniqueDonors' => $totalUniqueDonors,
        'avgDonationSize'   => $avgDonationSize
    ],
    'campaigns' => $allCampaigns,
    'pendingTransactions' => $pendingTransactions,
    'charts' => [
        'trendLabels'    => array_column($trendsData, 'month_label'),
        'trendValues'    => array_map('floatval', array_column($trendsData, 'total_amount')),
        'categoryLabels' => ['Calamity Relief', 'Medical', 'Education'],
        'categoryValues' => [$totalFundsRaised * 0.6, $totalFundsRaised * 0.25, $totalFundsRaised * 0.15]
    ]
]);
exit;
?>