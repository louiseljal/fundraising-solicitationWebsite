<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/db.php';

try {
    // 2. FETCH SYSTEM METRIC COUNTS
    $stmtTotal = $pdo->query("SELECT COUNT(user_id) FROM users WHERE is_deleted = 0");
    $totalMembers = (int)$stmtTotal->fetchColumn();

    $stmtActive = $pdo->query("SELECT COUNT(user_id) FROM users WHERE is_deleted = 0 AND account_status = 'Active'");
    $activeMembers = (int)$stmtActive->fetchColumn(); 

    $stmtCollections = $pdo->query("SELECT COUNT(campaign_id) FROM campaigns WHERE is_deleted = 0");
    $activeCollections = (int)$stmtCollections->fetchColumn();

    $stmtFunds = $pdo->query("SELECT SUM(amount) FROM donations WHERE payment_status = 'Completed' AND is_deleted = 0");
    $totalFundsRaised = (float)($stmtFunds->fetchColumn() ?: 0.00);


    // 3. FETCH ALL USERS (includes role so frontend can detect admins)
    $recentStmt = $pdo->query("
        SELECT user_id, username, user_role, DATE_FORMAT(created_at, '%b %d, %Y') as joined_date 
        FROM users 
        WHERE is_deleted = 0
        ORDER BY created_at DESC
    ");
    $allMembers = $recentStmt->fetchAll();


    // 4. FETCH SYSTEM ADMINISTRATORS (based on user_role flag)
    $adminStmt = $pdo->query("
        SELECT user_id, username, user_role 
        FROM users 
        WHERE is_deleted = 0 AND user_role = 'Admin'
        ORDER BY username ASC
    ");
    $rawAdmins = $adminStmt->fetchAll();
    
    $adminMembers = [];
    foreach ($rawAdmins as $userRow) {
        $adminMembers[] = [
            'user_id'   => $userRow['user_id'],
            'username'  => $userRow['username'],
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
        'recentMembers' => $allMembers, // Contains all system accounts now
        'admins'        => $adminMembers
    ]);
    exit;

} catch (\Exception $e) {
    echo json_encode(['error' => 'Membership API processing crash: ' . $e->getMessage()]);
    exit;
}
?>