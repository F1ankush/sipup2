<?php
/**
 * System Health Check
 * Verify that intelligent configuration system is working
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health Check</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .status-overall {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 5px;
        }
        .status-overall.good {
            background: #e0ffe0;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }
        .status-overall.bad {
            background: #ffe0e0;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        .check-item {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        .check-item.pass {
            background: #f0fff0;
            border-left-color: #27ae60;
        }
        .check-item.fail {
            background: #fff0f0;
            border-left-color: #e74c3c;
        }
        .check-item.warning {
            background: #fffff0;
            border-left-color: #f39c12;
        }
        .check-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .check-label .icon {
            font-size: 20px;
        }
        .check-detail {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
        }
        .code-block {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #5568d3;
        }
        .button.secondary {
            background: #f0f0f0;
            color: #333;
            margin-left: 10px;
        }
        .button.secondary:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 System Health Check</h1>
            <p>Intelligent Configuration System Status</p>
        </div>

        <?php
        // Perform all checks
        $checks = [];
        $allPass = true;

        // Check 1: PHP Version
        $phpOk = version_compare(PHP_VERSION, '7.0', '>=');
        $checks['PHP Version'] = [
            'pass' => $phpOk,
            'detail' => 'PHP ' . PHP_VERSION . ($phpOk ? ' (OK)' : ' (Too old)')
        ];
        if (!$phpOk) $allPass = false;

        // Check 2: MySQLi Extension
        $mysqliOk = extension_loaded('mysqli');
        $checks['MySQLi Extension'] = [
            'pass' => $mysqliOk,
            'detail' => $mysqliOk ? 'Loaded and available' : 'NOT FOUND - Database connections will fail'
        ];
        if (!$mysqliOk) $allPass = false;

        // Check 3: Config Manager exists
        $configMgrExists = file_exists(__DIR__ . '/includes/config_manager.php');
        $checks['Smart Config Manager'] = [
            'pass' => $configMgrExists,
            'detail' => $configMgrExists ? 'File: includes/config_manager.php ✓' : 'NOT FOUND'
        ];
        if (!$configMgrExists) $allPass = false;

        // Check 4: Config Manager can be loaded
        $configMgrLoads = false;
        if ($configMgrExists) {
            try {
                require_once __DIR__ . '/includes/config_manager.php';
                $configMgrLoads = class_exists('ConfigManager');
            } catch (Exception $e) {
                $configMgrLoads = false;
            }
        }
        $checks['Config Manager Class'] = [
            'pass' => $configMgrLoads,
            'detail' => $configMgrLoads ? 'ConfigManager class loaded successfully' : 'Failed to load class'
        ];
        if (!$configMgrLoads) $allPass = false;

        // Check 5: .db_config exists
        $dbConfigExists = file_exists(__DIR__ . '/.db_config');
        $checks['Saved Credentials'] = [
            'pass' => $dbConfigExists,
            'detail' => $dbConfigExists ? '.db_config file found (credentials saved)' : '.db_config not yet created (will be auto-created on first successful connection)',
            'warning' => true
        ];

        // Check 6: Setup wizard exists
        $wizardExists = file_exists(__DIR__ . '/setup_wizard.php');
        $checks['Setup Wizard'] = [
            'pass' => $wizardExists,
            'detail' => $wizardExists ? 'File: setup_wizard.php ✓' : 'NOT FOUND'
        ];
        if (!$wizardExists) $allPass = false;

        // Check 7: Config API exists
        $apiExists = file_exists(__DIR__ . '/config_api.php');
        $checks['Configuration API'] = [
            'pass' => $apiExists,
            'detail' => $apiExists ? 'File: config_api.php ✓' : 'NOT FOUND'
        ];
        if (!$apiExists) $allPass = false;

        // Check 8: Main config file
        $mainConfigExists = file_exists(__DIR__ . '/includes/config.php');
        $checks['Main Configuration'] = [
            'pass' => $mainConfigExists,
            'detail' => $mainConfigExists ? 'File: includes/config.php ✓' : 'NOT FOUND - Critical!'
        ];
        if (!$mainConfigExists) $allPass = false;

        // Check 9: Database class
        $dbClassExists = file_exists(__DIR__ . '/includes/db.php');
        $checks['Database Class'] = [
            'pass' => $dbClassExists,
            'detail' => $dbClassExists ? 'File: includes/db.php ✓' : 'NOT FOUND - Critical!'
        ];
        if (!$dbClassExists) $allPass = false;

        // Check 10: Error log
        $errorLogExists = file_exists(__DIR__ . '/error_log.txt');
        $checks['Error Logging'] = [
            'pass' => $errorLogExists,
            'detail' => $errorLogExists ? 'error_log.txt found' : 'Not yet created',
            'warning' => true
        ];

        // Display overall status
        if ($allPass) {
            echo '<div class="status-overall good">✅ All critical systems operational! Your system is ready to go.</div>';
        } else {
            echo '<div class="status-overall bad">❌ Some critical systems are missing or not working properly.</div>';
        }

        // Display all checks
        echo '<div class="section"><div class="section-title">System Components</div>';

        foreach ($checks as $name => $result) {
            $class = $result['pass'] ? 'pass' : ($result['warning'] ?? false ? 'warning' : 'fail');
            $icon = $result['pass'] ? '✅' : '❌';
            if ($result['warning'] ?? false) $icon = '⚠️';
            
            echo '<div class="check-item ' . $class . '">';
            echo '<div class="check-label"><span class="icon">' . $icon . '</span>' . $name . '</div>';
            echo '<div class="check-detail">' . $result['detail'] . '</div>';
            echo '</div>';
        }

        echo '</div>';

        // Display database credentials status
        echo '<div class="section"><div class="section-title">Database Configuration Status</div>';

        if ($configMgrLoads) {
            try {
                $creds = ConfigManager::getDBCredentials();
                echo '<div class="check-item pass">';
                echo '<div class="check-label"><span class="icon">✅</span>Active Credentials</div>';
                echo '<div class="check-detail">Host: ' . htmlspecialchars($creds['host']) . '<br>';
                echo 'User: ' . htmlspecialchars($creds['user']) . '<br>';
                echo 'Database: ' . htmlspecialchars($creds['name']) . '</div>';
                echo '</div>';

                // Try to test connection
                if (ConfigManager::testConnection($creds['host'], $creds['user'], $creds['pass'], $creds['name'])) {
                    echo '<div class="check-item pass">';
                    echo '<div class="check-label"><span class="icon">✅</span>Database Connection Test</div>';
                    echo '<div class="check-detail">Successfully connected to database!</div>';
                    echo '</div>';
                } else {
                    echo '<div class="check-item fail">';
                    echo '<div class="check-label"><span class="icon">❌</span>Database Connection Test</div>';
                    echo '<div class="check-detail">Could not connect with current credentials. System will auto-detect or show setup wizard.</div>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="check-item warning">';
                echo '<div class="check-label"><span class="icon">⚠️</span>Configuration Check</div>';
                echo '<div class="check-detail">Unable to retrieve configuration: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
            }
        }

        echo '</div>';

        // Display next steps
        echo '<div class="section"><div class="section-title">What to do now</div>';
        echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">';
        echo '<h3 style="margin-bottom: 10px; color: #333;">✨ Quick Start (2 minutes)</h3>';
        echo '<ol style="margin-left: 20px; line-height: 1.8; color: #555;">';
        echo '<li>Open XAMPP Control Panel</li>';
        echo '<li>Click "Start" next to MySQL</li>';
        echo '<li>Refresh your browser: <strong>https://paninitech.in/</strong></li>';
        echo '<li>Done! Website should load automatically ✅</li>';
        echo '</ol>';
        echo '</div>';

        echo '<h3 style="margin-top: 20px; margin-bottom: 10px; color: #333;">If automatic doesn\'t work:</h3>';
        echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">';
        echo '<a href="setup_wizard.php" class="button">Open Setup Wizard</a>';
        echo '</div>';

        echo '</div>';

        // Technical details
        echo '<div class="section"><div class="section-title">How It Works</div>';
        echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; font-size: 13px; color: #555; line-height: 1.8;">';
        echo '<p><strong>1. Smart Detection:</strong> System tries multiple credential combinations automatically</p>';
        echo '<p style="margin-top: 10px;"><strong>2. Auto-Save:</strong> Once working credentials found, saved to .db_config</p>';
        echo '<p style="margin-top: 10px;"><strong>3. Fallback:</strong> If all else fails, shows beautiful setup wizard</p>';
        echo '<p style="margin-top: 10px;"><strong>4. Self-Healing:</strong> Never breaks due to wrong password in config.php</p>';
        echo '</div>';
        echo '</div>';

        // Conclusion
        echo '<div style="text-align: center; margin-top: 40px; padding-top: 30px; border-top: 2px solid #f0f0f0;">';
        if ($allPass) {
            echo '<h3 style="color: #27ae60; margin-bottom: 15px;">🎉 Your system is ready!</h3>';
            echo '<p style="color: #666; margin-bottom: 20px;">All components are in place. Just start MySQL and your website will work automatically.</p>';
        } else {
            echo '<h3 style="color: #e74c3c; margin-bottom: 15px;">⚠️ Some components need attention</h3>';
            echo '<p style="color: #666; margin-bottom: 20px;">Please make sure all critical files are present.</p>';
        }
        echo '<a href="https://paninitech.in/" class="button">Go to Website</a>';
        echo '<a href="setup_wizard.php" class="button secondary">Configure Database</a>';
        echo '</div>';

        ?>
    </div>
</body>
</html>
