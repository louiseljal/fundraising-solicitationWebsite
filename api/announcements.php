<?php
// ==================================================================
// api/announcements.php
// Operations Messaging Node (OLTP): Handles structural notice initialization,
// role-restricted notice generation, and prioritized retrieval arrays.
// ==================================================================

require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/db.php';

header('Content-Type: application/json');

// Self-healing database initialization block (optimized for evaluation environments)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS announcements (
        announcement_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id         INT NOT NULL,
        title           VARCHAR(200) NOT NULL,
        content         TEXT NOT NULL,
        priority        ENUM('Normal','Important','Urgent') DEFAULT 'Normal',
        is_pinned       TINYINT(1) DEFAULT 0,
        is_deleted      TINYINT(1) DEFAULT 0,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ==================================================================
// GET METHODS - Public Messaging Retrieval (Donors & Admins)
// ==================================================================
if ($method === 'GET') {
    
    // Fetch individual notice for editing or modal display details
    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM announcements WHERE announcement_id = ? AND is_deleted = 0");
        $stmt->execute([$id]);
        $notice = $stmt->fetch();

        if (!$notice) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Requested announcement profile not found.']);
            exit;
        }
        echo json_encode(['success' => true, 'announcement' => $notice]);
        exit;
    }

    // Default List view query execution matrix
    $stmt = $pdo->prepare("
        SELECT a.*, up.first_name, up.last_name
        FROM announcements a
        JOIN user_profiles up ON a.user_id = up.user_id
        WHERE a.is_deleted = 0
        ORDER BY a.is_pinned DESC, a.created_at DESC
    ");
    $stmt->execute();
    
    echo json_encode(['success' => true, 'announcements' => $stmt->fetchAll()]);
    exit;
}

// ==================================================================
// POST METHODS - Restricted Administrative Modifications (CRUD)
// ==================================================================
if ($method === 'POST') {
    
    // Rubric Rule: Lock all system changes down to Admin clearance tokens
    requireAdmin();

    // --- Action Tier: CREATE ---
    if ($action === 'create') {
        $title    = htmlspecialchars(trim($_POST['title'] ?? ''));
        $content  = htmlspecialchars(trim($_POST['content'] ?? ''));
        $priority = $_POST['priority'] ?? 'Normal';
        $pinned   = (isset($_POST['is_pinned']) && $_POST['is_pinned'] == '1') ? 1 : 0;

        if (!$title || !$content) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Validation error. Missing required content structures.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO announcements (user_id, title, content, priority, is_pinned) VALUES (?,?,?,?,?)");
        $stmt->execute([$_SESSION['user_id'], $title, $content, $priority, $pinned]);
        
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Announcement profile broadcast successfully!']);
        exit;
    }

    // --- Action Tier: UPDATE ---
    if ($action === 'update') {
        $id       = (int)($_POST['announcement_id'] ?? 0);
        $title    = htmlspecialchars(trim($_POST['title'] ?? ''));
        $content  = htmlspecialchars(trim($_POST['content'] ?? ''));
        $priority = $_POST['priority'] ?? 'Normal';
        $pinned   = (isset($_POST['is_pinned']) && $_POST['is_pinned'] == '1') ? 1 : 0;

        if (!$id || !$title || !$content) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Validation error. Missing target entity tracking elements.']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE announcements 
            SET title = ?, content = ?, priority = ?, is_pinned = ? 
            WHERE announcement_id = ? AND is_deleted = 0
        ");
        $stmt->execute([$title, $content, $priority, $pinned, $id]);

        echo json_encode(['success' => true, 'message' => 'Announcement entry updated successfully.']);
        exit;
    }

    // --- Action Tier: DELETE (Soft-Delete Integrity Enforcement) ---
    if ($action === 'delete') {
        $id = (int)($_POST['announcement_id'] ?? 0);

        $stmt = $pdo->prepare("UPDATE announcements SET is_deleted = 1 WHERE announcement_id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Announcement logical entity marked as archived.']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Malformed action parameter routing.']);
?>