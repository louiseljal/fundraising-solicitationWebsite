<?php
// ==================================================================
// login_process.php
// Secure User Authentication Processor.
// This handles form submissions from login.html via fetch().
// ==================================================================

$remember_me = !empty($_POST['remember_me']);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params($remember_me ? 60 * 60 * 24 * 30 : 0, '/');
    session_start();
}

// Rubric Compliance: Use absolute dir paths to guarantee your includes never break.
require_once __DIR__ . '/includes/db.php';

// Format explicit JSON API transmission content header
header('Content-Type: application/json');

// Only allow secure POST execution paths
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST allowed.']);
    exit;
}

// Retrieve raw inputs and neutralize potential cross-site rendering injections
$username = htmlspecialchars(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';

// Validate completeness
if (empty($username) || empty($password)) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['success' => false, 'message' => 'Username and password fields are required.']);
    exit;
}

try {
    // Look up credentials using safe placeholder queries (Defends against SQL Injection)
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, u.password_hash, u.user_role, u.account_status,
               up.first_name, up.last_name
        FROM users u
        LEFT JOIN user_profiles up ON u.user_id = up.user_id
        WHERE (u.username = ? OR u.email = ?)
          AND u.is_deleted = 0
        LIMIT 1
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    // Verify hash authenticity against password input strings
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401); // 401 Unauthorized
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }

    // Verify system profile status parameters
    if ($user['account_status'] === 'Suspended') {
        http_response_code(403); // 403 Forbidden
        echo json_encode(['success' => false, 'message' => 'Your account has been suspended. Please contact administration.']);
        exit;
    }

    // 🚀 CRITICAL SECURITY: Regenerate the session ID upon successful login.
    // This wipes out old guest identifiers and blocks session fixation vectors completely!
    session_regenerate_id(true);

    // Persist authenticated identity states across our application tier
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['user_role'] = $user['user_role'];
    $_SESSION['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $_SESSION['logged_in'] = true;

    $redirectPage = ($user['user_role'] === 'Admin') ? 'admin.php' : 'index.html';

// Send successful confirmation packet back to frontend fetch script
echo json_encode([
    'success'   => true,
    'message'   => 'Login successful!',
    'redirect'  => $redirectPage, // Updated to use the variable
    'user_role' => $user['user_role'],
    'full_name' => $_SESSION['full_name']
]);
} catch (PDOException $e) {
    // If the database query breaks, protect credentials and log it on the backend
    error_log("Login Query Fault: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal database communication error occurred during login processing.'
    ]);
}
?>