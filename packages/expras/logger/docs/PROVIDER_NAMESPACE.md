# Logger Provider Namespace

**Location:** `ExprAs\Logger\Provider`

This namespace contains components for automatic logger injection into services.

## Components

### LoggerProviderTrait

**File:** `src/Provider/LoggerProviderTrait.php`

A trait that can be added to any class to enable automatic logger injection.

```php
use ExprAs\Logger\Provider\LoggerProviderTrait;

class MyService
{
    use LoggerProviderTrait;
    
    public function doSomething()
    {
        $this->logger->info('Action performed');
    }
}
```

### LoggerProviderInitializer

**File:** `src/Provider/LoggerProviderInitializer.php`

An initializer that automatically detects services using `LoggerProviderTrait` and injects the `expras_logger` service into them.

**How it works:**
1. Runs after ANY service is created (regardless of which factory created it)
2. Checks if the service uses `LoggerProviderTrait`
3. If yes, calls `setLogger()` with the `expras_logger` service
4. Service is fully configured with logger automatically

## Why "Provider" Namespace?

The name "Provider" was chosen because:
- These components **provide** logger functionality to classes
- Clear and descriptive naming
- Common pattern in frameworks (ServiceProvider, etc.)
- Groups related functionality together

## Benefits of this Approach

### 1. Works with ANY Factory

Unlike abstract factories, initializers run on ALL services:
- Custom factories
- ConfigAbstractFactory
- InvokableFactory
- ReflectionBasedAbstractFactory
- Any other factory

### 2. Simpler Implementation

Initializers don't need to:
- Handle service creation
- Manage dependencies
- Deal with constructor parameters

They just:
- Check if service has the trait
- Inject the logger
- Done!

### 3. Zero Configuration

Just add the trait to your class:

```php
class MyHandler
{
    use LoggerProviderTrait;
    
    // That's it! Logger is automatically available
}
```

No need to:
- Configure in ConfigAbstractFactory
- Create custom factory
- Update service manager config

### 4. Performance

Initializers are efficient:
- Quick trait check
- Only injects if trait is present
- No complex logic
- Minimal overhead

## Migration from Old Structure

If you have old imports, update them:

```php
// Old (deprecated)
use ExprAs\Logger\LoggerProviderTrait;
use ExprAs\Logger\Factory\LoggerProviderAbstractFactory;

// New (current)
use ExprAs\Logger\Provider\LoggerProviderTrait;
// LoggerProviderInitializer is configured automatically, no need to import
```

## Related Documentation

- [LOGGER_PROVIDER_TRAIT.md](LOGGER_PROVIDER_TRAIT.md) - Detailed usage guide
- [EXAMPLE_USAGE.md](EXAMPLE_USAGE.md) - Practical examples
- [Laminas ServiceManager Initializers](https://docs.laminas.dev/laminas-servicemanager/v4/configuring-the-service-manager/#initializers)

