<?php
require_once 'config.php';
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Configuration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background:  #f5f5f5; }
        .container { max-width: 800px; margin:  0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #667eea; }
        .label { font-weight: bold; color: #555; }
        .value { color: #333; font-family: monospace; }
        . success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Streamity Configuration Test</h1>
        <p><strong>⚠️ DELETE THIS FILE AFTER TESTING!</strong></p>
        
        <div class="section">
            <h2>📋 PHP Configuration</h2>
            <p><span class="label">PHP Version:</span> <span class="value"><?php echo phpversion(); ?></span></p>
            <p><span class="label">Error Logging:</span> <span class="<? php echo ENABLE_ERROR_LOGGING ? 'success' : 'error'; ? >"><?php echo ENABLE_ERROR_LOGGING ? 'ENABLED' : 'DISABLED'; ?></span></p>
            <p><span class="label">Logs Directory:</span> <span class="value"><?php echo __DIR__ .  '/logs'; ?></span></p>
            <p><span class="label">Logs Writable:</span> <span class="<? php echo is_writable(__DIR__ . '/logs') ? 'success' : 'error'; ?>"><?php echo is_writable(__DIR__ . '/logs') ? 'YES' : 'NO'; ?></span></p>
        </div>
        
        <div class="section">
            <h2>💾 Database Configuration</h2>
            <? php $dbConfigured = ! empty($db_name) && !empty($db_username) && !empty($db_password); ?>
            <?php if (! $dbConfigured): ?>
                <p class="warning">⚠️ Database not configured! Edit config.php</p>
            <?php endif; ?>
            <p><span class="label">Host:</span> <span class="value"><?php echo htmlspecialchars($db_url); ?></span></p>
            <p><span class="label">Database:</span> <span class="value"><?php echo htmlspecialchars($db_name ?: '[NOT SET]'); ?></span></p>
            <p><span class="label">Username:</span> <span class="value"><?php echo htmlspecialchars($db_username ?: '[NOT SET]'); ?></span></p>
            <p><span class="label">Password:</span> <span class="<? php echo ! empty($db_password) ? 'success' : 'error'; ?>"><?php echo !empty($db_password) ? 'SET' : 'NOT SET'; ?></span></p>
            
            <? php if ($dbConfigured): ?>
                <p><strong>Database Connection Test:</strong></p>
                <? php
                $result = testDatabaseConnection();
                if ($result['status'] === 'success') {
                    echo '<p class="success">✅ ' . htmlspecialchars($result['message']) . '</p>';
                } else {
                    echo '<p class="error">❌ ' . htmlspecialchars($result['message']) . '</p>';
                }
                ?>
            <? php endif; ?>
        </div>
        
        <div class="section">
            <h2>⚙️ Application Configuration</h2>
            <p><span class="label">DNS:</span> <span class="value"><?php echo htmlspecialchars($dns); ?></span></p>
            <p><span class="label">CORS:</span> <span class="<? php echo $cors ? 'warning' : 'success'; ?>"><?php echo $cors ? 'ENABLED' : 'DISABLED'; ?></span></p>
            <p><span class="label">EPG URL:</span> <span class="value"><?php echo htmlspecialchars($epg_url ?: '[NOT SET]'); ?></span></p>
        </div>
        
        <div class="section">
            <h2>📁 Files</h2>
            <? php
            $files = [
                'config.php' => __DIR__ . '/config.php',
                'config.js' => __DIR__ . '/config.js',
                'config.css' => __DIR__ . '/config.css',
                'logger. php' => __DIR__ . '/logger.php',
                'logs/' => __DIR__ . '/logs'
            ];
            foreach ($files as $name => $path):
                $exists = file_exists($path);
            ?>
                <p><span class="label"><?php echo htmlspecialchars($name); ?>:</span> <span class="<? php echo $exists ? 'success' : 'error'; ?>"><?php echo $exists ? 'EXISTS' : 'MISSING'; ?></span></p>
            <?php endforeach; ?>
        </div>
        
        <div class="section">
            <h2>📄 Recent Logs</h2>
            <? php
            if (file_exists(APP_LOG_FILE_PATH)) {
                $lines = file(APP_LOG_FILE_PATH);
                $recent = array_slice($lines, -5);
                echo '<h3>Application Log (last 5 lines):</h3>';
                echo '<pre>' . htmlspecialchars(implode('', $recent)) . '</pre>';
            } else {
                echo '<p>No application log yet.</p>';
            }
            
            if (file_exists(LOG_FILE_PATH)) {
                $lines = file(LOG_FILE_PATH);
                $recent = array_slice($lines, -5);
                echo '<h3>PHP Errors (last 5 lines):</h3>';
                echo '<pre>' . htmlspecialchars(implode('', $recent)) . '</pre>';
            } else {
                echo '<p>No PHP errors logged. </p>';
            }
            ?>
        </div>
        
        <div class="section">
            <h2>💻 System Info</h2>
            <p><span class="label">Server:</span> <span class="value"><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></span></p>
            <p><span class="label">Document Root:</span> <span class="value"><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ??  'Unknown'); ?></span></p>
            <p><span class="label">Current Dir:</span> <span class="value"><?php echo htmlspecialchars(__DIR__); ?></span></p>
        </div>
        
        <p style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ff9800;">
            <strong>Remember:</strong> Delete this file (test-config.php) after verifying your setup!
        </p>
    </div>
</body>
</html>
