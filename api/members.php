<?php
header('Content-Type: application/json');

// 1. DATABASE CONNECTION CONFIGURATION
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

try {
    // 2. FETCH SYSTEM METRIC COUNTS
    $stmtTotal = $pdo->query("SELECT COUNT(user_id) FROM users");
    $totalMembers = (int)$stmtTotal->fetchColumn();

    $activeMembers = $totalMembers; 

    $stmtCollections = $pdo->query("SELECT COUNT(campaign_id) FROM campaigns");
    $activeCollections = (int)$stmtCollections->fetchColumn();

    $stmtFunds = $pdo->query("SELECT SUM(amount) FROM donations WHERE payment_status = 'Completed'");
    $totalFundsRaised = (float)($stmtFunds->fetchColumn() ?: 0.00);


    // 3. FETCH ALL USERS (FIXED: Removed 'LIMIT 4' so the frontend can display everyone in the directory!)
    $recentStmt = $pdo->query("
        SELECT user_id, username, DATE_FORMAT(created_at, '%b %d, %Y') as joined_date 
        FROM users 
        WHERE user_id != 1
        ORDER BY created_at DESC
    ");
    $allMembers = $recentStmt->fetchAll();


    // 4. FETCH SYSTEM ADMINISTRATORS
    $adminStmt = $pdo->query("
        SELECT user_id, username 
        FROM users 
        WHERE user_id = 1
        ORDER BY username ASC
    ");
    $rawAdmins = $adminStmt->fetchAll();
    
    $adminMembers = [];
    foreach ($rawAdmins as $userRow) {
        $adminMembers[] = [
            'user_id'   => $userRow['user_id'],
            'username'  => $userRow['username'],
            'role'      => 'Admin'
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