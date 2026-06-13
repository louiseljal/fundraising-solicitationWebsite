<?php
// ==================================================================
// api/campaigns.php
// Operations API Layer: Handles Campaign Fetching (GET) and Secured CRUD (POST).
// ==================================================================

require_once dirname(__DIR__) . '/includes/db.php';

// Ensure sessions are active for authentication checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    // Optional month filter (month number '01'..'12' or integer)
    if (!empty($_GET['month'])) {
        $month = (int)ltrim($_GET['month'], '0');
        if ($month >=1 && $month <= 12) {
            $where[] = "MONTH(c.start_date) = ?";
            $params[] = $month;
        }
    }

    if (!empty($_GET['search'])) {
        $where[]  = "c.title LIKE ?";
        $params[] = '%' . $_GET['search'] . '%';
    }

    $whereSQL = implode(' AND ', $where);

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
// POST METHODS - Password-Secured Administrative Modifications (CRUD)
// ==================================================================
if ($method === 'POST') {

    // 1. Session & Role Verification
    $currentAdminId = $_SESSION['user_id'] ?? null;
    $userRole = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';

    if (!$currentAdminId || $userRole !== 'Admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access. Session invalid or insufficient privileges.']);
        exit;
    }

    // 2. Strict Password Verification against the Database
    $adminPassword = $_POST['admin_password'] ?? null;
    if (!$adminPassword) {
        echo json_encode(['success' => false, 'message' => 'Authentication parameters missing. Action refused.']);
        exit;
    }

    $authStmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ? AND user_role = 'Admin'");
    $authStmt->execute([$currentAdminId]);
    $adminRecord = $authStmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminRecord || !password_verify($adminPassword, $adminRecord['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Security verification rejected: Admin password incorrect.']);
        exit;
    }

    // 3. Authorized Actions Execution

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
            http_response_code(400); 
            echo json_encode(['success' => false, 'message' => 'Operational processing failed. Missing required fields.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO campaigns (title, slug, description, goal_amount, campaign_status, category, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Auto-generate a basic slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . time();
        
        $stmt->execute([$title, $slug, $description, $goal, $status, $category, $start_date, $end_date]);

        http_response_code(201); 
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

        if (!$title || !$description || !$goal || !$category || !$start_date || !$end_date) {
            http_response_code(400); 
            echo json_encode(['success' => false, 'message' => 'Operational processing failed. Missing required fields.']);
            exit;
        }

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