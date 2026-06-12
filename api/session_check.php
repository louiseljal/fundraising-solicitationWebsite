<?php
// =============================================
// api/session_check.php
// Called by app.js to check if user is logged in
// Returns user info so the page can show their name
// =============================================

session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false]);
    exit;
}

echo json_encode([
    'logged_in' => true,
    'user_id'   => $_SESSION['user_id'],
    'username'  => $_SESSION['username'],
    'user_role' => $_SESSION['user_role'],
    'full_name' => $_SESSION['full_name']
]);
?>
