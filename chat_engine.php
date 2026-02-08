<?php
/**
 * SECURE CHAT ENGINE - ULTRAWEBFORGE (groups consecutive messages from same sender)
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$ruta_secretos = '/home/sistemx/secrets.php';
if (!file_exists($ruta_secretos)) {
    error_log("ALERT: secrets.php not found at: " . $ruta_secretos);
    http_response_code(500);
    exit("Service configuration error.");
}
require_once $ruta_secretos;

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    error_log("Database Connection Error: " . $db->connect_error);
    http_response_code(503);
    exit("Service temporarily unavailable.");
}
$db->set_charset("utf8mb4");

$current_lang = 'en';
if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['en','es'], true)) { $current_lang = $_SESSION['lang']; }

if (isset($_GET['action']) && $_GET['action'] === 'load') {
    header('Content-Type: text/html; charset=utf-8');
    $email = trim((string)($_GET['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { exit; }

    $stmt = $db->prepare("SELECT email, message, sender, created_at FROM chat_messages WHERE email = ? ORDER BY created_at ASC");
    if (!$stmt) { error_log("chat_engine: prepare failed: " . $db->error); exit; }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    $systemTexts = ['Soporte en línea','Support online','Soporte en línea','Support Online'];

    // Cargar todas las filas para poder agrupar consecutivos
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $text = trim((string)$row['message']);
        if ($text === '' || in_array($text, $systemTexts, true)) continue;
        $rows[] = $row;
    }
    $stmt->close();

    // Agrupar por bloques consecutivos del mismo sender
    $groups = [];
    $currentSender = null;
    $currentGroup = [];

    foreach ($rows as $row) {
        $sender = ($row['sender'] === 'user') ? 'user' : 'admin';
        if ($currentSender === null) {
            $currentSender = $sender;
            $currentGroup[] = $row;
            continue;
        }
        if ($sender === $currentSender) {
            $currentGroup[] = $row;
        } else {
            // cerrar grupo anterior
            $groups[] = ['sender' => $currentSender, 'items' => $currentGroup];
            // iniciar nuevo
            $currentSender = $sender;
            $currentGroup = [$row];
        }
    }
    if (!empty($currentGroup)) {
        $groups[] = ['sender' => $currentSender, 'items' => $currentGroup];
    }

    // Render grupos en una sola burbuja por remitente consecutivo
    foreach ($groups as $g) {
        $sender = $g['sender'];
        if ($sender === 'admin') {
            $chipLabel = ($current_lang === 'es') ? 'Soporte' : 'Support';
            $avatarPathWeb = '/assets/img/support-avatar.webp';
            $avatarPathFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $avatarPathWeb;
            $avatarHtml = '';
            if (file_exists($avatarPathFs)) {
                $avatarHtml = "<div class='msg-avatar' aria-hidden='true' style=\"background-image: url('{$avatarPathWeb}');\"></div>";
            }
            echo "<div class='msg-bubble msg-admin'>";
            echo $avatarHtml;
            echo "<div class='msg-content'>";
            echo "<div class='sender-chip' aria-hidden='true'><span class='sender-name'>" . htmlspecialchars($chipLabel, ENT_QUOTES, 'UTF-8') . "</span></div>";
            // Múltiples mensajes del mismo admin dentro de la misma burbuja
            foreach ($g['items'] as $it) {
                $safeText = htmlspecialchars(trim((string)$it['message']), ENT_QUOTES, 'UTF-8');
                if ($safeText !== '') {
                    echo "<div class='msg-text'>{$safeText}</div>";
                }
            }
            echo "</div></div>";
        } else {
            // Usuario: una burbuja con múltiples msg-text
            echo "<div class='msg-bubble msg-user'>";
            foreach ($g['items'] as $it) {
                $safeText = htmlspecialchars(trim((string)$it['message']), ENT_QUOTES, 'UTF-8');
                if ($safeText !== '') {
                    echo "<div class='msg-text'>{$safeText}</div>";
                }
            }
            echo "</div>";
        }
    }
    exit;
}

// Webhook Telegram/Instagram (improved with better logging and validation)
$rawInput = file_get_contents("php://input");
$update = json_decode($rawInput, true);

// Log webhook data for debugging
if ($rawInput) {
    error_log("chat_engine webhook received: " . substr($rawInput, 0, 500));
}

if (is_array($update) && isset($update['message']) && isset($update['message']['chat']['id'])) {
    $chat_id_incoming = $update['message']['chat']['id'];
    $reply_text = trim((string)($update['message']['text'] ?? ''));
    
    error_log("chat_engine: Processing message from chat_id: {$chat_id_incoming}, text: " . substr($reply_text, 0, 100));
    
    if (!empty($reply_text) && defined('TG_ADMIN_ID') && $chat_id_incoming == TG_ADMIN_ID) {
        $target_email = ''; $admin_message = '';
        
        // Try to parse email:message format first
        if (strpos($reply_text, ':') !== false) {
            $parts = explode(':', $reply_text, 2);
            $maybeEmail = trim($parts[0]); $maybeMsg = trim($parts[1]);
            if (filter_var($maybeEmail, FILTER_VALIDATE_EMAIL) && $maybeMsg !== '') {
                $target_email = $maybeEmail; $admin_message = $maybeMsg;
            }
        }
        
        // If no email:message format, use the last user who sent a message
        if ($target_email === '') {
            $q = $db->query("SELECT email FROM chat_messages WHERE sender='user' ORDER BY created_at DESC LIMIT 1");
            if ($q && ($row = $q->fetch_assoc())) { 
                $target_email = $row['email']; 
                $admin_message = $reply_text;
                error_log("chat_engine: Auto-assigned to last user: {$target_email}");
            }
            if ($q) $q->free();
        }
        
        if ($target_email !== '' && $admin_message !== '') {
            $stmtIns = $db->prepare("INSERT INTO chat_messages (email, message, sender, created_at) VALUES (?, ?, 'admin', NOW())");
            if ($stmtIns) { 
                $stmtIns->bind_param("ss", $target_email, $admin_message); 
                if ($stmtIns->execute()) {
                    error_log("chat_engine: Admin message saved successfully for {$target_email}");
                } else {
                    error_log("chat_engine webhook execute failed: " . $stmtIns->error);
                }
                $stmtIns->close(); 
            }
            else { error_log("chat_engine webhook insert prepare failed: " . $db->error); }
        } else {
            error_log("chat_engine: Could not determine target_email or message is empty");
        }
    }
    http_response_code(200);
    exit;
}

// POST Usuario -> Admin (sin cambios relevantes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $email = trim((string)($_POST['email'] ?? ''));
    $msg = trim((string)($_POST['message'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $msg === '') { http_response_code(400); exit; }

    $stmt = $db->prepare("INSERT INTO chat_messages (email, message, sender, created_at) VALUES (?, ?, 'user', NOW())");
    if ($stmt) { $stmt->bind_param("ss", $email, $msg); $stmt->execute(); $stmt->close(); }
    else { error_log("chat_engine POST insert prepare failed: " . $db->error); http_response_code(500); exit; }

    if (defined('TG_TOKEN') && defined('TG_ADMIN_ID')) {
        $text_formatted = "👤 *Nuevo mensaje de:* `{$email}`\n\n" . $msg;
        $url = "https://api.telegram.org/bot" . TG_TOKEN . "/sendMessage";
        $payload = ['chat_id' => TG_ADMIN_ID, 'text' => $text_formatted, 'parse_mode' => 'Markdown'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $result = curl_exec($ch);
        if ($result === false) { error_log("chat_engine: curl error sending to Telegram: " . curl_error($ch)); }
        curl_close($ch);
    }

    http_response_code(200);
    exit;
}

http_response_code(204);
exit;