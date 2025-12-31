<?php
/**
 * HTTP 500 Error Debugger
 * Run this script to diagnose connection and configuration issues
 */

echo "<h2>🔍 HTTP 500 Error Diagnosis</h2>\n";

// Check PHP version
echo "<h3>1. PHP Information</h3>\n";
echo "PHP Version: " . phpversion() . "<br>\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>\n";

// Check MySQL extension
echo "<h3>2. MySQL Extension</h3>\n";
if (extension_loaded('mysqli')) {
    echo "✅ MySQLi extension is loaded<br>\n";
} else {
    echo "❌ MySQLi extension is NOT loaded<br>\n";
}

// Check config file
echo "<h3>3. Configuration File</h3>\n";
if (file_exists('includes/config.php')) {
    echo "✅ config.php exists<br>\n";
    
    // Try to load config
    try {
        require_once 'includes/config.php';
        echo "✅ config.php loaded successfully<br>\n";
        echo "DB_HOST: " . DB_HOST . "<br>\n";
        echo "DB_USER: " . DB_USER . "<br>\n";
        echo "DB_NAME: " . DB_NAME . "<br>\n";
    } catch (Exception $e) {
        echo "❌ Error loading config.php: " . $e->getMessage() . "<br>\n";
    }
} else {
    echo "❌ config.php does NOT exist<br>\n";
}

// Test database connection
echo "<h3>4. Database Connection Test</h3>\n";
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "❌ Connection Error: " . $conn->connect_error . "<br>\n";
    } else {
        echo "✅ Database connection successful<br>\n";
        
        // Test query
        $result = $conn->query("SELECT 1");
        if ($result) {
            echo "✅ Test query executed successfully<br>\n";
        } else {
            echo "❌ Test query failed: " . $conn->error . "<br>\n";
        }
        
        $conn->close();
    }
} catch (mysqli_sql_exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>\n";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "<br>\n";
}

// Check include files
echo "<h3>5. Include Files</h3>\n";
$files = [
    'includes/db.php',
    'includes/functions.php',
    'includes/error_handler.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>\n";
    } else {
        echo "❌ $file NOT found<br>\n";
    }
}

// Check for PHP errors
echo "<h3>6. Recent PHP Errors</h3>\n";
if (file_exists('error_log.txt')) {
    $errors = file_get_contents('error_log.txt');
    $lines = explode("\n", $errors);
    $recent = array_slice($lines, -10);
    echo "<pre>";
    foreach ($recent as $line) {
        if (!empty($line)) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "No error_log.txt found<br>\n";
}

// Check directory permissions
echo "<h3>7. Directory Permissions</h3>\n";
$dirs = ['uploads', 'cache', 'includes'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '✅ Writable' : '❌ Not writable';
        echo "$dir: $writable<br>\n";
    } else {
        echo "$dir: ❌ Does not exist<br>\n";
    }
}

echo "<h3>8. Recommendations</h3>\n";
echo "<ul>";
echo "<li>If MySQLi is not loaded, enable it in php.ini</li>";
echo "<li>If database connection fails, verify DB_HOST, DB_USER, DB_PASS in config.php</li>";
echo "<li>For XAMPP, default MySQL password is usually empty ('')</li>";
echo "<li>Make sure MySQL service is running in XAMPP</li>";
echo "<li>Check that database 'b2b_billing_system' exists</li>";
echo "<li>Review the error log for specific error messages</li>";
echo "</ul>";

echo "<h3>9. Next Steps</h3>\n";
echo "<ol>";
echo "<li>Verify MySQL is running in XAMPP Control Panel</li>";
echo "<li>Check that the database exists: CREATE DATABASE IF NOT EXISTS b2b_billing_system;</li>";
echo "<li>Verify database credentials in config.php match your MySQL setup</li>";
echo "<li>Clear error_log.txt and reload the page to see fresh errors</li>";
echo "<li>Enable display_errors in php.ini to see errors directly</li>";
echo "</ol>";

?>
