<?php
/**
 * config.php - Streamity IPTV Player PHP Configuration
 * 
 * Description: PHP backend configuration for database connection, EPG (Electronic
 *              Program Guide) management, and comprehensive error logging. 
 * 
 * Repository: irlam/webplayer
 * Created: 10/01/2026 (UK format:   DD/MM/YYYY)
 * Last Modified: 11/01/2026 - Added comprehensive error logging
 * 
 * IMPORTANT: Configure your MySQL database settings below!  
 * 
 * Setup Steps:
 * 1. Create a MySQL database in cPanel (MySQL Database Wizard)
 * 2. Note down:   database name, username, password
 * 3. Import sql_table.sql into your database (via phpMyAdmin)
 * 4. Fill in the values below
 * 5. [Optional] Add EPG XML URL from your IPTV provider
 */

/*----- Error Logging Configuration -----*/
/**
 * Enable detailed PHP error logging
 * Set to true during setup/debugging
 * Set to false in production
 */
define('ENABLE_ERROR_LOGGING', true);

/**
 * Log file paths
 * All PHP errors and database errors will be written here
 */
define('LOG_FILE_PATH', __DIR__ . '/logs/php_errors.log');
define('DB_LOG_FILE_PATH', __DIR__ . '/logs/database_errors.log');
define('APP_LOG_FILE_PATH', __DIR__ . '/logs/app_errors.log');

/**
 * Error logging function
 * Logs errors to file with timestamp (UK format)
 * 
 * @param string $message Error message to log
 * @param string $type Error type (ERROR, WARNING, INFO)
 * @param string $file Log file to write to (default: php_errors.log)
 */
function logError($message, $type = 'ERROR', $file = LOG_FILE_PATH) {
    if (! ENABLE_ERROR_LOGGING) return;
    
    // Ensure logs directory exists
    $logDir = dirname($file);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Create log entry with UK timestamp
    $timestamp = date('d/m/Y H:i: s');
    $logEntry = "[{$timestamp}] [{$type}] {$message}\n";
    
    // Write to log file
    error_log($logEntry, 3, $file);
    
    // Also write to PHP error log
    error_log("[Streamity] {$type}: {$message}");
}

/**
 * Log configuration load
 */
logError('config.php loaded successfully', 'INFO', APP_LOG_FILE_PATH);

/*----- Database Configuration -----*/
// MySQL database server (usually "localhost" for cPanel)
$db_url = "localhost";

// Database name - CREATE THIS IN CPANEL FIRST!  
// Example: "omxfcmit_streamity"
$db_name = "omxfcmit_webplayer"; // ⚠️ FILL THIS IN

// Database username - CREATE THIS IN CPANEL FIRST!
// Example: "omxfcmit_user"
$db_username = "omxfcmit_webplayer"; // ⚠️ FILL THIS IN

// Database password - SET THIS WHEN CREATING USER IN CPANEL
// Example: "YourSecurePassword123!"
$db_password = "Subaru5554346"; // ⚠️ FILL THIS IN

/*----- EPG (Electronic Program Guide) Configuration -----*/
/**
 * EPG XML URL from your IPTV provider
 * 
 * This provides the TV guide/schedule information.   
 * Format example: "http://provider.com:80/xmltv. php?username=USER&password=PASS"
 * 
 * If you don't have EPG, leave empty ("") - the player will still work
 * but won't show TV guide information.  
 */
$epg_url = ""; // Optional - leave empty if you don't have EPG

/**
 * EPG update frequency
 * 
 * Defines how often EPG data is considered "stale" and needs updating.   
 * When a user logs in, if EPG data is older than this value, it updates.
 * 
 * Options:  "+12 hours", "+1 day", "+2 days", "+1 week"
 */
$epg_valid_hours = "+12 hours";

/*----- CORS Configuration -----*/
/**
 * Set to true ONLY if window.cors = true in config.js
 * This must match the CORS setting in config.js
 */
$cors = true;

/**
 * DNS URL - MUST MATCH window.dns in config.js
 * Used for validation and security in proxy. php
 * 
 * Example: "http://iptv.example.com:8080"
 */
$dns = "https://x.3ovus.net"; // ⚠️ MUST MATCH config.js

