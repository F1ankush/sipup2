<?php
/**
 * Database Connection Check
 * This ensures database is properly configured before trying to use it
 */

// Check if we're in setup mode
$isSetupPage = (strpos($_SERVER['PHP_SELF'], 'setup_database.php') !== false);
$isHealthCheck = (strpos($_SERVER['PHP_SELF'], 'health_check.php') !== false);

// If it's a setup or health check page, skip this
if ($isSetupPage || $isHealthCheck) {
    return;
}

// Check if .db_config exists
$configFile = __DIR__ . '/../.db_config';
$hasValidConfig = false;

if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    if ($config && !empty($config['password'])) {
        $hasValidConfig = true;
    }
}

// If no valid config file, redirect to setup
if (!$hasValidConfig) {
    // Only redirect for web requests, not for API calls
    if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Location: /setup_database.php');
        exit;
    }
}
