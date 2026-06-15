<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/db.php';

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
            // Require authentication
            require_once dirname(__DIR__) . '/includes/session.php';
            if (!function_exists('requireLogin')) {
                session_start();
                if (empty($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
                    exit;
                }
            } else {
                requireLogin();
            }

            $campaign_id = !empty($rawInput['campaign_id']) ? intval($rawInput['campaign_id']) : null;
            $title = trim($rawInput['title'] ?? '');
            $goal_amount = floatval($rawInput['goal_amount'] ?? 0);
            $description = trim($rawInput['description'] ?? '');
            $campaign_status = trim($rawInput['campaign_status'] ?? 'Draft');
            $category = trim($rawInput['category'] ?? 'General');
            $start_date = trim($rawInput['start_date'] ?? '');
            $end_date = trim($rawInput['end_date'] ?? '');

            if (empty($title) || $goal_amount <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid title or goal parameters.']);
                exit;
            }

            if ($campaign_id) {
                // Update Entity
                $stmt = $pdo->prepare("UPDATE campaigns SET title = ?, description = ?, goal_amount = ?, campaign_status = ?, category = ?, start_date = ?, end_date = ? WHERE campaign_id = ?");
                $stmt->execute([$title, $description, $goal_amount, $campaign_status, $category, $start_date, $end_date, $campaign_id]);
                echo json_encode(['success' => true, 'message' => 'Campaign record updated successfully.']);
            } else {
                // Create Entity
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
                $stmt = $pdo->prepare("INSERT INTO campaigns (title, slug, description, goal_amount, campaign_status, category, start_date, end_date, current_raised_cache) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00)");
                $stmt->execute([$title, $slug, $description, $goal_amount, $campaign_status, $category, $start_date, $end_date]);
                echo json_encode(['success' => true, 'message' => 'Campaign record created successfully.']);
            }
            exit;
        }

        // 1b. CHANGE CAMPAIGN STATUS (ADMIN ONLY)
        if ($action === 'change_campaign_status') {
            // Ensure only admins can change status
            require_once dirname(__DIR__) . '/includes/session.php';
            if (!function_exists('requireAdmin')) {
                // fallback: simple role check
                session_start();
                if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
                    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                    exit;
                }
            } else {
                requireAdmin();
            }

            $campaign_id = intval($rawInput['campaign_id'] ?? 0);
            $status = trim($rawInput['status'] ?? '');
            $allowed = ['Draft','Active','Paused','Completed','Cancelled'];
            if (!$campaign_id || !in_array($status, $allowed, true)) {
                echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE campaigns SET campaign_status = ? WHERE campaign_id = ?");
            $stmt->execute([$status, $campaign_id]);
            echo json_encode(['success' => true, 'message' => 'Campaign status updated']);
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

            // Update cached campaign values based on status change
            if ($payment_status === 'Completed') {
                // Increment cache when donation is approved
                $stmtUpdateCampaign = $pdo->prepare("UPDATE campaigns SET current_raised_cache = current_raised_cache + ? WHERE campaign_id = ?");
                $stmtUpdateCampaign->execute([$tx['amount'], $tx['campaign_id']]);
            } elseif ($tx['payment_status'] === 'Completed' && $payment_status !== 'Completed') {
                // Decrement cache when donation changes from Completed to non-Completed
                $stmtUpdateCampaign = $pdo->prepare("UPDATE campaigns SET current_raised_cache = current_raised_cache - ? WHERE campaign_id = ?");
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
$stmtFunds = $pdo->query("SELECT SUM(amount) FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0");
$totalFundsRaised = (float)($stmtFunds->fetchColumn() ?: 0.00);

$stmtDonors = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0");
$totalUniqueDonors = (int)($stmtDonors->fetchColumn() ?: 0);

$stmtAvg = $pdo->query("SELECT AVG(amount) FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0");
$avgDonationSize = (float)($stmtAvg->fetchColumn() ?: 0.00);

// 2. Campaigns Database Collection
$campaignsStmt = $pdo->query("SELECT campaign_id, title, description, goal_amount, current_raised_cache, campaign_status FROM campaigns WHERE is_deleted = 0 ORDER BY campaign_id DESC");
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

// 4. Pending solicitations for admin moderation
$solicitationsStmt = $pdo->query("SELECT s.solicitation_id, s.post_title, s.solicitation_category, s.urgency_level, s.target_amount, s.post_description, u.username FROM solicitations s JOIN users u ON s.user_id = u.user_id WHERE s.status = 'Pending' AND s.is_deleted = 0 ORDER BY s.created_at DESC");
$pendingSolicitations = $solicitationsStmt->fetchAll();

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
    'pendingSolicitations' => $pendingSolicitations,
    'charts' => [
        'trendLabels'    => array_column($trendsData, 'month_label'),
        'trendValues'    => array_map('floatval', array_column($trendsData, 'total_amount')),
        'categoryLabels' => ['Calamity Relief', 'Medical', 'Education'],
        'categoryValues' => [$totalFundsRaised * 0.6, $totalFundsRaised * 0.25, $totalFundsRaised * 0.15]
    ]
]);
exit;
?>