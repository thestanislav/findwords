# ExprAs Logger Module

A comprehensive logging infrastructure for the ExprAs framework. Provides PSR-3 compliant logging with support for multiple loggers, custom entities, and various handler types (file, database, syslog, etc.).

## Features

- **PSR-3 Compliant**: Full implementation of PSR-3 LoggerInterface
- **Multiple Loggers**: Each module can create its own logger
- **Flexible Handlers**: Stream, rotating file, database (Doctrine), syslog, custom
- **Custom Entities**: Use module-specific log entities extending AbstractLogEntity
- **Logger Registry**: Centralized discovery of all application loggers
- **Admin API**: REST endpoint to list all registered loggers
- **Request Context**: Automatically capture request data (URI, method, IP, body)
- **Monolog v3**: Built on modern, battle-tested logging library

---

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Architecture](#architecture)
- [For Module Developers](#for-module-developers)
- [Handler Types](#handler-types)
- [Configuration Reference](#configuration-reference)
- [Admin API](#admin-api)
- [Custom Entities](#custom-entities)
- [Best Practices](#best-practices)

---

## Installation

The module is automatically included in the ExprAs framework. Ensure you have the required dependencies:

```bash
composer require monolog/monolog:^3.0
```

---

## Quick Start

### Basic Usage (with Constructor Injection)

```php
use Psr\Log\LoggerInterface;

class MyService
{
    public function __construct(
        private LoggerInterface $logger
    ) {}
    
    public function doSomething()
    {
        $this->logger->info('Operation started');
        $this->logger->error('An error occurred', ['context' => 'additional info']);
    }
}
```

### Automatic Logger Injection (with LoggerProviderTrait)

> **✨ NEW**: Use `LoggerProviderTrait` for automatic logger injection without constructor parameters!  
> **📖 Full Guide**: See [LOGGER_PROVIDER_TRAIT.md](docs/LOGGER_PROVIDER_TRAIT.md)

```php
use ExprAs\Logger\Provider\LoggerProviderTrait;

class MyService
{
    use LoggerProviderTrait;
    
    public function doSomething()
    {
        // Logger is automatically injected by LoggerProviderInitializer
        $this->logger->info('Operation started');
        $this->logger->error('An error occurred', ['context' => 'additional info']);
    }
}
```

**Benefits:**
- ✅ No constructor injection needed for logger
- ✅ Works with or without other dependencies
- ✅ Automatically gets `expras_logger` injected
- ✅ Perfect for commands, listeners, and handlers

### Log Levels

```php
$logger->emergency('System is unusable');
$logger->alert('Action must be taken immediately');
$logger->critical('Critical conditions');
$logger->error('Error conditions');
$logger->warning('Warning conditions');
$logger->notice('Normal but significant');
$logger->info('Informational messages');
$logger->debug('Debug-level messages');
```

---

## Architecture

### Components

```
┌─────────────────────────────────────────────────────┐
│        ExprAs\Logger (Infrastructure)               │
├─────────────────────────────────────────────────────┤
│ • HandlerBuilder - Creates handlers from config     │
│ • ProcessorBuilder - Creates processors from config │
│ • LoggerRegistry - Discovers all loggers            │
│ • LoggerAbstractFactory - Auto-creates loggers      │
│ • AbstractLogEntity - Base class for log entities   │
└─────────────────────────────────────────────────────┘
```

### How It Works

1. **Configuration**: Define loggers in `config/logger.php`
2. **Auto-Creation**: `LoggerAbstractFactory` creates loggers on-demand
3. **Registration**: Created loggers auto-register with `LoggerRegistry`
4. **Usage**: Inject `LoggerInterface` or specific logger by name

---

## For Module Developers

> **📖 Full Guide**: See [EXTENDING.md](docs/EXTENDING.md) for complete documentation on creating custom loggers.

### Creating a Module-Specific Logger

**Step 1: Define Configuration**

Create `your-module/config/logger.php`:

```php
<?php

use Monolog\Level;
use YourModule\Entity\YourLogEntity;

return [
    'log' => [
        'yourmodule.logger' => [
            'name' => 'yourmodule',
            'handlers' => [
                'doctrine' => [
                    'name' => 'doctrine',
                    'options' => [
                        'entity' => YourLogEntity::class,
                        'level' => Level::Info,
                    ],
                ],
            ],
            'processors' => [
                'requestData' => [
                    'name' => 'requestData',
                ],
            ],
            'metadata' => [
                'entity' => YourLogEntity::class,
                'description' => 'Your Module Logger',
                'module' => 'YourModule',
            ],
        ],
    ],
];
```

**Step 2: Register Factory** (optional)

In your `ConfigProvider.php`:

```php
use ExprAs\Logger\Service\LoggerAbstractFactory;

public function getDependencies()
{
    return [
        'factories' => [
            'yourmodule.logger' => LoggerAbstractFactory::class,
        ],
    ];
}
```

> **Note**: With `LoggerAbstractFactory` in abstract_factories, explicit registration is optional!

**Step 3: Use Logger**

```php
class YourService
{
    public function __construct(
        #[Inject('yourmodule.logger')]
        private LoggerInterface $logger
    ) {}
    
    public function doSomething()
    {
        $this->logger->info('Module action performed', [
            'userId' => 123,
            'action' => 'create',
        ]);
    }
}
```

---

## Handler Types

### 1. Stream Handler

Logs to files or stdout/stderr.

```php
'stream' => [
    'name' => 'stream',
    'options' => [
        'stream' => 'data/logs/app.log',  // or 'php://stdout'
        'level' => Level::Debug,
        'bubble' => true,
    ],
],
```

### 2. Rotating File Handler

Creates daily/weekly log files, keeps last N files.

```php
'rotating_file' => [
    'name' => 'rotating_file',
    'options' => [
        'path' => 'data/logs/app.log',
        'maxFiles' => 30,  // Keep 30 days
        'level' => Level::Info,
    ],
],
```

### 3. Doctrine Handler

Logs to database using Doctrine ORM.

```php
'doctrine' => [
    'name' => 'doctrine',
    'options' => [
        'entity' => YourLogEntity::class,
        'level' => Level::Error,
    ],
],
```

### 4. Syslog Handler

Logs to system syslog.

```php
'syslog' => [
    'name' => 'syslog',
    'options' => [
        'ident' => 'expras',
        'facility' => LOG_USER,
        'level' => Level::Warning,
    ],
],
```

### 5. Custom Handler

Use your own handler implementation.

```php
'custom' => [
    'name' => 'custom',
    'options' => [
        'service' => MyCustomHandler::class,
    ],
],
```

---

## Configuration Reference

### Complete Logger Configuration

```php
'log' => [
    'logger.name' => [
        // Logger channel name
        'name' => 'channel_name',
        
        // Handlers (can have multiple)
        'handlers' => [
            'handler_key' => [
                'name' => 'stream|rotating_file|doctrine|syslog|custom',
                'options' => [
                    'level' => Level::Info,
                    // Handler-specific options
                ],
            ],
        ],
        
        // Processors (can have multiple)
        'processors' => [
            'processor_key' => [
                'name' => 'requestData|custom',
                'priority' => -1,
            ],
        ],
        
        // Metadata (for admin interface)
        'metadata' => [
            'entity' => LogEntity::class,
            'description' => 'Human-readable description',
            'module' => 'Module\\Namespace',
        ],
    ],
],
```

### Multiple Handlers Example

```php
'mylogger' => [
    'name' => 'myapp',
    'handlers' => [
        // Log everything to rotating file
        'all' => [
            'name' => 'rotating_file',
            'options' => [
                'path' => 'data/logs/all.log',
                'level' => Level::Debug,
            ],
        ],
        // Log only errors to database
        'errors' => [
            'name' => 'doctrine',
            'options' => [
                'entity' => ErrorLogEntity::class,
                'level' => Level::Error,
            ],
        ],
        // Log critical to syslog
        'critical' => [
            'name' => 'syslog',
            'options' => [
                'level' => Level::Critical,
            ],
        ],
    ],
],
```

---

## Admin API

### List All Loggers

```
GET /api/admin/loggers
```

**Response:**

```json
{
    "success": true,
    "count": 3,
    "data": [
        {
            "name": "expras_logger",
            "entity": "ExprAs\\Logger\\Entity\\ErrorLogEntity",
            "description": "ExprAs Framework Error Logger",
            "module": "ExprAs\\Logger",
            "channel": "expras_logger"
        },
        {
            "name": "admin.api.logger",
            "entity": "Admin\\Entity\\AdminRequestLogEntity",
            "description": "Admin API Request Logger",
            "module": "ExprAs\\Admin",
            "channel": "admin.api"
        }
    ]
}
```

---

## Custom Entities

### Creating a Custom Log Entity

**Step 1: Extend AbstractLogEntity**

```php
<?php

namespace YourModule\Entity;

use ExprAs\Logger\Entity\AbstractLogEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'yourmodule_logs')]
#[ORM\Entity]
class YourLogEntity extends AbstractLogEntity
{
    // Add module-specific fields
    
    #[ORM\Column(type: 'integer')]
    protected int $userId;
    
    #[ORM\Column(type: 'string')]
    protected string $actionType;
    
    // Getters and setters
    public function getUserId(): int
    {
        return $this->userId;
    }
    
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
    
    public function getActionType(): string
    {
        return $this->actionType;
    }
    
    public function setActionType(string $actionType): void
    {
        $this->actionType = $actionType;
    }
}
```

**Step 2: Create Migration**

```bash
vendor/bin/doctrine-module migrations:diff
vendor/bin/doctrine-module migrations:migrate
```

**Step 3: Use in Configuration**

```php
'yourmodule.logger' => [
    'handlers' => [
        'doctrine' => [
            'name' => 'doctrine',
            'options' => [
                'entity' => YourLogEntity::class,
            ],
        ],
    ],
],
```

### Inherited Fields from AbstractLogEntity

All custom entities automatically have:

- `id` - Primary key
- `datetime` - Timestamp
- `level` - Log level (integer)
- `levelName` - Log level name (e.g., "ERROR")
- `message` - Log message
- `channel` - Logger channel name
- `context` - Additional context (JSON)
- `extra` - Extra data from processors (JSON)

---

## Best Practices

### 1. Use Appropriate Log Levels

```php
// Emergency - system is unusable
$logger->emergency('Database is down, application cannot start');

// Error - runtime errors that don't require immediate action
$logger->error('Failed to process payment', ['orderId' => 123]);

// Warning - exceptional occurrences that are not errors
$logger->warning('API rate limit reached');

// Info - interesting events (user login, SQL logs)
$logger->info('User logged in', ['userId' => 456]);

// Debug - detailed debug information
$logger->debug('Query executed', ['sql' => 'SELECT * FROM users']);
```

### 2. Include Context

```php
// Good - includes context
$logger->error('Payment failed', [
    'userId' => $user->getId(),
    'amount' => $amount,
    'gateway' => 'stripe',
    'error' => $exception->getMessage(),
]);

// Bad - no context
$logger->error('Payment failed');
```

### 3. Use Separate Loggers for Different Concerns

```php
// Separate loggers for different purposes
$securityLogger = $container->get('security.logger');
$apiLogger = $container->get('api.logger');
$paymentLogger = $container->get('payment.logger');
```

### 4. Log Rotation

Always use rotating file handler for file logs:

```php
'handlers' => [
    'file' => [
        'name' => 'rotating_file',  // Not 'stream'
        'options' => [
            'maxFiles' => 30,  // Keep 30 days
        ],
    ],
],
```

### 5. Different Levels to Different Handlers

```php
'handlers' => [
    // Debug logs to file (high volume)
    'debug_file' => [
        'name' => 'rotating_file',
        'options' => ['level' => Level::Debug],
    ],
    // Errors to database (low volume, searchable)
    'error_db' => [
        'name' => 'doctrine',
        'options' => ['level' => Level::Error],
    ],
],
```

---

## Troubleshooting

### Logger Not Found

**Problem**: `Container entry "mymodule.logger" not found`

**Solution**: Ensure configuration exists in `config/logger.php` under `log` key and `LoggerAbstractFactory` is in `abstract_factories`.

### Logs Not Appearing in Database

**Problem**: Logs appear in files but not database

**Solution**: 
1. Check entity class exists and extends `AbstractLogEntity`
2. Run migrations: `vendor/bin/doctrine-module migrations:migrate`
3. Verify handler configuration includes correct entity class

### Permission Denied on Log Files

**Problem**: Cannot write to log file

**Solution**: Ensure `data/logs/` directory exists and is writable:

```bash
mkdir -p data/logs
chmod 777 data/logs
```

---

## Migration Guide

### From Old LogEntity to ErrorLogEntity

The old `LogEntity` is now deprecated. Update references:

```php
// Old
use ExprAs\Logger\Entity\LogEntity;

// New
use ExprAs\Logger\Entity\ErrorLogEntity;
```

### Database Migration

Rename table (if needed):

```sql
ALTER TABLE expras_logs RENAME TO expras_error_logs;
```

Or create migration:

```php
$this->addSql('ALTER TABLE expras_logs RENAME TO expras_error_logs');
```

---

## Advanced Usage

### Custom Processor

```php
class UserIdProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        $context['userId'] = $_SESSION['user_id'] ?? null;
        return $record->with(context: $context);
    }
}

// Register in ConfigProvider
'factories' => [
    UserIdProcessor::class => InvokableFactory::class,
]

// Use in config
'processors' => [
    'userId' => [
        'name' => 'custom',
        'options' => [
            'service' => UserIdProcessor::class,
        ],
    ],
],
```

### Custom Handler

```php
class SlackHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        // Send to Slack webhook
        $this->sendToSlack($record->formatted);
    }
}

// Use in config
'handlers' => [
    'slack' => [
        'name' => 'custom',
        'options' => [
            'service' => SlackHandler::class,
        ],
    ],
],
```

---

## License

Part of the ExprAs framework.

---

## Contributing

When adding features to the logger module:

1. Ensure PSR-3 compatibility
2. Add tests for new handlers/processors
3. Update this README
4. Add configuration examples

---

## Support

For issues and questions, please refer to the ExprAs framework documentation or create an issue in the project repository.
