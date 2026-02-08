# Telegram Chat Integration Fix

## Problem
The live chat was not sending messages to Telegram. Messages were only flowing from Telegram to the Live Chat, but not the other way around.

## Root Cause
The issue was in `chat_engine.php` line 162. The code was passing a PHP array directly to `CURLOPT_POSTFIELDS`:

```php
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
```

When cURL receives an array in `CURLOPT_POSTFIELDS`, it automatically encodes it as `multipart/form-data`, but the Telegram Bot API expects `application/x-www-form-urlencoded` format for this type of request.

## Solution
Changed the code to properly encode the payload using `http_build_query()`:

```php
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
```

This ensures the data is sent as URL-encoded form data, which the Telegram Bot API can properly parse.

## How It Works

### Live Chat → Telegram (Fixed)
1. User sends a message through the web chat interface
2. JavaScript sends POST request to `chat_engine.php` with email and message
3. Message is stored in the database
4. **[FIXED]** Message is sent to Telegram admin via Bot API using properly encoded POST data
5. Admin receives notification in Telegram

### Telegram → Live Chat (Already Working)
1. Admin replies in Telegram
2. Telegram sends webhook to `chat_engine.php`
3. Message is stored in the database with the target email
4. JavaScript polls and loads the message from the database
5. User sees the admin's reply in the web chat

## Testing

To test the fix, you need:

1. A Telegram bot token (from @BotFather)
2. Your Telegram chat ID
3. Configure these in `/home/sistemx/secrets.php`:
   ```php
   define('TG_TOKEN', 'your_bot_token_here');
   define('TG_ADMIN_ID', 'your_telegram_chat_id');
   ```

4. Set up the webhook (one-time setup):
   ```bash
   curl "https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook?url=https://yourdomain.com/chat_engine.php"
   ```

5. Test the flow:
   - Open your website's live chat
   - Send a message from the chat interface
   - Check if you receive the notification in Telegram
   - Reply from Telegram using format `email@example.com: Your reply message`
   - Check if the reply appears in the live chat

## Files Modified
- `chat_engine.php` - Line 162: Added `http_build_query()` to properly encode the Telegram API payload
