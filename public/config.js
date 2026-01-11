/**
 * config.js - Streamity IPTV Player Configuration
 * 
 * Description:   Main configuration file for the IPTV web player. 
 *              Contains settings for IPTV provider connection, CORS handling,
 *              optional TMDB movie database integration, and comprehensive
 *              client-side error logging.
 * 
 * Repository: irlam/webplayer
 * Created: 10/01/2026 (UK format:  DD/MM/YYYY)
 * Last Modified: 11/01/2026 - Added error logging system
 * 
 * IMPORTANT: You MUST configure the settings below before the player will work! 
 * 
 * Configuration Steps:
 * 1. Set window.dns to your IPTV provider's URL
 * 2. Configure CORS settings if needed
 * 3. Set HTTPS to true if your streams use SSL
 * 4. [Optional] Add TMDB API key for enhanced movie info
 * 5. Set enableErrorLogging to true for debugging
 */

/*----- Player Configuration -----*/
// The name displayed in the browser title and header
window.playername = "SMIPTV Web Player";

/*----- DNS Configuration -----*/
// CRITICAL: Your IPTV provider's DNS URL
// Example: "http://iptv.example.com:8080" or "http://123.456.789.0:8080"
// 
// ⚠️ REPLACE THIS WITH YOUR ACTUAL IPTV PROVIDER URL ⚠️
window.dns = "https://x.3ovus.net";

// If you don't have an IPTV provider yet, you can use a demo/test server
// Or leave as is and configure later from the login screen

/*----- CORS (Cross-Origin Resource Sharing) -----*/
/**
 * Set to false if your IPTV provider has CORS enabled (most providers).
 * Set to true if you get CORS errors in browser console.
 * 
 * When true, the player uses proxy. php to bypass CORS restrictions.
 * If setting to true, you must also configure config.php
 */
window.cors = true;

/*----- HTTPS Configuration -----*/
/**
 * Set to true if your IPTV streams use HTTPS/SSL protocol
 * Set to false for standard HTTP streams
 * 
 * Most IPTV providers use HTTP (false), but some premium services use HTTPS
 */
window.https = true;

/*----- TMDB API [OPTIONAL] -----*/
/**
 * The Movie Database (TMDB) API key for enhanced movie/series information
 * 
 * The player will use info from your IPTV provider by default.  
 * If movie/series info is missing, TMDB will be used as a fallback. 
 * 
 * Get a free API key at: https://developers.themoviedb.org/3/getting-started/introduction
 * 
 * Example: window.tmdb = "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6";
 */
window.tmdb = "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6";

/*----- Error Logging Configuration -----*/
/**
 * Enable comprehensive client-side error logging
 * 
 * When true:  
 * - All JavaScript errors are logged to browser console
 * - Errors are sent to server (/logger. php) for persistent storage
 * - Network errors, API failures, and crashes are tracked
 * 
 * Set to true during setup/debugging
 * Set to false in production for better performance
 */
window.enableErrorLogging = true;

/**
 * Enable debug mode
 * Shows additional console messages for troubleshooting
 */
window.debugMode = false;

/*----- Error Logging System -----*/
/**
 * Client-side error logging functionality
 * Captures all JavaScript errors and sends them to the server
 */
window.logError = function(error, source, context) {
    if (! window.enableErrorLogging) return;
    
    // Create detailed error object
    const errorData = {
        timestamp:  new Date().toLocaleString('en-GB', { 
            timeZone: 'Europe/London',
            hour12: false 
        }),
        message: error.message || error. toString(),
        source: source || 'Unknown',
        context: context || 'General',
        userAgent: navigator.userAgent,
        url: window.location.href,
        dns: window.dns,
        cors: window.cors,
        https: window.https
    };
    
    // Add stack trace if available
    if (error.stack) {
        errorData.stack = error.stack;
    }
    
    // Log to browser console with styling
    console.group('%c⚠️ ERROR LOGGED', 'color: #ff0000; font-weight: bold; font-size: 14px;');
    console.log('%cTimestamp:', 'font-weight: bold;', errorData.timestamp);
    console.log('%cSource:', 'font-weight: bold;', errorData.source);
    console.log('%cContext:', 'font-weight: bold;', errorData.context);
    console.log('%cMessage:', 'font-weight: bold;', errorData.message);
    if (errorData.stack) {
        console.log('%cStack Trace:', 'font-weight:  bold;');
        console.log(errorData.stack);
    }
    console.log('%cConfiguration:', 'font-weight: bold;');
    console.log('  DNS:', errorData.dns);
    console.log('  CORS:', errorData.cors);
    console.log('  HTTPS:', errorData.https);
    console.groupEnd();
    
    // Send error to server for persistent logging
    // Only if logger.php exists (fail silently if not)
    try {
        fetch('/logger.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(errorData)
        }).catch(function(err) {
            // Logger.php might not exist yet, fail silently
            if (window.debugMode) {
                console.log('Note: Could not send error to server logger (this is OK during initial setup)');
            }
        });
    } catch (e) {
        // Fail silently
    }
};