/*----- Configuration Validation -----*/
/**
 * Validate database configuration
 * Logs warnings if critical settings are missing
 */
function validateConfig() {
    global $db_name, $db_username, $db_password, $dns;
    
    $issues = [];
    
    // Check database configuration
    if (empty($db_name)) {
        $issues[] = "Database name is not configured";
        logError("Database name is empty - please configure in config.php", 'WARNING', APP_LOG_FILE_PATH);
    }
    
    if (empty($db_username)) {
        $issues[] = "Database username is not configured";
        logError("Database username is empty - please configure in config.php", 'WARNING', APP_LOG_FILE_PATH);
    }
    
    if (empty($db_password)) {
        $issues[] = "Database password is not configured";
        logError("Database password is empty - please configure in config.php", 'WARNING', APP_LOG_FILE_PATH);
    }
    
    // Check DNS configuration
    if ($dns === "http://domain.com:80") {
        $issues[] = "DNS is set to default value";
        logError("DNS not configured - still using default value", 'WARNING', APP_LOG_FILE_PATH);
    }
    
    // Log validation results
    if (count($issues) > 0) {
        logError("Configuration validation found " . count($issues) . " issue(s): " . implode(", ", $issues), 'WARNING', APP_LOG_FILE_PATH);
        return false;
    } else {
        logError("Configuration validation passed", 'INFO', APP_LOG_FILE_PATH);
        return true;
    }
}

// Run validation
validateConfig();

/**
 * Database connection test function
 * Tests if database connection can be established
 * 
 * @return array Connection result with status and message
 */
function testDatabaseConnection() {
    global $db_url, $db_name, $db_username, $db_password;
    
    // Skip if database not configured
    if (empty($db_name) || empty($db_username)) {
        return [
            'status' => 'error',
            'message' => 'Database not configured'
        ];
    }
    
    try {
        // Attempt connection
        $conn = new mysqli($db_url, $db_username, $db_password, $db_name);
        
        // Check connection
        if ($conn->connect_error) {
            logError("Database connection failed: " .  $conn->connect_error, 'ERROR', DB_LOG_FILE_PATH);
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $conn->connect_error
            ];
        }
        
        logError("Database connection successful", 'INFO', DB_LOG_FILE_PATH);
        $conn->close();
        
        return [
            'status' => 'success',
            'message' => 'Database connection successful'
        ];
        
    } catch (Exception $e) {
        logError("Database connection exception: " . $e->getMessage(), 'ERROR', DB_LOG_FILE_PATH);
        return [
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage()
        ];
    }
}

/**
 * Custom error handler
 * Catches all PHP errors and logs them
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (! ENABLE_ERROR_LOGGING) return false;
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER DEPRECATED'
    ];
    
    $errorType = isset($errorTypes[$errno]) ? $errorTypes[$errno] : 'UNKNOWN';
    $message = "{$errorType} in {$errfile} on line {$errline}: {$errstr}";
    
    logError($message, $errorType, LOG_FILE_PATH);
    
    // Don't execute PHP's internal error handler
    return false;
}

// Set custom error handler
set_error_handler("customErrorHandler");

/**
 * Shutdown function
 * Catches fatal errors that can't be caught by error handler
 */
function shutdownHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        logError(
            "FATAL ERROR in {$error['file']} on line {$error['line']}: {$error['message']}", 
            'FATAL', 
            LOG_FILE_PATH
        );
    }
}

// Register shutdown handler
register_shutdown_function('shutdownHandler');

/**
 * Performance Note:   
 * EPG updates only trigger when:   
 * 1. A user logs in
 * 2. Current EPG data is older than $epg_valid_hours
 * 
 * This prevents unnecessary server load.   
 */

// Log configuration summary
if (ENABLE_ERROR_LOGGING) {
    $configSummary = "Configuration loaded - DB: " . ($db_name ?: 'NOT SET') . 
                     ", DNS: " .  $dns . 
                     ", CORS: " . ($cors ? 'enabled' : 'disabled') . 
                     ", EPG: " . ($epg_url ?  'configured' : 'not configured');
    logError($configSummary, 'INFO', APP_LOG_FILE_PATH);
}
?>
