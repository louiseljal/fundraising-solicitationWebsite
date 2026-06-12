<?php
// ==================================================================
// api/session_test.php
// Diagnostic script to test session restrictions and role checking.
// ==================================================================

header('Content-Type: application/json');

// Include our updated session helper
require_once dirname(__DIR__) . '/includes/session.php';

// Check if the user passed a "test_action" parameter via the URL
$action = $_GET['action'] ?? 'check_login';

switch ($action) {
    case 'login_member':
        // Fake a standard member logging in
        $_SESSION['user_id'] = 42;
        $_SESSION['user_role'] = 'Member';
        echo json_encode(['success' => true, 'message' => 'Simulated login as standard Member successful.']);
        break;

    case 'login_admin':
        // Fake an Admin logging in
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'Admin';
        echo json_encode(['success' => true, 'message' => 'Simulated login as Admin successful.']);
        break;

    case 'logout':
        // Clear the fake session data
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Simulated logout successful.']);
        break;

    case 'test_admin_route':
        // Test our requireAdmin() security guard!
        requireAdmin();
        echo json_encode([
            'success' => true, 
            'message' => 'Access granted! You passed the Admin security check.',
            'session_data' => [
                'user_id' => $_SESSION['user_id'],
                'user_role' => $_SESSION['user_role']
            ]
        ]);
        break;

    case 'check_login':
    default:
        // Test our standard requireLogin() security guard!
        requireLogin();
        echo json_encode([
            'success' => true, 
            'message' => 'Access granted! You passed the standard Login security check.',
            'user_id' => $_SESSION['user_id']
        ]);
        break;
}
?>