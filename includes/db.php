<?php
require_once 'config.php';
require_once 'config_manager.php';

class Database {
    private $connection;
    private $isConnected = false;
    private $lastError = '';
    
    public function __construct() {
        try {
            // Try configured credentials first
            if (!$this->tryConnection(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
                // If that fails, try auto-detection
                $detected = ConfigManager::autoDetectCredentials();
                if ($detected && $this->tryConnection($detected['host'], $detected['user'], $detected['pass'], $detected['dbname'] ?? $detected['name'])) {
                    // Save the working credentials
                    ConfigManager::saveCredentials($detected['host'], $detected['user'], $detected['pass'], $detected['dbname'] ?? $detected['name']);
                } else {
                    // All attempts failed, redirect to setup wizard
                    $this->redirectToSetup();
                }
            }
        } catch (Exception $e) {
            error_log("Database Constructor Error: " . $e->getMessage());
            $this->redirectToSetup();
        }
    }
    
    private function tryConnection($host, $user, $pass, $dbname) {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $this->connection = new mysqli($host, $user, $pass, $dbname);
            $this->connection->set_charset("utf8mb4");
            
            if (!$this->connection->ping()) {
                return false;
            }
            
            $this->isConnected = true;
            return true;
            
        } catch (mysqli_sql_exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
    
    private function redirectToSetup() {
        error_log("Database connection failed: " . $this->lastError . " - Redirecting to setup wizard");
        
        // Get the base URL dynamically
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $baseUrl = $protocol . $_SERVER['HTTP_HOST'];
        $setupPath = '/setup_wizard.php';
        
        // Check if we're already on setup page to prevent redirect loop
        if (strpos($_SERVER['REQUEST_URI'] ?? '', 'setup_wizard.php') === false && 
            strpos($_SERVER['REQUEST_URI'] ?? '', 'config_api.php') === false) {
            header('Location: ' . $baseUrl . $setupPath, true, 302);
        }
        
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Configuration Required</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Arial, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                }
                .container {
                    background: white;
                    border-radius: 10px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-width: 600px;
                    width: 100%;
                    padding: 40px;
                    text-align: center;
                }
                h1 {
                    color: #e74c3c;
                    margin-top: 0;
                }
                p {
                    color: #555;
                    line-height: 1.6;
                    margin: 15px 0;
                }
                .button {
                    display: inline-block;
                    background: #667eea;
                    color: white;
                    padding: 12px 30px;
                    border-radius: 5px;
                    text-decoration: none;
                    margin-top: 20px;
                    font-weight: 600;
                }
                .button:hover {
                    background: #5568d3;
                }
                .info-box {
                    background: #f0f4ff;
                    border-left: 4px solid #667eea;
                    padding: 15px;
                    text-align: left;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                .info-box strong {
                    color: #333;
                }
                code {
                    background: #f5f5f5;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-family: monospace;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>⚠️ Database Configuration Required</h1>
                <p>Your system is not connected to the database yet.</p>
                
                <div class="info-box">
                    <strong>What to do:</strong><br>
                    Click the button below to configure your database connection. 
                    You'll need your database credentials from your hosting control panel.
                </div>
                
                <div class="info-box">
                    <strong>For Hostinger Users:</strong><br>
                    Log in to cPanel → Databases section<br>
                    Find your database name and username<br>
                    Copy your password<br>
                    Then come back and enter these credentials
                </div>
                
                <a href="<?php echo $baseUrl . $setupPath; ?>" class="button">
                    ➜ Open Configuration Wizard
                </a>
                
                <p style="color: #999; font-size: 12px; margin-top: 30px;">
                    If you continue to see this message after configuration, 
                    check your database credentials and try again.
                </p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    public function getConnection() {
        if (!$this->isConnected || !$this->connection) {
            throw new Exception("Database connection is not available");
        }
        return $this->connection;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function escape($str) {
        return $this->connection->real_escape_string($str);
    }
    
    public function getLastId() {
        return $this->connection->insert_id;
    }
    
    public function affectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function close() {
        $this->connection->close();
    }
}

// Create global database instance
$db = new Database();
?>
