# Configuration

The ExprAs Nutgram extension uses Laminas configuration system with global and local configuration files.

## Configuration Files

- `config/autoload/nutgram.global.php`: Production configuration
- `config/autoload/nutgram.local.php`: Development configuration (overrides global)
- `Bot/config/nutgram.php`: Module configuration both for dev and propd

## Configuration Structure

```php
return [
    'nutgram' => [
        // Bot Configuration
        'token' => '',                 // Bot token from @BotFather
        'safe_mode' => true,          // Enable safe mode for error handling
        'mixins' => true,             // Enable additional functionality

        // Bot Settings
        'config' => [
            'api_url' => 'https://api.telegram.org',  // Telegram API URL
            'test_env' => false,      // Use test environment
            'is_local' => false,      // Local development mode
            'timeout' => 30,          // API request timeout
            'enable_http2' => true,   // Enable HTTP/2 support
            'conversation_ttl' => 3600, // Conversation timeout
        ],

        // Webhook Configuration
        'webhook' => [
            'url' => '',              // Webhook URL
            'max_connections' => 40,   // Maximum parallel connections
            'drop_pending_updates' => false, // Drop pending updates on set
            'secret_token' => '',     // Secret token for webhook
            'allowed_updates' => [    // Update types to receive
                'message',
                'edited_message',
                'channel_post',
                'edited_channel_post',
                'inline_query',
                'chosen_inline_result',
                'callback_query',
                'shipping_query',
                'pre_checkout_query',
                'poll',
                'poll_answer',
                'my_chat_member',
                'chat_member',
                'chat_join_request'
            ],
        ],

        // Command Handlers
        'services' => [
            'start' => \Bot\Handler\StartHandler::class,
            'help' => \Bot\Handler\HelpHandler::class,
        ],
    ],
];
```

## Environment Variables

Production configuration should use environment variables:

```php
// config/autoload/nutgram.global.php
'token' => getenv('TELEGRAM_BOT_TOKEN'),
'webhook' => [
    'url' => getenv('TELEGRAM_WEBHOOK_URL'),
    'secret_token' => getenv('NUTGRAM_SECRET_TOKEN'),
],
```

Required environment variables:
```bash
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_WEBHOOK_URL=https://your-domain.com/ng/wh/update
NUTGRAM_SECRET_TOKEN=your_secret_token_here
```

## Development Configuration

Local configuration can use hardcoded values for development:

```php
// config/autoload/nutgram.local.php
return [
    'nutgram' => [
        'token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz',
        'config' => [
            'is_local' => true,
            'test_env' => false,
        ],
        'webhook' => [
            'url' => 'https://your-local-domain.test/ng/wh/update',
        ],
        'safe_mode' => false,
    ],
];
```

## Configuration Options

### Bot Configuration

- `token`: Your bot token from @BotFather
- `safe_mode`: Enable safe mode for error handling
- `mixins`: Enable additional functionality

### Bot Settings

- `api_url`: Telegram API URL (default: https://api.telegram.org)
- `test_env`: Use test environment (default: false)
- `is_local`: Local development mode (default: false)
- `timeout`: API request timeout in seconds (default: 30)
- `enable_http2`: Enable HTTP/2 support (default: true)
- `conversation_ttl`: Conversation timeout in seconds (default: 3600)

### Webhook Configuration

- `url`: Your webhook URL
- `max_connections`: Maximum parallel connections (default: 40)
- `drop_pending_updates`: Drop pending updates when setting webhook
- `secret_token`: Secret token for webhook validation
- `allowed_updates`: Array of update types to receive

### Middleware Configuration

The package includes several core middlewares that are automatically registered. **Priority order is critical** as some middlewares depend on others:

```php
'middlewares' => [
    [
        'middleware' => UserEntityInjectorMiddleware::class,
        'priority'   => PHP_INT_MAX, // Highest priority - executes FIRST
    ],
    [
        'middleware' => ChatEntityInjectorMiddleware::class,
        'priority'   => PHP_INT_MAX - 1, // Second priority
    ],
    [
        'middleware' => UserMessageListenerMiddleware::class,
        'priority'   => PHP_INT_MAX - 2, // Third priority - depends on user entity
    ],
]
```

**⚠️ Important: Do not change this priority order!**

- **`UserEntityInjectorMiddleware`** must execute first as it creates and injects the user entity
- **`ChatEntityInjectorMiddleware`** executes second to inject chat entities  
- **`UserMessageListenerMiddleware`** executes last as it depends on the injected user entity

#### Middleware Dependencies

```php
UserEntityInjectorMiddleware → User entity available in bot context
    ↓
ChatEntityInjectorMiddleware → Chat entity available in bot context  
    ↓
UserMessageListenerMiddleware → Can access both user and chat entities
```

**Why this order matters:**
- `UserMessageListenerMiddleware` calls `$bot->get(User::class)` which requires the user entity to be injected first
- Changing the order will cause runtime errors when middlewares try to access entities that haven't been injected yet

### Command Handlers

Register your command handlers in the `services` array:

```php
'services' => [
    'command_name' => \Your\Handler\Class::class,
],
```

## Best Practices

1. Use environment variables in production
2. Keep sensitive data out of version control
3. Enable safe mode in production
4. Use HTTPS for webhook URLs
5. Set appropriate timeouts
6. Limit allowed update types to those you need
7. **Never change middleware priority order** - it's critical for proper functionality
8. **Test middleware dependencies** when adding custom middlewares
9. **Use explicit priorities** when adding new middlewares to avoid conflicts

## Validation

Test your configuration:

```bash
# Check webhook status
vendor/bin/mezzio-sf-console nutgram:hook:info

# Set webhook with current configuration
vendor/bin/mezzio-sf-console nutgram:hook:set
```
