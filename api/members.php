<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/db.php';

// Initialize session to determine identity of active Admin confirming changes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 1. HANDLE IN-LINE STATUS / ROLE UPDATES (POST REQUEST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId        = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $accountStatus = $_POST['account_status'] ?? null;
        $userRole      = $_POST['user_role'] ?? null;
        $adminPassword = $_POST['admin_password'] ?? null;
        $currentAdminId = $_SESSION['user_id'] ?? null;

        if (!$currentAdminId || !$adminPassword) {
            echo json_encode(['success' => false, 'error' => 'Authentication parameters missing. Action refused.']);
            exit;
        }

        // 1a. Security Authentication Verification against 'password_hash' column
        $authStmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ? AND user_role = 'Admin'");
        $authStmt->execute([$currentAdminId]);
        $adminRecord = $authStmt->fetch(PDO::FETCH_ASSOC);

        if (!$adminRecord || !password_verify($adminPassword, $adminRecord['password_hash'])) {
            echo json_encode(['success' => false, 'error' => 'Security verification rejected: Admin password incorrect.']);
            exit;
        }

        // 1b. Proceed with updates if authorized
        if ($userId) {
            if ($accountStatus && in_array($accountStatus, ['Active', 'Suspended'])) {
                $updateStmt = $pdo->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
                $updateStmt->execute([$accountStatus, $userId]);
                echo json_encode(['success' => true]);
                exit;
            }

            if ($userRole && in_array($userRole, ['Admin', 'Donor'])) {
                $updateStmt = $pdo->prepare("UPDATE users SET user_role = ? WHERE user_id = ?");
                $updateStmt->execute([$userRole, $userId]);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'error' => 'Invalid parameters supplied']);
        exit;
    }

    // 2. FETCH SYSTEM METRIC COUNTS
    $stmtTotal = $pdo->query("SELECT COUNT(user_id) FROM users WHERE is_deleted = 0");
    $totalMembers = (int)$stmtTotal->fetchColumn();

    $stmtActive = $pdo->query("SELECT COUNT(user_id) FROM users WHERE is_deleted = 0 AND account_status = 'Active'");
    $activeMembers = (int)$stmtActive->fetchColumn(); 

    $stmtCollections = $pdo->query("SELECT COUNT(campaign_id) FROM campaigns WHERE is_deleted = 0");
    $activeCollections = (int)$stmtCollections->fetchColumn();

    $stmtFunds = $pdo->query("SELECT SUM(amount) FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0");
    $totalFundsRaised = (float)($stmtFunds->fetchColumn() ?: 0.00);


    // 3. FETCH ALL USERS JOINED WITH PROFILE NAMES AND EMAILS
    $recentStmt = $pdo->query("
        SELECT u.user_id, u.username, u.email, u.user_role, u.account_status, 
               p.first_name, p.last_name, DATE_FORMAT(u.created_at, '%b %d, %Y') as joined_date 
        FROM users u
        LEFT JOIN user_profiles p ON u.user_id = p.user_id
        WHERE u.is_deleted = 0
        ORDER BY u.created_at DESC
    ");
    $allMembers = $recentStmt->fetchAll(PDO::FETCH_ASSOC);


    // 4. FETCH SYSTEM ADMINISTRATORS
    $adminStmt = $pdo->query("
        SELECT u.user_id, u.username, u.email, u.user_role 
        FROM users u
        WHERE u.is_deleted = 0 AND u.user_role = 'Admin'
        ORDER BY u.username ASC
    ");
    $rawAdmins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $adminMembers = [];
    foreach ($rawAdmins as $userRow) {
        $adminMembers[] = [
            'user_id'   => $userRow['user_id'],
            'username'  => $userRow['username'],
            'email'     => $userRow['email'],
            'role'      => $userRow['user_role'] ?? 'Admin'
        ];
    }


    // 5. OUTPUT SECURE DATASET
    echo json_encode([
        'metrics' => [
            'totalMembers'      => $totalMembers,
            'activeMembers'     => $activeMembers,
            'activeCollections' => $activeCollections,
            'totalFundsRaised'  => $totalFundsRaised
        ],
        'recentMembers' => $allMembers,
        'admins'        => $adminMembers
    ]);
    exit;

} catch (\Exception $e) {
    echo json_encode(['error' => 'Membership API processing crash: ' . $e->getMessage()]);
    exit;
}