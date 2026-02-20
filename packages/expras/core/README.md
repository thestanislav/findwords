# ExprAs Core Package

Core functionality package providing essential features for Mezzio applications.

## Installation

```bash
composer require expras/core
```

## Features

### Configuration Management
- Environment-based configuration
- Easy configuration overrides
- Dot notation support for config access
- Environment variable integration

### Session Handling
- PSR-7 compatible session middleware
- Session storage adapters
- Flash message support
- Session encryption

### Cache Integration
- PSR-6 Cache interface implementation
- PSR-16 Simple Cache implementation
- Multiple cache backend support
- Cache tagging and invalidation

### Console Commands
- Symfony Console integration
- Custom command framework
- Interactive CLI tools
- Command scheduling support

### Middleware Pipeline
- Configurable middleware chains
- Priority-based middleware sorting
- Conditional middleware execution
- Error handling middleware

### Service Container
- PSR-11 container extensions
- Factory generators
- Service decoration support
- Lazy loading services

## Usage

### Configuration Setup

```php
use Expras\Core\Config\ConfigProvider;

class MyConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // Your dependencies
            ],
        ];
    }
}
```

### Session Management

```php
use Expras\Core\Session\SessionMiddleware;

// In your pipeline:
$app->pipe(SessionMiddleware::class);

// In your handler:
$session = $request->getAttribute('session');
$session->set('key', 'value');
```

### Cache Usage

```php
use Expras\Core\Cache\CacheFactory;

$cache = $container->get(CacheFactory::class);
$cache->set('key', 'value', 3600); // Cache for 1 hour
```

## Requirements

- PHP 8.0 or higher
- Mezzio 3.x
- PSR-7 implementation
- PSR-11 container
- PSR-15 middleware

## Documentation

For detailed documentation, please visit the [official documentation](https://docs.expras.com/core).

## Contributing

Please read [CONTRIBUTING.md](../CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests. 