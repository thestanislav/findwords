# ExprAs Nutgram Extension

Nutgram integration for ExprAs framework, providing webhook support and command management.

## Installation

1. Add the repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../expras/nutgram"
        }
    ]
}
```

2. Install the package:

```bash
composer require expras/nutgram
```

## Features

- **Webhook Support**: Handle Telegram webhook updates
- **Command Management**: Register and manage bot commands  
- **Middleware Support**: Custom middleware for request processing
- **Entity Management**: User and chat entity management
- **Waiting State System**: Multi-step conversation support with nullable field handling
- **Subscriber Interface**: Event-based handler registration

## Configuration

Create configuration files in your project's `config/autoload` directory:

### Global Configuration (`nutgram.global.php`)

```php
return [
    'nutgram' => [
        // Bot token from @BotFather (required)
        'token' => getenv('TELEGRAM_BOT_TOKEN'),

        // Bot configuration
        'config' => [
            'api_url' => 'https://api.telegram.org',
            'test_env' => false,
            'is_local' => false,
            'timeout' => 30,
            'enable_http2' => true,
            'conversation_ttl' => 3600,
        ],

        // Webhook configuration
        'webhook' => [
            'url' => getenv('TELEGRAM_WEBHOOK_URL'),
            'max_connections' => 40,
            'drop_pending_updates' => true,
            'secret_token' => getenv('NUTGRAM_SECRET_TOKEN'),
            'allowed_updates' => [
                'message',
                'callback_query',
                // Add other update types as needed
            ],
        ],

        // Safe mode (recommended for production)
        'safe_mode' => true,

        // Enable mixins for additional functionality
        'mixins' => true,

        // Handlers configuration - all handlers including commands and update listeners
        'handlers' => [
            \Bot\Handler\StartHandler::class,
            \Bot\Handler\HelpHandler::class,
            \Bot\Handler\MessageHandler::class,
        ],
    ],
];
```

### Local Configuration (`nutgram.local.php`)

```php
return [
    'nutgram' => [
        // Bot token for local development
        'token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz',

        // Local configuration overrides
        'config' => [
            'is_local' => true,
            'test_env' => false,
        ],

        // Local webhook URL
        'webhook' => [
            'url' => 'https://your-local-domain.test/ng/wh/update',
            'drop_pending_updates' => false,
        ],

        // Disable safe mode for development
        'safe_mode' => false,
    ],
];
```

## Command Line Tools

The extension provides several console commands:

```bash
# Show webhook information
vendor/bin/mezzio-sf-console nutgram:hook:info

# Set webhook using configuration
vendor/bin/mezzio-sf-console nutgram:hook:set

# Remove webhook
vendor/bin/mezzio-sf-console nutgram:hook:remove [--drop-pending-updates]

# Log out from Telegram API
vendor/bin/mezzio-sf-console nutgram:logout [--drop-pending-updates]

# Register bot commands
vendor/bin/mezzio-sf-console nutgram:register-commands
```

## Creating Handlers

### Command Handlers

1. Create a command handler class:

```php
namespace Bot\Handler;

use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class StartHandler extends Command
{
    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage('Welcome! 👋');
    }
}
```

### Event Handlers with SubscriberInterface

For handlers that need to subscribe to multiple events, implement the `SubscriberInterface`:

```php
namespace Bot\Handler;

use ExprAs\Nutgram\SubscriberInterface;
use SergiX44\Nutgram\Nutgram;

class MessageHandler implements SubscriberInterface
{
    public function subscribeToEvents(Nutgram $bot): void
    {
        // Use arrow functions to call class methods (recommended pattern)
        $bot->onText('hello', fn(Nutgram $bot) => $this->onHelloText($bot));
        $bot->onPhoto(fn(Nutgram $bot) => $this->onPhotoMessage($bot));
        $bot->onCallbackQuery(fn(Nutgram $bot) => $this->onCallbackQuery($bot));
    }

    // Individual handler methods
    private function onHelloText(Nutgram $bot): void
    {
        $bot->sendMessage('Hello there!');
    }

    private function onPhotoMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Nice photo!');
    }

    private function onCallbackQuery(Nutgram $bot): void
    {
        $bot->answerCallbackQuery('Button clicked!');
    }
}
```

### Combined Command and Event Handler

You can also combine both approaches:

```php
namespace Bot\Handler;

use ExprAs\Nutgram\SubscriberInterface;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class AdvancedHandler extends Command implements SubscriberInterface
{
    protected string $command = 'advanced';
    protected ?string $description = 'Advanced command with additional event handling';

    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage('Advanced command executed!');
    }

    public function subscribeToEvents(Nutgram $bot): void
    {
        // Additional event subscriptions
        $bot->onText('help', fn(Nutgram $bot) => $this->onHelpText($bot));
    }

    private function onHelpText(Nutgram $bot): void
    {
        $bot->sendMessage('Help text received!');
    }
}
```

2. Register the handler in your configuration:

```php
'handlers' => [
    \Bot\Handler\StartHandler::class,
],
```

## Webhook Setup

The extension automatically sets up a webhook endpoint at `/ng/wh/update`. Make sure your webhook URL:
- Uses HTTPS (required by Telegram)
- Points to this path
- Is publicly accessible

Example webhook URLs:
- Production: `https://your-domain.com/ng/wh/update`
- Local Development: `https://your-local-domain.test/ng/wh/update`

## Environment Variables

Required environment variables:

```bash
# Production
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_WEBHOOK_URL=https://your-domain.com/ng/wh/update
NUTGRAM_SECRET_TOKEN=your_secret_token_here

# Development (optional, can use nutgram.local.php instead)
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_WEBHOOK_URL=https://your-local-domain.test/ng/wh/update
```

## Security

1. Always use HTTPS for webhook URLs
2. Set a secret token in production
3. Enable safe mode in production
4. Use environment variables for sensitive data
5. Validate webhook requests using the secret token

## Development

1. Get a bot token from [@BotFather](https://t.me/BotFather)
2. Configure your local environment
3. Set up HTTPS for local development (required by Telegram)
4. Use the console commands to manage your bot

## Quick Reference

| Task | Guide |
|------|-------|
| **Create Handlers** | [Handlers Guide](docs/handlers-guide.md) |
| **Inline Buttons** | [Handlers Guide](docs/handlers-guide.md) |
| **Multi-step Forms** | [Waiting State](docs/waiting-state.md) |
| **Event Handling** | [Subscriber Interface](docs/subscriber-interface.md) |
| **Configuration** | [Configuration Guide](docs/configuration.md) |

## Troubleshooting

1. **401 Unauthorized**
   - Check your bot token
   - Ensure `test_env` is `false` unless using a test bot

2. **Webhook Issues**
   - Verify HTTPS is properly configured
   - Check the webhook URL is publicly accessible
   - Ensure the secret token matches

3. **Command Registration**
   - Verify command handlers extend `Command` class
   - Check the handler is properly registered in configuration
   - Run `nutgram:register-commands` after adding new commands

4. **Entity Field Errors**
   - If you get "Cannot assign null to property" errors for `waitingContext`, ensure you're using the latest version
   - The `waitingContext` field is now properly nullable and handles null values automatically

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

MIT License - see [LICENSE](LICENSE) for details.