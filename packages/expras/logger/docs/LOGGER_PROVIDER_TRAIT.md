# LoggerProviderTrait

Automatic logger injection for any service using the `LoggerProviderTrait`.

**Namespace:** `ExprAs\Logger\Provider`

## Overview

The `LoggerProviderTrait` provides a simple way to inject the `expras_logger` into any service without manually configuring it in every factory. The `LoggerProviderInitializer` automatically detects services using the trait and injects the logger after the service is created.

## Usage

### Basic Usage - No Constructor Dependencies

For simple services with no constructor dependencies:

```php
<?php

namespace App\Service;

use ExprAs\Logger\Provider\LoggerProviderTrait;

class MyService
{
    use LoggerProviderTrait;
    
    public function doSomething()
    {
        $this->logger->info('Something happened');
        $this->logger->error('An error occurred', ['context' => 'data']);
    }
}
```

The service will be automatically created and the logger injected when you request it from the container:

```php
$service = $container->get(MyService::class);
$service->doSomething(); // Logger is available
```

### Advanced Usage - With Dependencies

For services with constructor dependencies, use `ConfigAbstractFactory`:

```php
<?php

namespace App\Service;

use ExprAs\Logger\Provider\LoggerProviderTrait;
use Doctrine\ORM\EntityManager;

class UserService
{
    use LoggerProviderTrait;
    
    public function __construct(
        private EntityManager $entityManager,
        private NotificationService $notificationService
    ) {}
    
    public function createUser(array $data)
    {
        $this->logger->info('Creating user', ['data' => $data]);
        
        // ... business logic ...
        
        $this->logger->info('User created successfully');
    }
}
```

Configure dependencies in your config file:

```php
<?php

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;

return [
    ConfigAbstractFactory::class => [
        UserService::class => [
            EntityManager::class,
            NotificationService::class,
        ],
    ],
];
```

The service creation flow:
1. `ConfigAbstractFactory` creates the service with its dependencies
2. `LoggerProviderInitializer` runs automatically after creation
3. Initializer detects that `UserService` uses `LoggerProviderTrait`
4. Initializer calls `setLogger()` to inject `expras_logger`

## How It Works

### The Trait

```php
trait LoggerProviderTrait
{
    protected LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
```

### The Initializer

The `LoggerProviderInitializer`:

1. **Runs** after ANY service is created (regardless of factory used)
2. **Checks** if the service instance uses `LoggerProviderTrait`
3. **Injects** the `expras_logger` by calling `setLogger()`

This is simpler and more flexible than an abstract factory because:
- Works with ANY factory (custom, ConfigAbstractFactory, InvokableFactory, etc.)
- No need to handle service creation
- Just checks and injects after creation
- Automatic for all services with the trait

## Configuration

The initializer is already registered in the logger module's `ConfigProvider`:

```php
'initializers' => [
    LoggerProviderInitializer::class, // <-- Auto logger injection
],
```

## Benefits

1. **DRY Principle**: No need to inject logger in every factory
2. **Consistency**: All services use the same logger (`expras_logger`)
3. **Simplicity**: Just add the trait, logger is available
4. **Flexibility**: Works with or without other dependencies

## Examples

### Example 1: Command Handler

```php
<?php

namespace Bot\Command;

use ExprAs\Logger\Provider\LoggerProviderTrait;
use SergiX44\Nutgram\Nutgram;

class MyCommand
{
    use LoggerProviderTrait;
    
    public function __invoke(Nutgram $bot)
    {
        $userId = $bot->userId();
        $this->logger->info('Command executed', ['user_id' => $userId]);
        
        // ... command logic ...
    }
}
```

### Example 2: Event Listener

```php
<?php

namespace Bot\Listener;

use ExprAs\Logger\Provider\LoggerProviderTrait;
use Doctrine\ORM\Event\PostPersistEventArgs;

class EntityCreatedListener
{
    use LoggerProviderTrait;
    
    public function postPersist(PostPersistEventArgs $args)
    {
        $entity = $args->getObject();
        
        $this->logger->info('Entity created', [
            'entity' => get_class($entity),
            'id' => method_exists($entity, 'getId') ? $entity->getId() : null,
        ]);
    }
}
```

### Example 3: API Handler

```php
<?php

namespace Bot\Handler;

use ExprAs\Logger\Provider\LoggerProviderTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiHandler implements RequestHandlerInterface
{
    use LoggerProviderTrait;
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('API request received', [
            'uri' => $request->getUri()->getPath(),
            'method' => $request->getMethod(),
        ]);
        
        // ... handler logic ...
    }
}
```

## Logger Configuration

The injected logger is configured in `config/autoload/logger.php`:

```php
'log' => [
    'expras_logger' => [
        'name' => 'expras_logger',
        'handlers' => [
            'stream' => [
                'name' => 'stream',
                'options' => [
                    'stream' => 'data/logs/expras-error.log',
                    'level' => Level::Warning,
                ],
            ],
        ],
        // ... more configuration ...
    ],
],
```

You can customize logging behavior by modifying this configuration.

## Troubleshooting

### Error: "Cannot automatically create X. Constructor has required parameters"

**Solution**: Configure the service with `ConfigAbstractFactory`:

```php
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;

return [
    ConfigAbstractFactory::class => [
        YourService::class => [
            Dependency1::class,
            Dependency2::class,
        ],
    ],
];
```

### Logger is null/not set

**Verify**:
1. The trait is properly imported: `use ExprAs\Logger\Provider\LoggerProviderTrait;`
2. The trait is used in the class: `use LoggerProviderTrait;`
3. The service is retrieved from the container, not instantiated with `new`

### Multiple loggers needed

If you need a different logger for a specific service, you can:

1. Inject it via constructor:
```php
public function __construct(LoggerInterface $customLogger)
{
    $this->logger = $customLogger; // Override trait property
}
```

2. Or override `setLogger()`:
```php
public function setLogger(LoggerInterface $logger): void
{
    // Use custom logger instead
    $this->logger = $container->get('my.custom.logger');
}
```

## Related Documentation

- [EXTENDING.md](EXTENDING.md) - Logger module extension guide
- [DOCTRINE_LISTENERS.md](DOCTRINE_LISTENERS.md) - Doctrine event listener logging
- Laminas ServiceManager: https://docs.laminas.dev/laminas-servicemanager/

