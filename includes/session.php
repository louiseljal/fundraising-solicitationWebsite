<?php
// includes/session.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminRole($role): bool {
    return strtolower(trim((string) $role)) === 'admin';
}

/**
 * Enforces authentication. Stops script and returns structured error if session is missing.
 */
function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        // Explicitly format response as application/json
        header('Content-Type: application/json');
        // HTTP 401: Unauthorized access
        http_response_code(401); 
        
        echo json_encode([
            'success' => false, 
            'message' => 'Authentication required. Please log in first.'
        ]);
        exit;
    }
}

/**
 * Enforces authorization. Verifies if authenticated identity holds administrative clearance.
 */
function requireAdmin() {
    // 1. Ensure the user is authenticated first
    requireLogin(); 
    
    // 2. Validate current permission status
    if (!isAdminRole($_SESSION['user_role'] ?? '')) {
        // Explicitly format response as application/json
        header('Content-Type: application/json');
        // HTTP 403: Forbidden access (Authenticated but lacks permission level)
        http_response_code(403); 
        
        echo json_encode([
            'success' => false, 
            'message' => 'Access denied. Administrator clearance required.'
        ]);
        exit;
    }
}
?>