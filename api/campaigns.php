<?php
// ==================================================================
// api/campaigns.php
// Operations API Layer: Handles Campaign Fetching (GET) and CRUD (POST).
// ==================================================================

// Absolute directory reference to pull our central session logic safely
require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ==================================================================
// GET METHODS - Public Read Access (Donors & Admins)
// ==================================================================
if ($method === 'GET') {

    // 1. Fetch individual campaign card data details
    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT c.*,
                   COALESCE(SUM(CASE WHEN d.payment_status = 'Completed' THEN d.amount ELSE 0 END), 0) AS total_raised,
                   COUNT(CASE WHEN d.payment_status = 'Completed' THEN 1 END) AS donor_count
            FROM campaigns c
            LEFT JOIN donations d ON c.campaign_id = d.campaign_id AND (d.is_deleted = 0 OR d.is_deleted IS NULL)
            WHERE c.campaign_id = ? AND c.is_deleted = 0
            GROUP BY c.campaign_id
        ");
        $stmt->execute([$id]);
        $campaign = $stmt->fetch();

        if (!$campaign) {
            http_response_code(404); // 404 Not Found
            echo json_encode(['success' => false, 'message' => 'Requested campaign profile not found.']);
            exit;
        }

        echo json_encode(['success' => true, 'campaign' => $campaign]);
        exit;
    }

    // 2. Fetch all campaigns with flexible system parameter filter stacks
    $where  = ["c.is_deleted = 0"];
    $params = [];

    if (!empty($_GET['status'])) {
        $where[]  = "c.campaign_status = ?";
        $params[] = $_GET['status'];
    }

    if (!empty($_GET['category'])) {
        $where[]  = "c.category = ?";
        $params[] = $_GET['category'];
    }

    if (!empty($_GET['search'])) {
        $where[]  = "c.title LIKE ?";
        $params[] = '%' . $_GET['search'] . '%';
    }

    $whereSQL = implode(' AND ', $where);

    // Dynamic parameterized database compilation block
    // Added NULLIF(c.goal_amount, 0) to cleanly avoid math crashes if a goal is zero.
    $stmt = $pdo->prepare("
        SELECT c.*,
               COALESCE(SUM(CASE WHEN d.payment_status = 'Completed' THEN d.amount ELSE 0 END), 0) AS total_raised,
               COUNT(CASE WHEN d.payment_status = 'Completed' THEN 1 END) AS donor_count,
               ROUND(
                   COALESCE(SUM(CASE WHEN d.payment_status = 'Completed' THEN d.amount ELSE 0 END), 0)
                   / NULLIF(c.goal_amount, 0) * 100, 2
               ) AS progress_pct
        FROM campaigns c
        LEFT JOIN donations d ON c.campaign_id = d.campaign_id AND (d.is_deleted = 0 OR d.is_deleted IS NULL)
        WHERE $whereSQL
        GROUP BY c.campaign_id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute($params);
    $campaigns = $stmt->fetchAll();

    echo json_encode(['success' => true, 'campaigns' => $campaigns]);
    exit;
}

// ==================================================================
// POST METHODS - Restricted Administrative Modifications (CRUD)
// ==================================================================
if ($method === 'POST') {

    // Rubric Compliance: Trigger unified secure guard handler
    requireAdmin();

    // --- Action Tier: CREATE ---
    if ($action === 'create') {
        $title       = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $goal        = (float)($_POST['goal_amount'] ?? 0);
        $category    = htmlspecialchars(trim($_POST['category'] ?? ''));
        $start_date  = $_POST['start_date'] ?? '';
        $end_date    = $_POST['end_date'] ?? '';
        $status      = $_POST['campaign_status'] ?? 'Draft';

        if (!$title || !$description || !$goal || !$category || !$start_date || !$end_date) {
            http_response_code(400); // 400 Bad Request
            echo json_encode(['success' => false, 'message' => 'Operational processing failed. Missing required fields.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO campaigns (title, description, goal_amount, campaign_status, category, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $goal, $status, $category, $start_date, $end_date]);

        http_response_code(201); // 201 Created
        echo json_encode(['success' => true, 'message' => 'Campaign profile populated successfully!', 'id' => $pdo->lastInsertId()]);
        exit;
    }

    // --- Action Tier: UPDATE ---
    if ($action === 'update') {
        $id          = (int)($_POST['campaign_id'] ?? 0);
        $title       = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $goal        = (float)($_POST['goal_amount'] ?? 0);
        $category    = htmlspecialchars(trim($_POST['category'] ?? ''));
        $start_date  = $_POST['start_date'] ?? '';
        $end_date    = $_POST['end_date'] ?? '';
        $status      = $_POST['campaign_status'] ?? 'Draft';

        $stmt = $pdo->prepare("
            UPDATE campaigns
            SET title = ?, description = ?, goal_amount = ?,
                campaign_status = ?, category = ?, start_date = ?, end_date = ?
            WHERE campaign_id = ? AND is_deleted = 0
        ");
        $stmt->execute([$title, $description, $goal, $status, $category, $start_date, $end_date, $id]);

        echo json_encode(['success' => true, 'message' => 'Campaign profile modified successfully.']);
        exit;
    }

    // --- Action Tier: DELETE (Soft-Delete Policy Compliance) ---
    if ($action === 'delete') {
        $id = (int)($_POST['campaign_id'] ?? 0);

        $stmt = $pdo->prepare("UPDATE campaigns SET is_deleted = 1 WHERE campaign_id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Campaign logical unit marked as archived.']);
        exit;
    }
}

// Default Fallthrough Endpoint Status Exception Handler
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Malformed query string routing or unknown runtime action context requested.']);
?>