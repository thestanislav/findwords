# Console Commands

The ExprAs Nutgram extension provides several console commands to manage your bot.

## Available Commands

### Webhook Information

```bash
vendor/bin/mezzio-sf-console nutgram:hook:info
```

Shows current webhook status including:
- Webhook URL
- Custom certificate status
- Pending update count
- IP address
- Last error information
- Maximum connections
- Allowed update types

### Set Webhook

```bash
vendor/bin/mezzio-sf-console nutgram:hook:set
```

Sets the webhook using configuration from `nutgram.webhook.url`. Requires:
- Valid bot token
- HTTPS URL
- Publicly accessible domain

### Remove Webhook

```bash
vendor/bin/mezzio-sf-console nutgram:hook:remove [--drop-pending-updates]
```

Removes the webhook. Options:
- `--drop-pending-updates`: Drop all pending updates

### Logout

```bash
vendor/bin/mezzio-sf-console nutgram:logout [--drop-pending-updates]
```

Logs out from the cloud Bot API server. Options:
- `--drop-pending-updates`: Drop all pending updates

### Register Commands

```bash
vendor/bin/mezzio-sf-console nutgram:register-commands
```

Registers bot commands configured in `services` with Telegram.

### Delete Sent Messages

```bash
vendor/bin/mezzio-sf-console nutgram:delete-sent-messages
```

Removes sent messages from users' Telegram chats. This command:
- Processes messages marked for deletion
- Deletes messages from Telegram using the Bot API
- Updates message status in the database
- Handles errors gracefully with logging
- Processes messages in batches of 100 for performance

**Note**: This command requires proper error logging configuration (`expras_error_logger` service) for optimal error handling.

## Usage Examples

1. Check webhook status:
```bash
vendor/bin/mezzio-sf-console nutgram:hook:info
```

2. Set up webhook:
```bash
vendor/bin/mezzio-sf-console nutgram:hook:set
```

3. Remove webhook and drop updates:
```bash
vendor/bin/mezzio-sf-console nutgram:hook:remove --drop-pending-updates
```

4. Logout and drop updates:
```bash
vendor/bin/mezzio-sf-console nutgram:logout --drop-pending-updates
```

5. Register commands:
```bash
vendor/bin/mezzio-sf-console nutgram:register-commands
```

6. Delete sent messages:
```bash
vendor/bin/mezzio-sf-console nutgram:delete-sent-messages
```

## Common Issues

1. **401 Unauthorized**
   - Check bot token
   - Verify `test_env` setting
   - Ensure token has required permissions

2. **SSL Certificate Issues**
   - Verify HTTPS is properly configured
   - Check certificate validity
   - Ensure domain is accessible

3. **Command Registration Fails**
   - Verify command handlers extend `Command` class
   - Check handler registration in configuration

4. **Delete Message Errors**
   - Ensure proper chat_id and message_id parameters
   - Verify message exists and is accessible
   - Check bot permissions for message deletion
   - Validate command names follow Telegram rules

## Best Practices

1. Use `nutgram:hook:info` to verify setup
2. Register commands after adding new handlers
3. Use `--drop-pending-updates` when needed
4. Keep webhook URL secure and accessible
5. Monitor command execution results

## Environment-Specific Usage

### Development

```bash
# Set local webhook
TELEGRAM_BOT_TOKEN=your_test_token \
TELEGRAM_WEBHOOK_URL=https://your-local-domain.test/ng/wh/update \
vendor/bin/mezzio-sf-console nutgram:hook:set
```

### Production

```bash
# Set production webhook
TELEGRAM_BOT_TOKEN=your_production_token \
TELEGRAM_WEBHOOK_URL=https://your-domain.com/ng/wh/update \
vendor/bin/mezzio-sf-console nutgram:hook:set
```
