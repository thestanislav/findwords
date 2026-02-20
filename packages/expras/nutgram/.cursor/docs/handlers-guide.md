# Nutgram Handlers Guide

Quick reference for creating handlers and handling inline buttons in ExprAs Nutgram.

## Basic Handler Structure

### 1. Simple Command Handler

```php
<?php

namespace Bot\Command;

use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class StartCommand extends Command
{
    protected string $command = 'start';
    protected ?string $description = 'Start the bot';

    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage('Welcome! Use /help to see available commands.');
    }
}
```

### 2. Command with Parameters

```php
<?php

namespace Bot\Command;

use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class SurveyCommand extends Command
{
    protected string $command = 'survey';
    protected ?string $description = 'Start survey';

    public function handle(Nutgram $bot): void
    {
        // Handle parameters from command delegation
        $params = $this->getParameters();
        
        if (isset($params['step'])) {
            $this->handleSurveyStep($bot, $params['step']);
            return;
        }
        
        // Default behavior when no parameters
        $this->startSurvey($bot);
    }
    
    private function startSurvey(Nutgram $bot): void
    {
        $bot->sendMessage('Survey started!');
    }
    
    private function handleSurveyStep(Nutgram $bot, string $step): void
    {
        $bot->sendMessage("Survey step: {$step}");
    }
}
```

## Callback Data Routing

### Automatic Command Routing

**All callback data starting with `/` is automatically routed to the corresponding command handler by the `CallbackQueryCommandDelegator`.**

```php
// These callbacks are automatically routed:
callback_data: '/start'           // → StartCommand::handle()
callback_data: '/survey'          // → SurveyCommand::handle()
callback_data: '/menu'            // → MenuCommand::handle()
```

### Passing Parameters via Callbacks

You can pass parameters to commands using JSON format:

```php
// JSON parameters
callback_data: '/survey {"step": "name", "type": "user"}'
callback_data: '/menu {"section": "settings"}'
callback_data: '/vote {"vote": 1, "meme_id": 123}'
```

### Command Handler with Parameters

```php
<?php

namespace Bot\Command;

use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class VoteCommand extends Command
{
    protected string $command = 'vote';
    protected ?string $description = 'Vote on items';

    public function handle(Nutgram $bot): void
    {
        $params = $this->getParameters();
        
        // Handle vote with parameters
        if (isset($params['vote']) && isset($params['item_id'])) {
            $this->processVote($bot, $params['vote'], $params['item_id']);
            return;
        }
        
        // Default behavior
        $this->showVoteOptions($bot);
    }
    
    private function processVote(Nutgram $bot, int $vote, int $itemId): void
    {
        // Process the vote
        $bot->sendMessage("Vote {$vote} recorded for item {$itemId}");
    }
    
    private function showVoteOptions(Nutgram $bot): void
    {
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '👍 Like',
                    callback_data: '/vote {"vote": 1, "item_id": 123}'
                ),
                InlineKeyboardButton::make(
                    text: '👎 Dislike', 
                    callback_data: '/vote {"vote": -1, "item_id": 123}'
                )
            );
            
        $bot->sendMessage('Vote on this item:', [
            'reply_markup' => $keyboard
        ]);
    }
}
```

## Inline Button Handling

### Creating Inline Keyboards

```php
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

private function showMenu(Nutgram $bot): void
{
    $keyboard = InlineKeyboardMarkup::make()
        ->addRow(
            InlineKeyboardButton::make(
                text: '📊 Start Survey',
                callback_data: '/survey'
            )
        )
        ->addRow(
            InlineKeyboardButton::make(
                text: '⚙️ Settings',
                callback_data: '/settings'
            )
        )
        ->addRow(
            InlineKeyboardButton::make(
                text: '❓ Help',
                callback_data: '/help'
            )
        );

    $bot->sendMessage('Choose an action:', [
        'reply_markup' => $keyboard
    ]);
}
```

### Navigation Between Commands

```php
private function showMainMenu(Nutgram $bot): void
{
    $keyboard = InlineKeyboardMarkup::make()
        ->addRow(
            InlineKeyboardButton::make(
                text: '🏠 Main Menu',
                callback_data: '/menu'
            )
        )
        ->addRow(
            InlineKeyboardButton::make(
                text: '📅 Program',
                callback_data: '/program'
            )
        );

    $bot->sendMessage('Navigation:', [
        'reply_markup' => $keyboard
    ]);
}
```

## Handler Registration

### 1. Register in Nutgram Config

```php
// src/Bot/config/nutgram.php
return [
    'nutgram' => [
        'handlers' => [
            StartCommand::class,
            SurveyCommand::class,
            VoteCommand::class,
            MenuCommand::class,
        ],
    ],
];
```

