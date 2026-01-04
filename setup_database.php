<?php
/**
 * Database Configuration & Setup Wizard
 * Use this to configure your database on Hostinger
 */

$isConfigured = false;
$testResult = '';
$errorMessage = '';

// Check if .db_config exists and is valid
$configFile = __DIR__ . '/.db_config';
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    if ($config && !empty($config['password'])) {
        $isConfigured = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'test') {
        $host = $_POST['db_host'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';
        $dbname = $_POST['db_name'] ?? '';
        
        // Test connection
        try {
            $conn = new mysqli($host, $user, $pass, $dbname);
            if ($conn->connect_error) {
                $errorMessage = "Connection Failed: " . $conn->connect_error;
            } else {
                $testResult = "✓ Database connection successful!";
                $conn->close();
            }
        } catch (Exception $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'save') {
        $host = $_POST['db_host'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';
        $dbname = $_POST['db_name'] ?? '';
        
        // Test before saving
        try {
            $conn = new mysqli($host, $user, $pass, $dbname);
            if ($conn->connect_error) {
                $errorMessage = "Cannot save: Connection Failed - " . $conn->connect_error;
            } else {
                $conn->close();
                
                // Save configuration
                $config = [
                    'host' => $host,
                    'user' => $user,
                    'password' => $pass,
                    'dbname' => $dbname,
                    'saved_at' => date('Y-m-d H:i:s')
                ];
                
                if (file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
                    $testResult = "✓ Configuration saved successfully! The system will now use these credentials.";
                    $isConfigured = true;
                } else {
                    $errorMessage = "Failed to save configuration. Check file permissions.";
                }
            }
        } catch (Exception $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}

// Load current config if exists
$currentConfig = ['host' => '', 'user' => '', 'pass' => '', 'dbname' => ''];
if ($isConfigured && file_exists($configFile)) {
    $currentConfig = json_decode(file_get_contents($configFile), true);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration Wizard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 { 
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle { 
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-box {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .status-configured {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-not-configured {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #f5c6cb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-test {
            background: #17a2b8;
            color: white;
        }
        .btn-test:hover {
            background: #138496;
        }
        .btn-save {
            background: #28a745;
            color: white;
        }
        .btn-save:hover {
            background: #218838;
        }
        .help-text {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
            border-left: 4px solid #667eea;
        }
        .help-text h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .help-text ol {
            margin-left: 20px;
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }
        .help-text li {
            margin-bottom: 8px;
        }
        .code {
            background: #333;
            color: #0f0;
            padding: 10px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Configuration</h1>
        <p class="subtitle">Configure your database connection for this application</p>
        
        <?php if ($isConfigured): ?>
            <div class="status-box status-configured">
                ✓ Database is configured and ready to use
            </div>
        <?php else: ?>
            <div class="status-box status-not-configured">
                ⚠ Database is not yet configured. Please enter your database credentials.
            </div>
        <?php endif; ?>
        
        <?php if ($testResult): ?>
            <div class="alert alert-success">✓ <?php echo htmlspecialchars($testResult); ?></div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-error">✗ <?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="db_host">Database Host</label>
                <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($currentConfig['host'] ?? 'localhost'); ?>" required>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Usually: localhost (local) or your hosting provider's database host</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="db_user">Database Username</label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($currentConfig['user'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($currentConfig['password'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="db_name">Database Name</label>
                <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($currentConfig['dbname'] ?? ''); ?>" required>
            </div>
            
            <div class="button-group">
                <button type="submit" name="action" value="test" class="btn-test">Test Connection</button>
                <button type="submit" name="action" value="save" class="btn-save">Save Configuration</button>
            </div>
        </form>
        
        <div class="help-text">
            <h3>📋 How to Find Your Hostinger Database Credentials</h3>
            <ol>
                <li>Log in to your Hostinger Control Panel at hpanel.hostinger.com</li>
                <li>Go to <strong>Databases</strong> → <strong>MySQL Databases</strong></li>
                <li>Find your database or create a new one</li>
                <li>Copy the credentials:
                    <div class="code">
Database Name: u110596290_b2bsystem<br>
Username: u110596290_b22bsystem<br>
Password: [Your password]<br>
Host: localhost
                    </div>
                </li>
                <li>Enter these values above and click "Test Connection"</li>
                <li>Once successful, click "Save Configuration"</li>
            </ol>
        </div>
        
        <?php if ($isConfigured): ?>
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: 500;">← Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - B2B Retailer Platform</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .status.success {
            background-color: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .status.error {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .status.info {
            background-color: #d1ecf1;
            border: 2px solid #17a2b8;
            color: #0c5460;
        }
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .stat-box {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
        }
        .stat-box .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
        }
        .stat-box.success {
            background-color: #d4edda;
        }
        .stat-box.warning {
            background-color: #fff3cd;
        }
        .next-steps {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .next-steps h3 {
            margin-top: 0;
            color: #333;
        }
        .next-steps ol {
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 10px;
            color: #555;
        }
        .next-steps a {
            color: #667eea;
            text-decoration: none;
        }
        .next-steps a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            font-size: 1rem;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Setup Complete</h1>
        
        <div class="status success">
            <strong>✓ Setup Successful!</strong> Your database has been initialized.
        </div>

        <div class="stats">
            <div class="stat-box success">
                <h3>Executed Statements</h3>
                <div class="number"><?php echo $successCount; ?></div>
            </div>
            <?php if ($errorCount > 0): ?>
            <div class="stat-box warning">
                <h3>Warnings</h3>
                <div class="number"><?php echo $errorCount; ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="next-steps">
            <h3>What's Next?</h3>
            <ol>
                <li><strong>Create Admin Account:</strong> Go to <a href="admin/setup.php">Admin Setup Page</a></li>
                <li><strong>Add Products:</strong> Login to admin panel and add your product catalog</li>
                <li><strong>Review Home Page:</strong> Visit <a href="index.php">Homepage</a></li>
                <li><strong>Test Application:</strong> Try the <a href="pages/apply.php">retailer application form</a></li>
            </ol>
        </div>

        <a href="index.php" class="button">Go to Homepage</a>
    </div>
</body>
</html>