/**
 * Global error handler
 * Catches all unhandled JavaScript errors
 */
window.addEventListener('error', function(event) {
    if (window.enableErrorLogging) {
        window.logError(
            event.error || { message: event.message },
            event.filename || 'Unknown file',
            'Global Error Handler'
        );
    }
});

/**
 * Promise rejection handler
 * Catches all unhandled promise rejections
 */
window.addEventListener('unhandledrejection', function(event) {
    if (window.enableErrorLogging) {
        window.logError(
            { message: event.reason },
            'Promise',
            'Unhandled Promise Rejection'
        );
    }
});

/**
 * Configuration validation
 * Checks if critical settings are configured
 */
window.validateConfig = function() {
    const issues = [];
    
    // Check DNS configuration
    if (window.dns === "http://domain.com:80") {
        issues.push({
            severity: 'WARNING',
            message: 'DNS is set to default value. Please configure your IPTV provider URL.',
            setting: 'window.dns'
        });
    }
    
    // Check CORS/DNS mismatch
    if (window. cors === true && window.dns === "http://domain.com:80") {
        issues.push({
            severity: 'ERROR',
            message:  'CORS is enabled but DNS is not configured. Player will not work.',
            setting: 'window. dns and window.cors'
        });
    }
    
    // Display issues in console
    if (issues.length > 0) {
        console.group('%c⚙️ CONFIGURATION ISSUES DETECTED', 'color: #ff9800; font-weight: bold; font-size: 14px;');
        issues.forEach(function(issue) {
            const style = issue.severity === 'ERROR' 
                ? 'color: #ff0000; font-weight: bold;'
                : 'color: #ff9800; font-weight:  bold;';
            console.log('%c' + issue.severity + ':', style, issue.message);
            console.log('  Setting:', issue.setting);
        });
        console.groupEnd();
        
        // Log to server
        if (window.enableErrorLogging) {
            window.logError(
                { message: issues.length + ' configuration issue(s) detected' },
                'config.js',
                'Configuration Validation'
            );
        }
    } else {
        if (window.debugMode) {
            console.log('%c✅ Configuration validated successfully', 'color: #4caf50; font-weight:  bold;');
        }
    }
    
    return issues;
};

/**
 * Startup logging
 * Logs when config.js loads successfully
 */
if (window.debugMode) {
    console.group('%c🚀 STREAMITY PLAYER INITIALIZING', 'color: #2196f3; font-weight:  bold; font-size: 16px;');
    console.log('%cPlayer Name:', 'font-weight: bold;', window.playername);
    console.log('%cDNS:', 'font-weight: bold;', window.dns);
    console.log('%cCORS Enabled:', 'font-weight:  bold;', window.cors);
    console.log('%cHTTPS Enabled:', 'font-weight: bold;', window.https);
    console.log('%cError Logging:', 'font-weight: bold;', window.enableErrorLogging);
    console.log('%cDebug Mode:', 'font-weight: bold;', window.debugMode);
    console.log('%cTimestamp:', 'font-weight:  bold;', new Date().toLocaleString('en-GB', { 
        timeZone: 'Europe/London',
        hour12: false 
    }));
    console.groupEnd();
}

// Validate configuration on load
setTimeout(function() {
    window.validateConfig();
}, 500);

/**
 * DO NOT MODIFY BELOW THIS LINE
 * The code below automatically updates the page title
 */
//! !!! !!  Don't change this !!! !!!
if (document.getElementsByTagName("title")[0]) {
    document.getElementsByTagName("title")[0].innerText = window.playername;
}
