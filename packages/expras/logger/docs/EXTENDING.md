# Extending Logger for Modules

This document explains how modules can create their own loggers with custom entities and field mapping.

## Architecture: Separation of Concerns

The logger module follows the **Open-Closed Principle**: open for extension, closed for modification.

```
┌─────────────────────────────────────────────────────────────┐
│  Core Logger Module (logger/)                               │
│  - AbstractLogEntity (common fields)                        │
│  - DoctrineHandler (common field mapping + extension point) │
│  - HandlerBuilder, ProcessorBuilder, LoggerRegistry         │
└─────────────────────────────────────────────────────────────┘
                          ↑ extends
┌─────────────────────────────────────────────────────────────┐
│  Module-Specific (e.g., nutgram/)                           │
│  - TelegramLogEntity extends AbstractLogEntity              │
│  - NutgramDoctrineHandler extends DoctrineHandler           │
│  - TelegramContextProcessor                                 │
└─────────────────────────────────────────────────────────────┘
```

---

## Why Module-Specific Handlers?

### ❌ **Bad: Hardcoded in Core**

```php
// In core DoctrineHandler - BAD!
class DoctrineHandler {
    protected function write(LogRecord $record): void {
        // ... common mapping ...
        
        // Hardcoded for ErrorLogEntity - WRONG!
        $this->setOptionalField($entity, 'setFile', ...);
        $this->setOptionalField($entity, 'setRequestUri', ...);
        
        // Hardcoded for TelegramLogEntity - WRONG!
        $this->setOptionalField($entity, 'setUpdateId', ...);
        $this->setOptionalField($entity, 'setChatId', ...);
        
        // Every new module needs to modify core handler - BAD!
    }
}
```

**Problems:**
- Core handler knows about all modules
- Tight coupling
- Violates Open-Closed Principle
- Cannot add new module without modifying core

---

### ✅ **Good: Extension Point**

```php
// In core DoctrineHandler - GOOD!
class DoctrineHandler {
    protected function write(LogRecord $record): void {
        // Map ONLY AbstractLogEntity fields (common to all)
        $entity->setDatetime(...);
        $entity->setLevel(...);
        $entity->setMessage(...);
        $entity->setChannel(...);
        $entity->setContext(...);
        $entity->setExtra(...);
        
        // Extension point for subclasses
        $this->mapAdditionalFields($entity, $record);
    }
    
    protected function mapAdditionalFields(object $entity, LogRecord $record): void {
        // Default: empty
        // Subclasses override to add their own mappings
    }
}

// In logger module (for ErrorLogEntity) - GOOD!
class ErrorDoctrineHandler extends DoctrineHandler {
    protected function mapAdditionalFields(object $entity, LogRecord $record): void {
        // Map error-specific fields
        $this->setOptionalField($entity, 'setFile', ...);
        $this->setOptionalField($entity, 'setRequestUri', ...);
    }
}

// In nutgram module (for TelegramLogEntity) - GOOD!
class NutgramDoctrineHandler extends DoctrineHandler {
    protected function mapAdditionalFields(object $entity, LogRecord $record): void {
        // Map Telegram-specific fields
        $this->setOptionalField($entity, 'setUpdateId', ...);
        $this->setOptionalField($entity, 'setChatId', ...);
    }
}
```

**Benefits:**
- Core handler stays generic
- Each module manages its own mapping
- No coupling between core and modules
- Can add new modules without touching core
- Follows Open-Closed Principle

---

## Step-by-Step: Creating Custom Logger

### **Step 1: Create Custom Entity**

```php
<?php
// mymodule/src/Entity/MyModuleLogEntity.php

namespace MyModule\Entity;

use ExprAs\Logger\Entity\AbstractLogEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'mymodule_logs')]
#[ORM\Entity]
class MyModuleLogEntity extends AbstractLogEntity
{
    // Add your module-specific fields
    
    #[ORM\Column(name: 'custom_field', type: 'string', nullable: true)]
    protected ?string $customField = null;
    
    #[ORM\Column(name: 'user_id', type: 'integer', nullable: true)]
    protected ?int $userId = null;
    
    // Getters and setters
    public function getCustomField(): ?string
    {
        return $this->customField;
    }
    
    public function setCustomField(?string $customField): void
    {
        $this->customField = $customField;
    }
    
    public function getUserId(): ?int
    {
        return $this->userId;
    }
    
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }
}
```

---

### **Step 2: Create Custom Doctrine Handler**

```php
<?php
// mymodule/src/Handler/MyModuleDoctrineHandler.php

namespace MyModule\Handler;

use ExprAs\Logger\LogHandler\DoctrineHandler;
use Monolog\LogRecord;

class MyModuleDoctrineHandler extends DoctrineHandler
{
    /**
     * Map module-specific fields from context to entity
     */
    protected function mapAdditionalFields(object $entity, LogRecord $record): void
    {
        // Map your custom fields from context
        $this->setOptionalField($entity, 'setCustomField', $record->context['customField'] ?? null);
        $this->setOptionalField($entity, 'setUserId', $record->context['userId'] ?? null);
    }
}
```

