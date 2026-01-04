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
        $setupPath = '/setup_wizard.php'; // Relative path from root
        
        // Check if we're already on setup page to prevent redirect loop
        if (strpos($_SERVER['REQUEST_URI'] ?? '', 'setup_wizard.php') === false && 
            strpos($_SERVER['REQUEST_URI'] ?? '', 'config_api.php') === false) {
            header('Location: ' . $baseUrl . $setupPath, true, 302);
        }
        
        http_response_code(500);
        die("<h1>Database Configuration Required</h1><p>Please visit <a href='" . $baseUrl . $setupPath . "'>Setup Wizard</a> to configure your database connection.</p>");
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
