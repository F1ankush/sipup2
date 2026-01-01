<?php
/**
 * Hostinger Database Configuration Setup
 * Run this ONCE to save your Hostinger database credentials
 * DELETE THIS FILE AFTER USE FOR SECURITY
 */

// Protect this page - only accessible from local/same server
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
$isLocal = in_array($remoteIp, ['127.0.0.1', 'localhost', $_SERVER['SERVER_ADDR'] ?? '']);

// Allow access if password is provided
$accessGranted = false;
if (isset($_GET['token']) && $_GET['token'] === 'setup_' . md5('Sipup@2026')) {
    $accessGranted = true;
}

if (!$isLocal && !$accessGranted) {
    http_response_code(403);
    die('Access Denied. This script is not publicly accessible.');
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['password'] ?? '';
    $dbname = $_POST['dbname'] ?? '';

    // Validate inputs
    if (empty($user) || empty($dbname)) {
        $message = 'Database username and database name are required!';
        $messageType = 'error';
    } else {
        // Test connection
        try {
            $conn = @new mysqli($host, $user, $pass, $dbname);
            
            if ($conn->connect_error) {
                $message = 'Connection failed: ' . htmlspecialchars($conn->connect_error);
                $messageType = 'error';
            } else {
                // Save credentials to .db_config
                $credFile = __DIR__ . '/../.db_config';
                $data = [
                    'host' => $host,
                    'user' => $user,
                    'password' => $pass,
                    'dbname' => $dbname,
                    'saved_at' => date('Y-m-d H:i:s')
                ];
                
                if (file_put_contents($credFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX)) {
                    // Secure the file
                    @chmod($credFile, 0600);
                    $message = '✓ Database credentials saved successfully! You can now delete this file.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to save credentials. Check file permissions on includes/ folder.';
                    $messageType = 'error';
                }
                
                $conn->close();
            }
        } catch (Exception $e) {
            $message = 'Error: ' . htmlspecialchars($e->getMessage());
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hostinger Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
        button {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #bee5eb;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Hostinger Database Configuration</h1>
        
        <div class="info">
            <strong>Instructions:</strong>
            <ol>
                <li>Get your database credentials from <strong>Hostinger Control Panel</strong> → Databases</li>
                <li>Enter the credentials below</li>
                <li>Click Save</li>
                <li>Delete this file from your server</li>
            </ol>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="host">Database Host:</label>
                <input type="text" id="host" name="host" value="localhost" required>
                <div class="help-text">Usually "localhost" for Hostinger shared hosting</div>
            </div>

            <div class="form-group">
                <label for="user">Database User:</label>
                <input type="text" id="user" name="user" placeholder="e.g., u110596290_user" required>
                <div class="help-text">Your Hostinger database user (usually starts with u followed by numbers)</div>
            </div>

            <div class="form-group">
                <label for="password">Database Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your database password" required>
                <div class="help-text">Your database password from Hostinger</div>
            </div>

            <div class="form-group">
                <label for="dbname">Database Name:</label>
                <input type="text" id="dbname" name="dbname" placeholder="e.g., u110596290_b2b_billing" required>
                <div class="help-text">Your database name from Hostinger</div>
            </div>

            <button type="submit">💾 Save Configuration</button>
        </form>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <h3>📝 Where to find these credentials:</h3>
            <ol>
                <li>Log in to <strong>Hostinger Control Panel</strong></li>
                <li>Go to <strong>Databases</strong> or <strong>MySQL Databases</strong></li>
                <li>Find your database in the list</li>
                <li>Copy the Database Name, Database User, and Password</li>
                <li>Paste them in the form above</li>
            </ol>
        </div>

        <div style="margin-top: 20px; color: #999; font-size: 12px;">
            <strong>⚠️ IMPORTANT:</strong> Delete this file from your server after saving credentials.
            <br>File location: <code>setup_hostinger_db.php</code>
        </div>
    </div>
</body>
</html>
