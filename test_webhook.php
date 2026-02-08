#!/usr/bin/env php
<?php
/**
 * Test script to simulate Telegram webhook
 * Usage: php test_webhook.php [email] [message]
 */

$email = $argv[1] ?? 'test@example.com';
$message = $argv[2] ?? 'Test message from webhook';

// Simulate webhook payload from Telegram
$webhook_payload = [
    'update_id' => 123456789,
    'message' => [
        'message_id' => 1,
        'from' => [
            'id' => 12345678,
            'is_bot' => false,
            'first_name' => 'Test',
            'username' => 'testuser'
        ],
        'chat' => [
            'id' => 12345678, // This should match TG_ADMIN_ID in secrets.php
            'first_name' => 'Test',
            'username' => 'testuser',
            'type' => 'private'
        ],
        'date' => time(),
        'text' => $message
    ]
];

echo "=== Simulating Telegram Webhook ===\n";
echo "Email: $email\n";
echo "Message: $message\n\n";
echo "Payload:\n";
echo json_encode($webhook_payload, JSON_PRETTY_PRINT) . "\n\n";

// Make POST request to chat_engine.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/chat_engine.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response HTTP Code: $http_code\n";
echo "Response Body: $result\n";

echo "\n=== Check logs with: ===\n";
echo "tail -50 /var/log/php_errors.log | grep 'chat_engine'\n";
echo "# or\n";
echo "tail -50 /var/log/apache2/error.log | grep 'chat_engine'\n";
