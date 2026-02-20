# Webhook Setup

The ExprAs Nutgram extension uses webhooks to receive updates from Telegram.

## Webhook URL

The extension provides a webhook endpoint at `/ng/wh/update`. Your webhook URL should:
- Use HTTPS (required by Telegram)
- Point to this path
- Be publicly accessible

Example URLs:
```
Production: https://your-domain.com/ng/wh/update
Development: https://your-local-domain.test/ng/wh/update
```

## Configuration

```php
'webhook' => [
    // Webhook URL (required)
    'url' => 'https://your-domain.com/ng/wh/update',

    // Maximum parallel connections (optional, default: 40)
    'max_connections' => 40,

    // Drop pending updates when setting webhook (optional)
    'drop_pending_updates' => false,

    // Secret token for webhook validation (recommended)
    'secret_token' => 'your_secret_token_here',

    // Update types to receive (optional)
    'allowed_updates' => [
        'message',
        'callback_query',
        // Add other types as needed
    ],
],
```

## Setting Up Webhook

Use the console command:
```bash
vendor/bin/mezzio-sf-console nutgram:hook:set
```

## Checking Webhook Status

```bash
vendor/bin/mezzio-sf-console nutgram:hook:info
```

## Removing Webhook

```bash
vendor/bin/mezzio-sf-console nutgram:hook:remove [--drop-pending-updates]
```

## Security

1. Always use HTTPS
2. Set a secret token in production
3. Validate webhook requests
4. Limit allowed update types
5. Set appropriate max connections

## Local Development

1. Use a domain accessible by Telegram
2. Set up HTTPS (required)
3. Configure your local URL in `nutgram.local.php`
4. Test with `nutgram:hook:info`

## Troubleshooting

1. **401 Unauthorized**
   - Check bot token
   - Verify `test_env` setting

2. **SSL Issues**
   - Ensure valid SSL certificate
   - Check HTTPS configuration

3. **Connection Issues**
   - Verify domain is accessible
   - Check firewall settings
   - Validate webhook URL format
