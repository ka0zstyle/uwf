<?php
/**
 * send_mail.php - Contact form mail handler
 * Integrates PHPMailer with external secrets configuration
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer library
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Load credentials from external secrets.php
// Expected location: /home/sistemx/secrets.php
$secrets_path = '/home/sistemx/secrets.php';
if (file_exists($secrets_path)) {
    require_once $secrets_path;
} else {
    error_log("send_mail.php: secrets.php not found at {$secrets_path}");
    header("Location: index.php?status=error#contact");
    exit;
}

// Verify required constants are defined
$required_constants = ['SMTP_HOST', 'SMTP_USER', 'SMTP_PASS', 'SMTP_PORT'];
foreach ($required_constants as $const) {
    if (!defined($const)) {
        error_log("send_mail.php: Required constant {$const} not defined in secrets.php");
        header("Location: index.php?status=error#contact");
        exit;
    }
}

// Set SMTP_TO if not defined (use SMTP_FROM as fallback)
if (!defined('SMTP_TO')) {
    if (defined('SMTP_FROM')) {
        define('SMTP_TO', SMTP_FROM);
    } else {
        error_log("send_mail.php: Neither SMTP_TO nor SMTP_FROM defined");
        header("Location: index.php?status=error#contact");
        exit;
    }
}

// Set SMTP_SECURE if not defined (default to 'tls' for port 587)
if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', SMTP_PORT == 465 ? 'ssl' : 'tls');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get current language for redirects
    $lang_param = '';
    if (isset($_POST['lang'])) {
        $lang_param = '?lang=' . htmlspecialchars($_POST['lang']);
    }
    
    // Capture form data
    $name     = strip_tags(trim($_POST["name"] ?? ''));
    $email    = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $whatsapp = strip_tags(trim($_POST["whatsapp"] ?? ''));
    $country  = strip_tags(trim($_POST["country"] ?? ''));
    $message  = htmlspecialchars(trim($_POST["message"] ?? ''));

    // Basic validation
    if (empty($name) || empty($message) || empty($country) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php{$lang_param}#contact&status=error");
        exit;
    }

    // Validate WhatsApp number (should have at least 10 digits including country code)
    $whatsapp_digits = preg_replace('/[^0-9]/', '', $whatsapp);
    if (!empty($whatsapp) && strlen($whatsapp_digits) < 10) {
        error_log("send_mail.php: Invalid WhatsApp number format");
        header("Location: index.php?status=error#contact");
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration from external secrets
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE; // 'tls' or 'ssl'
        $mail->Port       = SMTP_PORT;

        // Sender and recipient configuration
        $fromEmail = defined('SMTP_FROM') ? SMTP_FROM : SMTP_USER;
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'UltraWebForge Contact System';
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress(SMTP_TO);
        $mail->addReplyTo($email, $name); // Add reply-to with customer email

        // HTML message format
        $mail->isHTML(true);
        $mail->Subject = "NEW CONTACT: $name";
        
        // WhatsApp link (only if provided)
        $whatsapp_html = '';
        if (!empty($whatsapp)) {
            $wa_link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp);
            $whatsapp_html = "<p><strong>WhatsApp:</strong> <a href='$wa_link' style='color: #2b9fe6;'>$whatsapp (Click to chat)</a></p>";
        }

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #333; max-width: 600px; border: 1px solid #eee; padding: 20px;'>
                <h2 style='color: #2b9fe6; border-bottom: 2px solid #2b9fe6; padding-bottom: 10px;'>[ NEW CONTACT RECEIVED ]</h2>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Country:</strong> $country</p>
                $whatsapp_html
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                <p><strong>Message:</strong></p>
                <div style='background: #f4f4f4; padding: 15px; border-radius: 5px; font-style: italic;'>
                    $message
                </div>
                <p style='font-size: 12px; color: #999; margin-top: 25px;'>UltraWebForge — Contact Form System.</p>
            </div>
        ";

        // Send and redirect to success page
        $mail->send();
        
        // Redirect to thanks page with language parameter
        $lang_param = isset($_POST['lang']) ? '?lang=' . htmlspecialchars($_POST['lang']) : '';
        header("Location: thanks.php{$lang_param}");
        exit;

    } catch (Exception $e) {
        // On failure, return to index with error and language
        error_log("send_mail.php: Mail sending failed - " . $mail->ErrorInfo);
        $lang_param = isset($_POST['lang']) ? '?lang=' . htmlspecialchars($_POST['lang']) : '';
        header("Location: index.php{$lang_param}#contact&status=error");
        exit;
    }
} else {
    // If accessed directly, redirect to home
    header("Location: index.php");
    exit;
}
