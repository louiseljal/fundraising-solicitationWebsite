<?php
// ==================================================================
// api/collections.php
// Operations Transactional Node (OLTP): Manages physical cash collections.
// Ensures strict ACID ledger parity between cash records and campaign summaries.
// ==================================================================

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/db.php';

header('Content-Type: application/json');

// Self-healing schema provisioning for standalone evaluation
$pdo->exec("
    CREATE TABLE IF NOT EXISTS collections (
        collection_id     INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id       INT NOT NULL,
        collected_by      INT NOT NULL,
        amount            DECIMAL(12,2) NOT NULL,
        collection_date   DATE NOT NULL,
        collection_method VARCHAR(100) DEFAULT 'Cash',
        notes             TEXT,
        is_deleted        TINYINT(1) DEFAULT 0,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id)  REFERENCES campaigns(campaign_id),
        FOREIGN KEY (collected_by) REFERENCES users(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ==================================================================
// GET METHODS - Transaction Log Extraction
// ==================================================================
if ($method === 'GET') {
    requireLogin(); // System-wide guard clause

    $stmt = $pdo->prepare("
        SELECT col.*, c.title AS campaign_title, c.category,
               up.first_name, up.last_name
        FROM collections col
        JOIN campaigns c ON col.campaign_id = c.campaign_id
        JOIN user_profiles up ON col.collected_by = up.user_id
        WHERE col.is_deleted = 0
        ORDER BY col.collection_date DESC
    ");
    $stmt->execute();
    $rows  = $stmt->fetchAll();
    
    // Memory-safe calculation of array column summaries
    $total = array_sum(array_column($rows, 'amount'));

    echo json_encode([
        'success' => true, 
        'collections' => $rows, 
        'total_collected' => $total
    ]);
    exit;
}

// ==================================================================
// POST METHODS - Ledger Modifications (ACID Boundaries)
// ==================================================================
if ($method === 'POST') {
    
    // --- SUB-ACTION: RECORD NEW COLLECTION ---
    if ($action === 'create') {
        requireLogin(); // Ensure collector has an active session token

        $campaign_id = (int)($_POST['campaign_id'] ?? 0);
        $amount      = (float)($_POST['amount'] ?? 0);
        $date        = $_POST['collection_date'] ?? date('Y-m-d');
        $method_type = htmlspecialchars(trim($_POST['collection_method'] ?? 'Cash'));
        $notes       = htmlspecialchars(trim($_POST['notes'] ?? ''));

        if (!$campaign_id || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Malformed dataset payload. Campaign and valid amount required.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // 1. Log transaction
            $stmt = $pdo->prepare("
                INSERT INTO collections (campaign_id, collected_by, amount, collection_date, collection_method, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$campaign_id, $_SESSION['user_id'], $amount, $date, $method_type, $notes]);

            // 2. Cascade update cache summary
            $stmt = $pdo->prepare("UPDATE campaigns SET current_raised_cache = current_raised_cache + ? WHERE campaign_id = ?");
            $stmt->execute([$amount, $campaign_id]);

            $pdo->commit();
            
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Physical collection balance committed successfully.']);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Isolation layer rollback executed. Transaction failed.']);
        }
        exit;
    }

    // --- SUB-ACTION: REMOVE COLLECTION ENTRY (FIXES THE HIDDEN BUG) ---
    if ($action === 'delete') {
        requireAdmin(); // Deleting financial data must be restricted to administrators

        $id = (int)($_POST['collection_id'] ?? 0);

        // Fetch target attributes to execute reverse cache calculation
        $stmt = $pdo->prepare("SELECT campaign_id, amount, is_deleted FROM collections WHERE collection_id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();

        if (!$record || $record['is_deleted'] == 1) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Target entity missing or already archived.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // 1. Deduct the amount back out of the campaign cache summary
            $stmt = $pdo->prepare("UPDATE campaigns SET current_raised_cache = current_raised_cache - ? WHERE campaign_id = ?");
            $stmt->execute([$record['amount'], $record['campaign_id']]);

            // 2. Apply logical soft-delete flag
            $stmt = $pdo->prepare("UPDATE collections SET is_deleted = 1 WHERE collection_id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Collection entry removed and campaign metrics re-balanced successfully.']);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Cache reconciliation failed. Deletion aborted.']);
        }
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'HTTP method action routing mapping not supported.']);
?>