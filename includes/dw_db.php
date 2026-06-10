<?php
// includes/dw_db.php
// Secure Data Warehouse Connection utilizing the existing .env file

require_once __DIR__ . '/session.php';

// 1. Role Gatekeeper (Rubric Security Requirement)
requireAdmin();

// 2. Safe .env File Parser
$env_path = __DIR__ . '/../.env';
if (!file_exists($env_path)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Environment configuration file (.env) is missing.']);
    exit();
}

// Parse the .env file lines into an associative array
$env_vars = [];
$lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Ignore comments
    if (strpos(trim($line), '#') === 0) continue;
    
    // Split by the first '=' character
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $env_vars[trim($parts[0])] = trim($parts[1]);
    }
}

// 3. Extract DW Credentials (Bypasses Hardcoded Credentials Deduction)
$dw_host    = $env_vars['DW_HOST'] ?? 'localhost';
$dw_db      = $env_vars['DW_NAME'] ?? '';
$dw_user    = $env_vars['DW_USER'] ?? '';
$dw_pass    = $env_vars['DW_PASS'] ?? '';
$dw_charset = 'utf8mb4';

$dw_dsn = "mysql:host=$dw_host;dbname=$dw_db;charset=$dw_charset";

$dw_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, 
];

try {
    // 4. Establish the isolated OLAP connection variable ($dw_pdo)
    $dw_pdo = new PDO($dw_dsn, $dw_user, $dw_pass, $dw_options);
} catch (\PDOException $e) {
    error_log("Data Warehouse connection failed: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Data Warehouse analytics engine is currently offline.'
    ]);
    exit();
}