### 2. Register in ConfigProvider

```php
// src/Bot/src/ConfigProvider.php
public function getDependencies(): array
{
    return [
        'invokables' => [
            StartCommand::class,
            SurveyCommand::class,
            VoteCommand::class,
        ],
        'factories' => [
            MenuCommand::class => MenuCommandFactory::class,
        ],
    ];
}
```

## Best Practices

### 1. Use Command Delegation for All Callbacks

**✅ DO:**
```php
// Use command delegation with parameters
callback_data: '/survey {"step": "name"}'
callback_data: '/vote {"vote": 1, "item_id": 123}'
callback_data: '/menu'
```

**❌ DON'T:**
```php
// Don't use custom callback data
callback_data: 'survey_step_name'
callback_data: 'vote_1_123'
```

### 2. Handle Parameters in Command Handlers

```php
public function handle(Nutgram $bot): void
{
    $params = $this->getParameters();
    
    // Check for specific parameters
    if (isset($params['action'])) {
        $this->handleAction($bot, $params['action']);
        return;
    }
    
    // Default behavior
    $this->showDefault($bot);
}
```

### 3. Use Proper Error Handling

```php
public function handle(Nutgram $bot): void
{
    try {
        $params = $this->getParameters();
        
        if (isset($params['vote'])) {
            $this->processVote($bot, $params);
        } else {
            $this->showVoteOptions($bot);
        }
    } catch (\Exception $e) {
        // Log error if logger is available
        if (method_exists($bot, 'get') && $bot->get('expras_error_logger')) {
            $bot->get('expras_error_logger')->error("Command error: " . $e->getMessage());
        }
        
        $bot->sendMessage('An error occurred. Please try again.');
    }
}
```

## Parameter Access

### JSON Parameters

```php
// Callback: '/survey {"step": "name", "type": "user"}'
$params = $this->getParameters();
$step = $params['step'] ?? 'default';     // "name"
$type = $params['type'] ?? 'general';     // "user"
```

### Space-Separated Parameters

```php
// Callback: '/list page 2'
$params = $this->getParameters();
$page = $params[0] ?? '1';               // "page"
$number = $params[1] ?? '1';             // "2"
```

## Common Patterns

### 1. Multi-Step Forms

```php
class SurveyCommand extends Command
{
    public function handle(Nutgram $bot): void
    {
        $params = $this->getParameters();
        
        switch ($params['step'] ?? 'start') {
            case 'start':
                $this->showStep1($bot);
                break;
            case 'name':
                $this->showStep2($bot);
                break;
            case 'email':
                $this->showStep3($bot);
                break;
            default:
                $this->showStep1($bot);
        }
    }
    
    private function showStep1(Nutgram $bot): void
    {
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: 'Next →',
                    callback_data: '/survey {"step": "name"}'
                )
            );
            
        $bot->sendMessage('Step 1: Introduction', [
            'reply_markup' => $keyboard
        ]);
    }
}
```

### 2. Voting System

```php
class VoteCommand extends Command
{
    public function handle(Nutgram $bot): void
    {
        $params = $this->getParameters();
        
        if (isset($params['vote']) && isset($params['item_id'])) {
            $this->processVote($bot, $params['vote'], $params['item_id']);
            return;
        }
        
        $this->showVoteOptions($bot);
    }
    
    private function showVoteOptions(Nutgram $bot): void
    {
        $itemId = 123; // Get from context
        
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '😍 Like',
                    callback_data: "/vote {\"vote\": 1, \"item_id\": {$itemId}}"
                ),
                InlineKeyboardButton::make(
                    text: '😐 Neutral',
                    callback_data: "/vote {\"vote\": 0, \"item_id\": {$itemId}}"
                ),
                InlineKeyboardButton::make(
                    text: '😞 Dislike',
                    callback_data: "/vote {\"vote\": -1, \"item_id\": {$itemId}}"
                )
            );
            
        $bot->sendMessage('Rate this item:', [
            'reply_markup' => $keyboard
        ]);
    }
}
```

## Key Points

1. **All callbacks starting with `/` are automatically routed** to the corresponding command handler
2. **Use JSON parameters** to pass data to commands: `/command {"key": "value"}`
3. **Commands should handle parameters** using `$this->getParameters()`
4. **No manual callback query handling** needed - the delegator handles everything
5. **Keep commands simple** - focus on processing parameters and business logic
6. **Use proper error handling** and logging throughout

This approach provides a clean, consistent way to handle all bot interactions through the command delegation system.