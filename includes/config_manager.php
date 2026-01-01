<?php
/**
 * Smart Configuration Manager
 * Handles database credentials intelligently
 * Detects XAMPP setup and auto-configures
 */

class ConfigManager {
    private static $defaultCredentials = [
        'localhost' => [
            'host' => 'localhost',
            'user' => 'root',
            'pass' => 'Sipup@2026',
            'name' => 'b2b_billing_system',
            'dbname' => 'b2b_billing_system'
        ],
        'hostinger' => [
            'host' => 'localhost',
            'user' => 'u110596290_b22bsystem',
            'pass' => 'Sipup@2026',
            'name' => 'u110596290_b2bsystem',
            'dbname' => 'u110596290_b2bsystem'
        ]
    ];



    
    /**
     * Get database credentials based on current environment
     * Priority: .db_config file → Hostinger environment → localhost defaults
     */
    public static function getDBCredentials() {
        // PRIORITY 1: Check if we have a saved override from .db_config
        $credFile = __DIR__ . '/../.db_config';
        if (file_exists($credFile) && is_readable($credFile)) {
            $saved = json_decode(file_get_contents($credFile), true);
            if ($saved && !empty($saved['password'])) {
                // Ensure dbname key exists
                if (!isset($saved['dbname']) && isset($saved['name'])) {
                    $saved['dbname'] = $saved['name'];
                }
                return $saved;
            }
        }

        // PRIORITY 2: Try to detect if running on Hostinger
        $env = self::detectEnvironment();
        
        // Return credentials based on detected environment
        $credentials = self::$defaultCredentials[$env] ?? self::$defaultCredentials['localhost'];
        
        // Ensure dbname key exists
        if (!isset($credentials['dbname']) && isset($credentials['name'])) {
            $credentials['dbname'] = $credentials['name'];
        }
        
        return $credentials;
    }

    /**
     * Detect current hosting environment (localhost or Hostinger)
     */
    private static function detectEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        // Check for localhost/127.0.0.1 (XAMPP/local development)
        if (strpos($host, 'localhost') !== false || 
            strpos($host, '127.0.0.1') !== false) {
            return 'localhost';
        }
        
        // If not localhost, assume Hostinger or other production hosting
        return 'hostinger';
    }

    /**
     * Test database connection with given credentials
     */
    public static function testConnection($host, $user, $pass, $dbname) {
        try {
            $conn = @new mysqli($host, $user, $pass, $dbname);
            if ($conn->connect_error) {
                return false;
            }
            $conn->close();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Save working credentials
     */
    public static function saveCredentials($host, $user, $pass, $dbname) {
        $credFile = __DIR__ . '/../.db_config';
        $data = [
            'host' => $host,
            'user' => $user,
            'password' => $pass,
            'dbname' => $dbname,
            'saved_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($credFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        chmod($credFile, 0600); // Read/write only
        
        return true;
    }

    /**
     * Try multiple credential combinations
     */
    public static function autoDetectCredentials() {
        $possibleCombos = [
            // Try empty password first (XAMPP default)
            ['localhost', 'root', '', 'b2b_billing_system'],
            // Try with password
            ['localhost', 'root', 'Karan@1903', 'b2b_billing_system'],
            // Try b2b_billing_system user
            ['localhost', 'b2b_billing_system', '', 'b2b_billing_system'],
            ['localhost', 'b2b_billing_system', 'Karan@1903', 'b2b_billing_system'],
        ];

        foreach ($possibleCombos as $creds) {
            if (self::testConnection($creds[0], $creds[1], $creds[2], $creds[3])) {
                return [
                    'host' => $creds[0],
                    'user' => $creds[1],
                    'pass' => $creds[2],
                    'name' => $creds[3]
                ];
            }
        }

        return null; // None worked
    }
}
?>
