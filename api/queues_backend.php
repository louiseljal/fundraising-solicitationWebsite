<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/db.php';

// ==========================================================================
// WRITE/MUTATION OPERATIONS (POST Requests)
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? $rawInput['action'] ?? '';

    try {
        // 1. Transaction Ledger Routing (Approve / Reject Donations)
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

            $pdo->beginTransaction();

            // Update donation status parameters
            $stmtUpdateTx = $pdo->prepare("UPDATE donations SET payment_status = ? WHERE donation_id = ?");
            $stmtUpdateTx->execute([$payment_status, $donation_id]);

            // Update cached campaign values based on status change
            if ($payment_status === 'Completed') {
                $stmtUpdateCampaign = $pdo->prepare("UPDATE campaigns SET current_raised_cache = current_raised_cache + ? WHERE campaign_id = ?");
                $stmtUpdateCampaign->execute([$tx['amount'], $tx['campaign_id']]);
            } elseif ($tx['payment_status'] === 'Completed' && $payment_status !== 'Completed') {
                $stmtUpdateCampaign = $pdo->prepare("UPDATE campaigns SET current_raised_cache = current_raised_cache - ? WHERE campaign_id = ?");
                $stmtUpdateCampaign->execute([$tx['amount'], $tx['campaign_id']]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "Transaction reference #$donation_id processed."]);
            exit;
        }

        // 2. Draft Campaign Approval Routing
        if ($action === 'process_campaign') {
            $campaign_id = intval($rawInput['campaign_id'] ?? 0);
            $status_action = trim($rawInput['status_action'] ?? ''); // 'Approve' or 'Reject'
            
            // If Approved, it goes Active. If Rejected, it goes to Cancelled.
            $final_status = ($status_action === 'Approve') ? 'Active' : 'Cancelled';

            // Verify the campaign exists and is currently a Draft
            $stmtCamp = $pdo->prepare("SELECT * FROM campaigns WHERE campaign_id = ? AND campaign_status = 'Draft' AND is_deleted = 0");
            $stmtCamp->execute([$campaign_id]);
            $campaign = $stmtCamp->fetch(PDO::FETCH_ASSOC);

            if (!$campaign) {
                echo json_encode(['success' => false, 'error' => 'Campaign not found or is no longer in Draft status.']);
                exit;
            }

            $pdo->beginTransaction();

            // Update the campaign status
            $stmtUpdateCamp = $pdo->prepare("UPDATE campaigns SET campaign_status = ? WHERE campaign_id = ?");
            $stmtUpdateCamp->execute([$final_status, $campaign_id]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "Campaign #$campaign_id updated to $final_status."]);
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
try {
    // 1. Fetch pending donations ledger
    $queueStmt = $pdo->query("
        SELECT d.donation_id, d.transaction_reference, d.amount, u.username, c.title AS campaign_title 
        FROM donations d
        JOIN users u ON d.user_id = u.user_id
        JOIN campaigns c ON d.campaign_id = c.campaign_id
        WHERE d.payment_status = 'Pending'
        ORDER BY d.created_at DESC
    ");
    $pendingTransactions = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch pending (Draft) Campaigns
    $campaignsStmt = $pdo->query("
        SELECT campaign_id, title, category, goal_amount, description, start_date 
        FROM campaigns 
        WHERE campaign_status = 'Draft' AND is_deleted = 0 
        ORDER BY created_at DESC
    ");
    $pendingCampaigns = $campaignsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'pendingTransactions' => $pendingTransactions,
        'pendingCampaigns' => $pendingCampaigns
    ]);
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
exit;
?>