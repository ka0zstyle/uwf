# Telegram Bot Setup Guide for Live Chat

This guide explains how to set up the Telegram bot integration for the UltraWebForge live chat system.

## Overview

The chat system uses Telegram to:
1. **Notify admins** when users send messages
2. **Receive admin responses** via Telegram and display them in the website chat
3. **Support multiple response methods** for admin convenience

## Prerequisites

- A Telegram account
- Access to the server where the website is hosted
- Database configured with `chat_messages` table

## Step 1: Create a Telegram Bot

1. Open Telegram and search for [@BotFather](https://t.me/BotFather)
2. Send `/newbot` command
3. Follow the prompts to:
   - Choose a name for your bot (e.g., "UWF Support Bot")
   - Choose a username (must end in 'bot', e.g., "uwf_support_bot")
4. **Save the bot token** - you'll need it later
   - Example format: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`

## Step 2: Get Your Chat ID

1. Send a message to your new bot (any message will work)
2. Visit this URL in your browser (replace `<YOUR_BOT_TOKEN>` with your actual token):
   ```
   https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
   ```
3. Look for the `"chat":{"id":` field in the response
4. **Save your chat ID** - it will be a number like `123456789`

## Step 3: Configure secrets.php

1. Create or edit `/home/sistemx/secrets.php` (adjust path if needed)
2. Add your Telegram configuration:
   ```php
   <?php
   // ... other configuration ...
   
   // Telegram Bot Configuration
   define('TG_TOKEN', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');  // Your bot token
   define('TG_ADMIN_ID', '123456789');                           // Your chat ID
   ```
3. Save the file and set secure permissions:
   ```bash
   chmod 600 /home/sistemx/secrets.php
   ```

## Step 4: Set Up the Webhook

The webhook tells Telegram where to send incoming messages. You need to register your chat_engine.php URL with Telegram.

### Method 1: Using Browser (Easiest)

Visit this URL (replace placeholders with your values):
```
https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://yourdomain.com/chat_engine.php
```

**Example:**
```
https://api.telegram.org/bot123456789:ABCdefGHIjklMNOpqrsTUVwxyz/setWebhook?url=https://ultrawebforge.com/chat_engine.php
```

You should see a success response:
```json
{"ok":true,"result":true,"description":"Webhook was set"}
```

### Method 2: Using cURL

```bash
curl -X POST "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook" \
  -d "url=https://yourdomain.com/chat_engine.php"
```

### Verify Webhook Setup

Check if your webhook is configured correctly:
```
https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo
```

You should see your webhook URL and `"pending_update_count": 0` (or a small number).

## Step 5: Test the Integration

### Test 1: Sending from Website

1. Open your website chat
2. Enter your email and send a test message
3. You should receive a notification in Telegram

### Test 2: Responding from Telegram

You have **three ways** to respond:

#### Method 1: Reply to Message (Recommended) ⭐
1. In Telegram, **quote/reply** to the notification message
2. Type your response and send
3. The system will automatically extract the user's email from the quoted message

#### Method 2: Email:Message Format
Type: `user@example.com:Your response message here`
- The part before `:` is the user's email
- The part after `:` is your message

#### Method 3: Auto-assign to Last User
Simply type your message and send it. The system will send it to the most recent user who messaged.

### Expected Behavior

✅ **When working correctly:**
- Admin gets Telegram notification with user's email when user sends message
- Admin can reply using any of the 3 methods above
- User sees admin response appear in website chat within 3 seconds (polling interval)
- Server logs show "✓ Admin message saved successfully"

❌ **Common issues:**
- Webhook not set up → Admin doesn't receive notifications
- Wrong TG_ADMIN_ID → System ignores admin messages
- Database not accessible → Messages not saved
- Chat not open/polling stopped → User doesn't see response immediately

## Troubleshooting

### Check Server Logs

View error logs to see what's happening:
```bash
tail -f /var/log/php_errors.log
# or
tail -f /var/log/apache2/error.log
```

Look for lines containing `chat_engine:` for detailed debugging information.

### Common Error Messages

**"TG_ADMIN_ID not defined"**
- Solution: Add `TG_ADMIN_ID` to secrets.php

**"Could not determine target_email"**
- Solution: Use one of the 3 response methods correctly
- The bot will send you a help message explaining the formats

**"Database prepare failed"**
- Solution: Check database connection in secrets.php
- Verify `chat_messages` table exists

**"Telegram API error"**
- Solution: Check that TG_TOKEN is correct
- Verify bot is not blocked

### Webhook Issues

If webhook stops working:

1. **Check webhook status:**
   ```
   https://api.telegram.org/bot<TOKEN>/getWebhookInfo
   ```

2. **Look for errors:**
   - `"last_error_message"` field will show any issues
   - `"pending_update_count"` should be 0 or low

3. **Reset webhook:**
   ```bash
   # Delete webhook
   curl "https://api.telegram.org/bot<TOKEN>/deleteWebhook"
   
   # Set it again
   curl -X POST "https://api.telegram.org/bot<TOKEN>/setWebhook" \
     -d "url=https://yourdomain.com/chat_engine.php"
   ```

4. **Verify SSL certificate:**
   - Telegram requires HTTPS
   - Certificate must be valid (not self-signed)
   - Use Let's Encrypt if needed

### Testing Webhook Manually

Send a test webhook to verify your endpoint:
```bash
curl -X POST "https://yourdomain.com/chat_engine.php" \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "chat": {"id": YOUR_CHAT_ID},
      "text": "test@example.com:Test message",
      "message_id": 123,
      "date": 1234567890
    }
  }'
```

Check server logs for the response.

## Security Notes

1. **Never commit secrets.php** to version control
2. **Keep webhook URL secure** - it's publicly accessible but validates the sender
3. **Use HTTPS only** - Telegram requires it
4. **Set proper file permissions** on secrets.php (600)
5. **Monitor logs** for suspicious activity

## Database Schema

The `chat_messages` table should have this structure:

```sql
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sender ENUM('user', 'admin') NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_email (email),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Advanced Configuration

### Custom Response Instructions

Edit the help message in `chat_engine.php` if you want to customize the instructions sent to admins.

### Multiple Admins

To support multiple admins, you'll need to modify the code to accept an array of admin IDs:
```php
define('TG_ADMIN_IDS', [123456789, 987654321]);
// Then check: in_array($chat_id_incoming, TG_ADMIN_IDS)
```

### Automatic Responses

Add an auto-responder by checking for specific keywords in user messages and automatically inserting admin responses.

## Support

If you continue having issues after following this guide:

1. Check all log files for errors
2. Verify each configuration step
3. Test with the manual webhook curl command
4. Ensure your hosting supports HTTPS and external webhook requests
5. Contact your hosting provider if webhook requests are being blocked

## Summary Checklist

- [ ] Created Telegram bot via @BotFather
- [ ] Got bot token and saved it
- [ ] Sent message to bot and got chat ID
- [ ] Configured TG_TOKEN and TG_ADMIN_ID in secrets.php
- [ ] Set webhook URL using browser or curl
- [ ] Verified webhook with getWebhookInfo
- [ ] Tested sending message from website
- [ ] Tested responding from Telegram
- [ ] Verified response appears in website chat
- [ ] Checked server logs for errors

Once all items are checked, your Telegram integration should be fully functional! 🎉
