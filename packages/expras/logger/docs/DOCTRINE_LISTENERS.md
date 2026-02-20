# Doctrine Entity Modifier Listeners

This document explains the pattern of using Doctrine listeners to make entity relations configurable.

## Problem

Log entities need to reference other entities (users, chats, etc.), but different applications might use different entity classes:

```php
// App A uses:
class User extends UserSuper {}

// App B uses:
class CustomUser extends UserSuper {}

// Log entity hardcodes:
#[ORM\ManyToOne(targetEntity: UserSuper::class)]  // ❌ Not flexible
protected ?UserSuper $user;
```

**Problem**: Can't change the target entity without modifying the log entity class.

---

## Solution: Doctrine Event Listeners

Use Doctrine's `loadClassMetadata` event to dynamically modify entity metadata based on configuration.

### **Pattern**

```php
class LogEntityModifierListener implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        
        // Only modify specific entity
        if ($classMetadata->getName() !== MyLogEntity::class) {
            return;
        }
        
        // Get configured entity class
        $config = $this->getContainer()->get('config');
        $targetEntity = $config['mymodule']['userEntity'] ?? DefaultUser::class;
        
        // Update association mapping
        if ($classMetadata->hasAssociation('user')) {
            $classMetadata->associationMappings['user']['targetEntity'] = $targetEntity;
        }
    }
    
    public function getSubscribedEvents(): array
    {
        return ['loadClassMetadata'];
    }
}
```

---

## Real Examples

### **1. Nutgram: TelegramLogEntityModifierListener**

**Entity:**
```php
class TelegramLogEntity extends AbstractLogEntity
{
    #[ORM\ManyToOne(targetEntity: DefaultUser::class)]  // Default
    protected ?User $user = null;
    
    #[ORM\ManyToOne(targetEntity: DefaultChat::class)]  // Default
    protected ?Chat $chat = null;
}
```

**Listener:**
```php
class TelegramLogEntityModifierListener implements EventSubscriber
{
    public function loadClassMetadata($eventArgs): void
    {
        if ($classMetadata->getName() !== TelegramLogEntity::class) {
            return;
        }
        
        $config = $this->getContainer()->get('config')['nutgram'];
        
        // Update user targetEntity
        $classMetadata->associationMappings['user']['targetEntity'] = 
            $config['userEntity']; // e.g., App\CustomTelegramUser::class
            
        // Update chat targetEntity
        $classMetadata->associationMappings['chat']['targetEntity'] = 
            $config['chatEntity']; // e.g., App\CustomChat::class
    }
}
```

**Configuration:**
```php
// config/nutgram.php
'nutgram' => [
    'userEntity' => App\Entity\CustomTelegramUser::class,
    'chatEntity' => App\Entity\CustomChat::class,
]
```

**Result**: TelegramLogEntity now references custom entities!

---

### **2. Admin: AdminLogEntityModifierListener**

**Entity:**
```php
class AdminRequestLogEntity extends AbstractLogEntity
{
    #[ORM\ManyToOne(targetEntity: UserSuper::class)]  // Default
    protected ?UserSuper $user = null;
}
```

**Listener:**
```php
class AdminLogEntityModifierListener implements EventSubscriber
{
    public function loadClassMetadata($eventArgs): void
    {
        if ($classMetadata->getName() !== AdminRequestLogEntity::class) {
            return;
        }
        
        $config = $this->getContainer()->get('config');
        
        // Try multiple config sources with fallback
        $userEntity = $config['exprass_admin']['userEntity'] 
            ?? $config['user']['entity'] 
            ?? UserSuper::class;
        
        // Update user targetEntity
        $classMetadata->associationMappings['user']['targetEntity'] = $userEntity;
    }
}
```

**Configuration Options:**
```php
// Option 1: Admin-specific
'exprass_admin' => [
    'userEntity' => App\Entity\AdminUser::class,
]

// Option 2: Global user config
'user' => [
    'entity' => App\Entity\User::class,
]

// Option 3: Use default (UserSuper::class)
```

---

## Registration

### **1. Register Listener in ConfigProvider**

```php
class ConfigProvider
{
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                MyLogEntityModifierListener::class,
            ],
        ];
    }
}
```

### **2. Register as Doctrine Subscriber**

```php
// config/doctrine.php
return [
    'doctrine' => [
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    MyLogEntityModifierListener::class,
                ],
            ],
        ],
    ]
];
```

---

## Benefits

✅ **Flexible**: Can use different entity classes per application  
✅ **Configuration-Driven**: No code changes needed  
✅ **Reusable**: Same log entity works across applications  
✅ **Type-Safe**: Still get proper type hints  
✅ **Migration-Safe**: Schema matches configured entities  

---

## When to Use This Pattern

Use modifier listeners when:
- ✅ Log entities reference configurable entities (users, chats, etc.)
- ✅ Different applications use different entity hierarchies
- ✅ Need flexibility without duplicating entity classes

Don't use when:
- ❌ Relation target is always the same
- ❌ Targeting concrete, non-configurable entities
- ❌ Simple scalar values (use columns instead)

---

## Complete Example

**Step 1: Entity with default target**
```php
#[ORM\ManyToOne(targetEntity: DefaultUser::class)]
protected ?User $user = null;
```

**Step 2: Listener**
```php
class LogModifierListener implements EventSubscriber
{
    public function loadClassMetadata($eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->getName() === MyLogEntity::class) {
            $config = $this->getContainer()->get('config');
            $metadata->associationMappings['user']['targetEntity'] = 
                $config['app']['userEntity'];
        }
    }
}
```

**Step 3: Register**
```php
// doctrine.php
'subscribers' => [LogModifierListener::class]
```

**Step 4: Configure**
```php
// app.php
'app' => ['userEntity' => App\CustomUser::class]
```

**Result**: MyLogEntity->user now references App\CustomUser instead of DefaultUser!

---

## Summary

| Module | Listener | Modifies | Configurable Via |
|--------|----------|----------|------------------|
| **Nutgram** | TelegramLogEntityModifierListener | TelegramLogEntity | `nutgram.userEntity`, `nutgram.chatEntity` |
| **Admin** | AdminLogEntityModifierListener | AdminRequestLogEntity | `exprass_admin.userEntity` |

Both use the same pattern to achieve configuration-driven entity relations! 🎯

