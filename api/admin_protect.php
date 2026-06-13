<?php
// api/admin_protect.php

// Ensure sessions are initialized securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. GUEST CHECK: If the user is not logged in at all, bounce them to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 2. Read the user role from the session (checking both common naming conventions)
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';

// 3. STANDARD USER CHECK: If they ARE logged in, but their role is not precisely 'Admin',
//    redirect them to index.html. Their donor session stays completely active!
if ($user_role !== 'Admin') {
    header("Location: index.html");
    exit();
}

// 4. If they are logged in AND their role is 'Admin', the script simply lets them pass 
//    and continues loading the admin page.
?>