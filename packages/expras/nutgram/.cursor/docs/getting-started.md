# Getting Started

## Prerequisites

1. PHP 8.1 or higher
2. Composer
3. ExprAs framework
4. A Telegram bot token from [@BotFather](https://t.me/BotFather)
5. HTTPS-enabled domain (required for webhook)

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

3. Copy configuration templates:

```bash
cp vendor/expras/nutgram/config/nutgram.global.php.dist config/autoload/nutgram.global.php
cp vendor/expras/nutgram/config/nutgram.local.php.dist config/autoload/nutgram.local.php
```

## Basic Setup

1. Get a bot token from [@BotFather](https://t.me/BotFather):
   - Open Telegram and message [@BotFather](https://t.me/BotFather)
   - Use `/newbot` to create a new bot
   - Follow the instructions to get your bot token

2. Configure your bot token:

```php
// config/autoload/nutgram.local.php (development)
'token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz',

// config/autoload/nutgram.global.php (production)
'token' => getenv('TELEGRAM_BOT_TOKEN'),
```

3. Set up webhook URL:

```php
// config/autoload/nutgram.local.php (development)
'webhook' => [
    'url' => 'https://your-domain.test/ng/wh/update',
],

// config/autoload/nutgram.global.php (production)
'webhook' => [
    'url' => getenv('TELEGRAM_WEBHOOK_URL'),
],
```

4. Register the webhook:

```bash
vendor/bin/mezzio-sf-console nutgram:hook:set
```

## Creating Your First Handler

1. Create a command handler:

```php
namespace Bot\Handler;

use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class StartHandler extends Command
{
    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage('Hello! 👋');
    }
}
```

2. Register the handler:

```php
// config/autoload/nutgram.local.php or nutgram.global.php
'services' => [
    'start' => \Bot\Handler\StartHandler::class,
],
```

3. Register commands with Telegram:

```bash
vendor/bin/mezzio-sf-console nutgram:register-commands
```

## Verifying Setup

1. Check webhook status:

```bash
vendor/bin/mezzio-sf-console nutgram:hook:info
```

2. Message your bot on Telegram:
   - Find your bot by username
   - Send `/start` command
   - You should receive the welcome message

## Next Steps

- Read [Configuration](configuration.md) for detailed configuration options
- Learn about [Command Handlers](command-handlers.md)
- Understand [Webhook Setup](webhook.md)
- Review [Security Best Practices](security.md)
