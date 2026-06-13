<?php
// ==========================================================================
// api/get_session_role.php
// Returns the active session state and user role for dynamic UI rendering.
// =========================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

echo json_encode([
    'logged_in' => $_SESSION['logged_in'] ?? false,
    'user_role' => $_SESSION['user_role'] ?? 'Guest',
    'username'  => $_SESSION['username'] ?? ''
]);