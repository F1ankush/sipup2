<?php
/**
 * Smart Configuration Manager
 * Handles database credentials intelligently
 * Detects XAMPP setup and auto-configures
 */

class ConfigManager {
    private static $defaultCredentials = [
        'xampp' => [
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'name' => 'b2b_billing_system'
        ],
        'production' => [
            'host' => 'localhost',
            'user' => 'b2b_billing_system',
            'pass' => 'Karan@1903',
            'name' => 'b2b_billing_system'
        ]
    ];

    /**
     * Get database credentials based on current environment
     */
    public static function getDBCredentials() {
        // Check if we have a saved override
        $credFile = __DIR__ . '/../.db_config';
        if (file_exists($credFile) && is_readable($credFile)) {
            $saved = json_decode(file_get_contents($credFile), true);
            if ($saved && !empty($saved['password'])) {
                return $saved;
            }
        }

        // Try to detect environment
        $env = self::detectEnvironment();
        
        // Try credentials in order: saved config → detected env → XAMPP default
        $credentials = self::$defaultCredentials[$env] ?? self::$defaultCredentials['xampp'];
        
        return $credentials;
    }

    /**
     * Detect if running on XAMPP or production
     */
    private static function detectEnvironment() {
        // Check various indicators
        if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || 
            strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false) {
            return 'xampp';
        }
        
        return 'xampp'; // Default to XAMPP for safety
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
