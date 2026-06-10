<?php
// ==================================================================
// api/solicitations.php
// Operations Management Node (OLTP): Governs formal donation requests.
// Implements strict Row-Level data visibility barriers based on RBAC.
// ==================================================================

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/db.php';

header('Content-Type: application/json');

// Self-healing database initialization block
$pdo->exec("
    CREATE TABLE IF NOT EXISTS solicitations (
        solicitation_id       INT AUTO_INCREMENT PRIMARY KEY,
        user_id               INT NOT NULL,
        post_title            VARCHAR(200) NOT NULL,
        solicitation_category VARCHAR(100) NOT NULL,
        target_amount         DECIMAL(12,2) NOT NULL,
        campaign_deadline     DATE NOT NULL,
        post_description      TEXT NOT NULL,
        urgency_level         ENUM('Low','Medium','High') DEFAULT 'Medium',
        poc_name              VARCHAR(100),
        poc_phone             VARCHAR(30),
        status                ENUM('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
        is_deleted            TINYINT(1) DEFAULT 0,
        created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ==================================================================
// GET METHODS - Restricted Row-Level Visibility Matrix
// ==================================================================
if ($method === 'GET') {
    requireLogin(); // Must have an active identity token

    $where  = ["s.is_deleted = 0"];
    $params = [];

    // Privacy Guard: Filter out what the user is allowed to see
    if (!isAdminRole($_SESSION['user_role'] ?? '')) {
        if ($action === 'my_submissions') {
            // User tracking their own request logs
            $where[]  = "s.user_id = ?";
            $params[] = (int)$_SESSION['user_id'];
        } else {
            // General public view for non-admins: Only display active, verified items
            $where[]  = "s.status IN ('Approved', 'Completed')";
        }
    } else {
        // Administrative state-modifier filters
        if (!empty($_GET['status'])) {
            $where[]  = "s.status = ?";
            $params[] = $_GET['status'];
        }
    }

    $whereSQL = implode(' AND ', $where);

    $stmt = $pdo->prepare("
        SELECT s.*, u.username, up.first_name, up.last_name
        FROM solicitations s
        JOIN users u ON s.user_id = u.user_id
        JOIN user_profiles up ON s.user_id = up.user_id
        WHERE $whereSQL
        ORDER BY s.created_at DESC
    ");
    $stmt->execute($params);

    echo json_encode(['success' => true, 'solicitations' => $stmt->fetchAll()]);
    exit;
}

// ==================================================================
// POST METHODS - Identity-Validated Modifications
// ==================================================================
if ($method === 'POST') {
    requireLogin();

    // --- SUB-ACTION: SUBMIT NEW SOLICITATION REQUEST ---
    if ($action === 'create') {
        $user_id     = $_SESSION['user_id'];
        $title       = htmlspecialchars(trim($_POST['post_title'] ?? ''));
        $category    = htmlspecialchars(trim($_POST['solicitation_category'] ?? ''));
        $amount      = (float)($_POST['target_amount'] ?? 0);
        $deadline    = $_POST['campaign_deadline'] ?? '';
        $description = htmlspecialchars(trim($_POST['post_description'] ?? ''));
        $urgency     = $_POST['urgency_level'] ?? 'Medium';
        $poc_name    = htmlspecialchars(trim($_POST['poc_name'] ?? ''));
        $poc_phone   = htmlspecialchars(trim($_POST['poc_phone'] ?? ''));

        if (!$title || !$category || !$amount || !$deadline || !$description) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Validation failure. Missing required operational parameters.']);
            exit;
        }

$items = $_POST['allocation_items'] ?? null;
        if (!is_array($items)) $items = [];
      $items_json = json_encode(array_values(array_filter($items, fn($v) => trim((string)$v) !== '')));

        $beneficiary_count = (int)($_POST['beneficiary_count'] ?? 0);
        if ($beneficiary_count < 0) $beneficiary_count = 0;

        $attachment_names = [];
        if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
            foreach ($_FILES['attachments']['name'] as $idx => $name) {
                if (!isset($_FILES['attachments']['error'][$idx]) || $_FILES['attachments']['error'][$idx] !== UPLOAD_ERR_OK) continue;
                $attachment_names[] = basename(String($name));
            }
        }
        $attachments_json = json_encode($attachment_names);

        $pdo->exec("ALTER TABLE solicitations
            ADD COLUMN IF NOT EXISTS beneficiary_count INT NULL,
            ADD COLUMN IF NOT EXISTS allocation_items_json LONGTEXT NULL,
            ADD COLUMN IF NOT EXISTS attachments_json LONGTEXT NULL");

        $stmt = $pdo->prepare("
            INSERT INTO solicitations 
            (user_id, post_title, solicitation_category, target_amount, campaign_deadline, post_description, urgency_level, poc_name, poc_phone, beneficiary_count, allocation_items_json, attachments_json, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->execute([
            $user_id, $title, $category, $amount, $deadline, $description, $urgency, $poc_name, $poc_phone,
            $beneficiary_count, $items_json, $attachments_json
        ]);

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Solicitation log appended to approval queues successfully.']);
        exit;
    }

    // --- SUB-ACTION: ADMINISTRATIVE MODERATION (ADMIN ONLY) ---
    if ($action === 'update_status') {
        requireAdmin();

        $id     = (int)($_POST['solicitation_id'] ?? 0);
        $status = htmlspecialchars(trim($_POST['status'] ?? ''));

        // Verify entity exists
        $stmt = $pdo->prepare("SELECT solicitation_id FROM solicitations WHERE solicitation_id = ? AND is_deleted = 0");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Target solicitation element not found.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE solicitations SET status = ? WHERE solicitation_id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode(['success' => true, 'message' => 'Target entity status updated successfully within main catalog.']);
        exit;
    }

    // --- SUB-ACTION: SECURE LOGICAL ARCHIVING ---
    if ($action === 'delete') {
        $id = (int)($_POST['solicitation_id'] ?? 0);

        // Fetch ownership data to perform authorization checks
        $stmt = $pdo->prepare("SELECT user_id, status FROM solicitations WHERE solicitation_id = ? AND is_deleted = 0");
        $stmt->execute([$id]);
        $record = $stmt->fetch();

        if (!$record) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Target entity missing or already processed.']);
            exit;
        }

        // Security Guard: Check if the user is allowed to delete this row
        if (!isAdminRole($_SESSION['user_role'] ?? '') && $record['user_id'] !== $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Privilege violation: Access denied to requested row entity.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE solicitations SET is_deleted = 1 WHERE solicitation_id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Entity removed from active catalogs.']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Malformed action parameter routing maps.']);
?>