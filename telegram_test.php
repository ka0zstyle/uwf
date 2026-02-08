#!/usr/bin/env php
<?php
/**
 * Telegram Webhook Tester for UltraWebForge Chat
 * 
 * This script helps you test and debug your Telegram webhook setup.
 * 
 * Usage:
 *   php telegram_test.php check           # Check configuration
 *   php telegram_test.php webhook         # Get webhook info
 *   php telegram_test.php set <url>       # Set webhook URL
 *   php telegram_test.php delete          # Delete webhook
 *   php telegram_test.php send <email> <message>  # Test sending to Telegram
 */

// Color output for terminal
function color($text, $color = 'white') {
    $colors = [
        'red' => "\033[0;31m",
        'green' => "\033[0;32m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'cyan' => "\033[0;36m",
        'white' => "\033[0;37m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

function success($msg) { echo color("✓ ", 'green') . $msg . "\n"; }
function error($msg) { echo color("✗ ", 'red') . $msg . "\n"; }
function info($msg) { echo color("ℹ ", 'cyan') . $msg . "\n"; }
function warn($msg) { echo color("⚠ ", 'yellow') . $msg . "\n"; }

// Load configuration
$secrets_path = '/home/sistemx/secrets.php';
if (!file_exists($secrets_path)) {
    error("secrets.php not found at: {$secrets_path}");
    error("Please create it from secrets.php.example");
    exit(1);
}

require_once $secrets_path;

// Check if required constants are defined
function check_config() {
    echo "\n" . color("=== Configuration Check ===", 'cyan') . "\n\n";
    
    $errors = [];
    
    if (!defined('TG_TOKEN')) {
        error("TG_TOKEN not defined in secrets.php");
        $errors[] = 'TG_TOKEN';
    } else {
        $token = TG_TOKEN;
        if (strpos($token, ':') === false || strlen($token) < 20) {
            error("TG_TOKEN looks invalid (should be like: 123456789:ABCdef...)");
            $errors[] = 'TG_TOKEN format';
        } else {
            success("TG_TOKEN is defined");
            info("  Token: " . substr($token, 0, 20) . "...");
        }
    }
    
    if (!defined('TG_ADMIN_ID')) {
        error("TG_ADMIN_ID not defined in secrets.php");
        $errors[] = 'TG_ADMIN_ID';
    } else {
        success("TG_ADMIN_ID is defined");
        info("  Admin ID: " . TG_ADMIN_ID);
    }
    
    if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        error("Database configuration incomplete");
        $errors[] = 'Database';
    } else {
        success("Database configuration found");
        
        // Test database connection
        $db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            error("Database connection failed: " . $db->connect_error);
            $errors[] = 'Database connection';
        } else {
            success("Database connection successful");
            
            // Check if table exists
            $result = $db->query("SHOW TABLES LIKE 'chat_messages'");
            if ($result && $result->num_rows > 0) {
                success("Table 'chat_messages' exists");
            } else {
                error("Table 'chat_messages' not found");
                $errors[] = 'chat_messages table';
            }
            $db->close();
        }
    }
    
    echo "\n";
    if (empty($errors)) {
        success("All configuration checks passed!");
        return true;
    } else {
        error("Configuration has " . count($errors) . " issue(s)");
        return false;
    }
}

// Make API call to Telegram
function telegram_api($method, $params = []) {
    if (!defined('TG_TOKEN')) {
        error("TG_TOKEN not configured");
        return false;
    }
    
    $url = "https://api.telegram.org/bot" . TG_TOKEN . "/" . $method;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    if (!empty($params)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($result === false) {
        error("cURL error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    $response = json_decode($result, true);
    
    if ($http_code !== 200 || !$response || !isset($response['ok'])) {
        error("API request failed (HTTP {$http_code})");
        return false;
    }
    
    return $response;
}

// Get webhook info
function get_webhook_info() {
    echo "\n" . color("=== Webhook Information ===", 'cyan') . "\n\n";
    
    $response = telegram_api('getWebhookInfo');
    
    if (!$response || !$response['ok']) {
        error("Failed to get webhook info");
        return;
    }
    
    $info = $response['result'];
    
    if (empty($info['url'])) {
        warn("No webhook is currently set");
        info("Use: php telegram_test.php set <your-webhook-url>");
    } else {
        success("Webhook is configured");
        info("  URL: " . $info['url']);
        info("  Pending updates: " . ($info['pending_update_count'] ?? 0));
        
        if (isset($info['last_error_date'])) {
            $last_error = date('Y-m-d H:i:s', $info['last_error_date']);
            error("  Last error: " . $last_error);
            if (isset($info['last_error_message'])) {
                error("  Error message: " . $info['last_error_message']);
            }
        } else {
            success("  No errors reported");
        }
        
        if (isset($info['max_connections'])) {
            info("  Max connections: " . $info['max_connections']);
        }
    }
    echo "\n";
}

// Set webhook
function set_webhook($url) {
    echo "\n" . color("=== Setting Webhook ===", 'cyan') . "\n\n";
    
    if (empty($url)) {
        error("Please provide a webhook URL");
        info("Example: php telegram_test.php set https://yourdomain.com/chat_engine.php");
        return;
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL) || strpos($url, 'https://') !== 0) {
        error("Invalid URL. Must be HTTPS");
        return;
    }
    
    info("Setting webhook to: {$url}");
    
    $response = telegram_api('setWebhook', ['url' => $url]);
    
    if (!$response || !$response['ok']) {
        error("Failed to set webhook");
        if (isset($response['description'])) {
            error("  " . $response['description']);
        }
        return;
    }
    
    success("Webhook set successfully!");
    info("  " . ($response['description'] ?? ''));
    
    // Verify it was set
    sleep(1);
    get_webhook_info();
}

// Delete webhook
function delete_webhook() {
    echo "\n" . color("=== Deleting Webhook ===", 'cyan') . "\n\n";
    
    $response = telegram_api('deleteWebhook');
    
    if (!$response || !$response['ok']) {
        error("Failed to delete webhook");
        return;
    }
    
    success("Webhook deleted successfully!");
}

// Send test message to Telegram
function send_test_message($email, $message) {
    echo "\n" . color("=== Sending Test Message ===", 'cyan') . "\n\n";
    
    if (empty($email) || empty($message)) {
        error("Please provide email and message");
        info("Example: php telegram_test.php send test@example.com \"Hello from test\"");
        return;
    }
    
    if (!defined('TG_ADMIN_ID')) {
        error("TG_ADMIN_ID not configured");
        return;
    }
    
    $text = "🧪 *Test Message*\n\n👤 From: `{$email}`\n\n{$message}";
    
    $response = telegram_api('sendMessage', [
        'chat_id' => TG_ADMIN_ID,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ]);
    
    if (!$response || !$response['ok']) {
        error("Failed to send message");
        if (isset($response['description'])) {
            error("  " . $response['description']);
        }
        return;
    }
    
    success("Test message sent to Telegram!");
    info("  Message ID: " . $response['result']['message_id']);
}

// Main command handler
$command = $argv[1] ?? 'help';

switch ($command) {
    case 'check':
        check_config();
        break;
        
    case 'webhook':
        get_webhook_info();
        break;
        
    case 'set':
        $url = $argv[2] ?? '';
        set_webhook($url);
        break;
        
    case 'delete':
        delete_webhook();
        break;
        
    case 'send':
        $email = $argv[2] ?? '';
        $message = $argv[3] ?? '';
        send_test_message($email, $message);
        break;
        
    case 'help':
    default:
        echo "\n" . color("Telegram Webhook Tester", 'cyan') . "\n";
        echo color("========================", 'cyan') . "\n\n";
        echo "Usage:\n";
        echo "  php telegram_test.php check              Check configuration\n";
        echo "  php telegram_test.php webhook            Get webhook info\n";
        echo "  php telegram_test.php set <url>          Set webhook URL\n";
        echo "  php telegram_test.php delete             Delete webhook\n";
        echo "  php telegram_test.php send <email> <msg> Send test to Telegram\n";
        echo "\n";
        echo "Examples:\n";
        echo "  php telegram_test.php check\n";
        echo "  php telegram_test.php set https://example.com/chat_engine.php\n";
        echo "  php telegram_test.php send test@example.com \"Test message\"\n";
        echo "\n";
        break;
}
