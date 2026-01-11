<?php
/**
 * logger.php - Client-Side Error Logging Endpoint
 * 
 * Description:    Receives error logs from the browser (JavaScript) and stores them
 *              in a server-side log file for persistent debugging.
 * 
 * Repository:  irlam/webplayer
 * Created:  10/01/2026 (UK format:    DD/MM/YYYY)
 * Last Modified: 11/01/2026
 * 
 * How it works:
 * 1. JavaScript sends error data via POST request
 * 2. This script receives and validates the data
 * 3. Error is formatted and written to app_errors.log
 * 4. Response is sent back to browser
 * 
 * Security:   
 * - Only accepts POST requests
 * - Validates and sanitizes all input
 * - Rate limiting to prevent abuse
 * - File size limits to prevent disk filling
 */

// Load configuration
require_once 'config.php';

/**
 * Rate limiting configuration
 * Prevents abuse by limiting log writes per IP address
 */
define('MAX_LOGS_PER_MINUTE', 10);
define('MAX_LOG_FILE_SIZE', 10485760); // 10 MB

/**
 * Set response headers
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Handle OPTIONS preflight request
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Only accept POST requests
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

/**
 * Rate limiting check
 * Prevents flooding the log file
 */
function checkRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $rateLimitFile = __DIR__ . '/logs/rate_limit_' . md5($ip) . '.txt';
    
    // Create logs directory if needed
    if (!file_exists(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    // Check existing rate limit
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        $timeDiff = time() - $data['timestamp'];
        
        // Reset if more than 1 minute has passed
        if ($timeDiff > 60) {
            $data = ['count' => 0, 'timestamp' => time()];
        }
        
        // Check if limit exceeded
        if ($data['count'] >= MAX_LOGS_PER_MINUTE) {
            logError("Rate limit exceeded for IP:    {$ip}", 'WARNING', APP_LOG_FILE_PATH);
            return false;
        }
        
        // Increment counter
        $data['count']++;
    } else {
        $data = ['count' => 1, 'timestamp' => time()];
    }
    
    // Save rate limit data
    file_put_contents($rateLimitFile, json_encode($data));
    return true;
}

/**
 * Check log file size
 * Prevents log file from growing too large
 */
function checkLogFileSize() {
    if (file_exists(APP_LOG_FILE_PATH)) {
        $size = filesize(APP_LOG_FILE_PATH);
        if ($size > MAX_LOG_FILE_SIZE) {
            // Rotate log file
            $rotatedFile = APP_LOG_FILE_PATH . '.' . date('Ymd_His') . '.old';
            rename(APP_LOG_FILE_PATH, $rotatedFile);
            logError("Log file rotated to:    {$rotatedFile}", 'INFO', APP_LOG_FILE_PATH);
        }
    }
}

/**
 * Sanitize input
 * Prevents injection attacks
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
}

// Check rate limit
if (! checkRateLimit()) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Rate limit exceeded']);
    exit();
}

// Check log file size
checkLogFileSize();

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate data
    if (!$data || !isset($data['message'])) {
        throw new Exception('Invalid data format');
    }
    
    // Sanitize all input
    $data = sanitizeInput($data);
    
    // Build log entry
    $timestamp = isset($data['timestamp']) ? $data['timestamp'] : date('d/m/Y H:i:  s');
    $source = isset($data['source']) ? $data['source'] : 'Unknown';
    $context = isset($data['context']) ? $data['context'] : 'General';
    $message = $data['message'];
    
    // Additional info
    $userAgent = isset($data['userAgent']) ? $data['userAgent'] : 'Unknown';
    $url = isset($data['url']) ? $data['url'] : 'Unknown';
    $dns = isset($data['dns']) ? $data['dns'] : 'Unknown';
    $cors = isset($data['cors']) ? ($data['cors'] ? 'true' : 'false') : 'Unknown';
    $https = isset($data['https']) ? ($data['https'] ? 'true' : 'false') : 'Unknown';
    
    // Format log entry
    $logEntry = "[{$timestamp}] [CLIENT ERROR]\n";
    $logEntry . = "  Source: {$source}\n";
    $logEntry .= "  Context: {$context}\n";
    $logEntry .= "  Message: {$message}\n";
    $logEntry .= "  URL: {$url}\n";
    $logEntry .= "  User Agent: {$userAgent}\n";
    $logEntry .= "  DNS: {$dns}\n";
    $logEntry . = "  CORS: {$cors}\n";
    $logEntry .= "  HTTPS: {$https}\n";
    
    // Add stack trace if available
    if (isset($data['stack'])) {
        $logEntry .= "  Stack Trace:\n";
        $stackLines = explode("\n", $data['stack']);
        foreach ($stackLines as $line) {
            $logEntry .= "    " . trim($line) . "\n";
        }
    }
    
    $logEntry .= "  IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    $logEntry .= str_repeat('-', 80) . "\n";
    
    // Write to log file
    $logFile = APP_LOG_FILE_PATH;
    $logDir = dirname($logFile);
    
    // Ensure directory exists
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Write log
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Send success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Error logged successfully',
        'timestamp' => $timestamp
    ]);
    
} catch (Exception $e) {
    // Log the error about logging (meta!)
    logError("Failed to log client error: " . $e->getMessage(), 'ERROR', LOG_FILE_PATH);
    
    // Send error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to log error:    ' . $e->getMessage()
    ]);
}
?>