---

### **Step 3: Create Factory**

```php
<?php
// mymodule/src/Handler/MyModuleDoctrineHandlerFactory.php

namespace MyModule\Handler;

use Doctrine\ORM\EntityManager;
use MyModule\Entity\MyModuleLogEntity;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MyModuleDoctrineHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new MyModuleDoctrineHandler(
            entityManager: $container->get(EntityManager::class),
            hydrator: null,
            entityName: $options['entityName'] ?? MyModuleLogEntity::class,
            level: $options['level'] ?? \Monolog\Level::Debug,
            bubble: $options['bubble'] ?? true
        );
    }
}
```

---

### **Step 4: Register in ConfigProvider**

```php
<?php
// mymodule/src/ConfigProvider.php

use MyModule\Handler\MyModuleDoctrineHandler;
use MyModule\Handler\MyModuleDoctrineHandlerFactory;

class ConfigProvider
{
    public function getDependencies(): array
    {
        return [
            'factories' => [
                'mymodule.logger' => LoggerAbstractFactory::class,
                MyModuleDoctrineHandler::class => MyModuleDoctrineHandlerFactory::class,
            ],
        ];
    }
}
```

---

### **Step 5: Configure Logger**

```php
<?php
// mymodule/config/logger.php

use MyModule\Entity\MyModuleLogEntity;
use MyModule\Handler\MyModuleDoctrineHandler;
use Monolog\Level;

return [
    'log' => [
        'mymodule.logger' => [
            'name' => 'mymodule',
            'handlers' => [
                'database' => [
                    'name' => 'custom',
                    'options' => [
                        'service' => MyModuleDoctrineHandler::class,
                    ],
                ],
            ],
            'processors' => [
                // Add your custom processor if needed
            ],
            'metadata' => [
                'entity' => MyModuleLogEntity::class,
                'description' => 'My Module Logger',
                'module' => 'MyModule',
            ],
        ],
    ],
];
```

---

### **Step 6: Optional - Create Custom Processor**

```php
<?php
// mymodule/src/Service/MyModuleContextProcessor.php

namespace MyModule\Service;

use Monolog\LogRecord;

class MyModuleContextProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        
        // Auto-add module-specific context
        $context['customField'] = $this->getCustomData();
        $context['userId'] = $this->getCurrentUserId();
        
        return $record->with(context: $context);
    }
    
    private function getCustomData(): string
    {
        // Your logic here
    }
    
    private function getCurrentUserId(): ?int
    {
        // Your logic here
    }
}
```

---

## Complete Flow

```
1. Log Created:
   $logger->error('Error', ['key' => 'value'])
           ↓
2. Custom Processor (Optional):
   MyModuleContextProcessor adds:
   - customField
   - userId
           ↓
3. Custom Handler:
   MyModuleDoctrineHandler->mapAdditionalFields()
   Maps context → entity fields
           ↓
4. Database:
   mymodule_logs table with all fields populated
```

---

## Real Examples

### **Logger Module (ErrorLogEntity)**

The logger module itself follows this pattern:

**Files:**
- `logger/src/Entity/ErrorLogEntity.php` - Error-specific entity
- `logger/src/Handler/ErrorDoctrineHandler.php` - Error-specific handler
- `logger/src/Handler/ErrorDoctrineHandlerFactory.php` - Factory
- `logger/config/logger.php` - Configuration

### **Nutgram Module (TelegramLogEntity)**

See the Nutgram module for a complete implementation:

**Files:**
- `nutgram/src/Entity/TelegramLogEntity.php` - Custom entity
- `nutgram/src/Handler/NutgramDoctrineHandler.php` - Custom handler
- `nutgram/src/Handler/NutgramDoctrineHandlerFactory.php` - Factory
- `nutgram/src/Service/TelegramContextProcessor.php` - Custom processor
- `nutgram/config/logger.php` - Configuration

**Usage:**
```php
// Automatic context added by TelegramContextProcessor
$logger->error('Bot error', ['error' => $e->getMessage()]);

// Database will have:
// - Common: datetime, level, message
// - Telegram: updateId, chatId, userId, handler, messageText
// - Custom: error (in context JSON)
```

---

## Benefits of This Architecture

✅ **Modular**: Each module manages its own logging  
✅ **Extensible**: Easy to add new modules  
✅ **Clean**: No coupling between core and modules  
✅ **Maintainable**: Changes in one module don't affect others  
✅ **Follows SOLID**: Open-Closed Principle  
✅ **Reusable**: Core components work for all modules  

---

## Summary

1. **Core provides**: Base entity, base handler with extension point, infrastructure
2. **Modules provide**: Custom entity, custom handler, custom processor
3. **Configuration wires**: Everything together using service container

**Key Pattern**: Template Method Pattern in `DoctrineHandler::write()` with `mapAdditionalFields()` as the hook.

