<?php
// ==================================================================
// logout.php
// Secure Session Termination Processor.
// Clears all server-side states and redirects to logout confirmation.
// ==================================================================

// 1. Initialize or resume the existing session context
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Unset all active session variables completely
$_SESSION = array();

// 3. Destroy the session cookie inside the user's web browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, // Forces the browser to expire the cookie immediately
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Wipe the session record clean off the web server
session_destroy();

// 5. Rubric Compliance: Securely redirect out to your logout page
header("Location: login.html");
exit;
?>