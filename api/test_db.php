<?php
// ==================================================================
// api/test_db.php
// Diagnostic script to verify .env loading and database connectivity.
// ==================================================================

// Set headers for clean API JSON output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Include the database connection file we want to test
    // We use dirname(__DIR__) to safely navigate up to the parent folder, then into includes
    require_once dirname(__DIR__) . '/includes/db.php';

    // Verify if the $pdo object exists and is initialized
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection file included, but \$pdo instance was not found or initialized.");
    }

    // Run a lightweight test query to ensure the MySQL server actually responds
    // CURRENT_TIMESTAMP verifies that the database server time is active
    $stmt = $pdo->query("SELECT VERSION() AS mysql_version, CURRENT_TIMESTAMP() AS server_time");
    $result = $stmt->fetch();

    // If we reach this point, everything works perfectly!
    echo json_encode([
        'success' => true,
        'message' => 'Database connection and configuration verified successfully!',
        'diagnostics' => [
            'environment' => 'Loaded via .env file parameters',
            'mysql_version' => $result['mysql_version'],
            'server_time' => $result['server_time']
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Catch any script-level errors that didn't get caught inside db.php
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection check failed.',
        'error_details' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>