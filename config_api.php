<?php
/**
 * Configuration API Handler
 * Handles database credential setup and testing
 */

header('Content-Type: application/json');

require_once 'includes/config_manager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'test') {
    // Test connection
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? 'root';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'b2b_billing_system';

    if (ConfigManager::testConnection($host, $user, $pass, $dbname)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Cannot connect to database']);
    }
} 
elseif ($action === 'save') {
    // Save configuration
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? 'root';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'b2b_billing_system';

    // Verify connection before saving
    if (!ConfigManager::testConnection($host, $user, $pass, $dbname)) {
        echo json_encode(['success' => false, 'error' => 'Connection failed. Please check credentials.']);
        exit;
    }

    // Save credentials
    if (ConfigManager::saveCredentials($host, $user, $pass, $dbname)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save configuration']);
    }
} 
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
