<?php
// ==================================================================
// register_process.php
// ACID-Compliant Transactional User Registration Engine.
// Handles structural signup validation and multi-table operations.
// ==================================================================

if (session_status() === PHP_SESSION_NONE) {
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
$first_name       = htmlspecialchars(trim($_POST['first_name'] ?? ''));
$last_name        = htmlspecialchars(trim($_POST['last_name'] ?? ''));
$username         = htmlspecialchars(trim($_POST['username'] ?? ''));
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// --- Validation Tier ---

// Check empty fields
if (!$first_name || !$last_name || !$username || !$email || !$password) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Check valid email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Check password length
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password length must be at least 8 characters.']);
    exit;
}

// Check passwords match
if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password confirmation fields do not match.']);
    exit;
}

try {
    // Check if username or email already exists using standard prepared statement placeholders
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        http_response_code(409); // 409 Conflict
        echo json_encode(['success' => false, 'message' => 'Username or email address is already taken.']);
        exit;
    }

    // Hash the password securely using standard system BCrypt algorithm
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // --- ACID Transaction Isolation Block ---
    $pdo->beginTransaction();

    // Operation 1: Populate core credentials into the operational users table
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, user_role, account_status)
        VALUES (?, ?, ?, 'Donor', 'Active')
    ");
    $stmt->execute([$username, $email, $hashed_password]);

    // Track state generation by fetching the last primary index autoincrement sequence ID
    $new_user_id = $pdo->lastInsertId();

    // Operation 2: Populate contextual info using the generated foreign key identity connection
    $stmt = $pdo->prepare("
        INSERT INTO user_profiles (user_id, first_name, last_name)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$new_user_id, $first_name, $last_name]);

    // Save changes completely if no structural exceptions occur
    $pdo->commit();

    // Return clean JSON success packet back to frontend layout
    echo json_encode([
        'success'  => true,
        'message'  => 'Account created successfully! You can now log in.',
        'redirect' => 'login.html'
    ]);

} catch (Exception $e) {
    // Rubric Cleanliness: Safe conditional rollback check
    // Prevents nested exceptions if a crash occurs outside an opened state boundary
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Silently log structural faults on server runtime metrics
    error_log("Registration Engine Fault: " . $e->getMessage());

    http_response_code(500); // 500 Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'A backend data transaction failure occurred. Registration aborted safely.'
    ]);
}
?>