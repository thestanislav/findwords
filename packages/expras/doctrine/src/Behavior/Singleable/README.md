# Singleable Behavior

The Singleable behavior ensures that only one entity within a defined group can have a specific field value. This is commonly used for "default" flags, "primary" status, or any scenario where exclusivity is required.

## Use Cases

- **Default addresses**: Only one shipping/billing address per user can be marked as default
- **Primary contact**: Only one contact method can be primary for a customer
- **Featured items**: Only one product can be featured in a category
- **Active status**: Only one item can be active within a group at a time

## Features

- ✅ **Database agnostic**: Works with MySQL, PostgreSQL, SQLite, and all Doctrine-supported platforms
- ✅ **Automatic enforcement**: When an entity gets the exclusive value, all others are automatically reset
- ✅ **Group scoping**: Define exclusivity per user, per category, or any grouping field
- ✅ **Flexible filtering**: Add static filters for complex scenarios
- ✅ **Fallback logic**: When removing the entity with the exclusive value, automatically assigns it to the first available entity

## Installation

The behavior is automatically available as part of the ExprAs Doctrine package.

## Usage

### Basic Example

```php
use ExprAs\Doctrine\Behavior\Singleable\SingleableInterface;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Address implements SingleableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string')]
    private string $street;

    // Only one address per user can be default
    #[Singleable(
        value: true,
        cancelValue: false,
        group: ['userId']
    )]
    #[ORM\Column(type: 'boolean')]
    private bool $isDefault = false;

    // ... getters and setters
}
```

### Advanced Example with Multiple Groups

```php
use ExprAs\Doctrine\Behavior\Singleable\SingleableInterface;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product implements SingleableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $categoryId;

    #[ORM\Column(type: 'integer')]
    private int $brandId;

    // Only one product per category can be featured
    #[Singleable(
        value: true,
        cancelValue: false,
        group: ['categoryId']
    )]
    #[ORM\Column(type: 'boolean')]
    private bool $isFeatured = false;

    // Only one product per brand per category can be highlighted
    #[Singleable(
        value: true,
        cancelValue: false,
        group: ['categoryId', 'brandId']
    )]
    #[ORM\Column(type: 'boolean')]
    private bool $isHighlighted = false;

    // ... getters and setters
}
```

### Example with Static Filters

```php
use ExprAs\Doctrine\Behavior\Singleable\SingleableInterface;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Contact implements SingleableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string')]
    private string $type; // 'phone', 'email', 'address'

    // Only one primary contact per user per type
    #[Singleable(
        value: true,
        cancelValue: false,
        group: ['userId', 'type']
    )]
    #[ORM\Column(type: 'boolean')]
    private bool $isPrimary = false;

    // Only one email can be verified and primary per user
    #[Singleable(
        value: true,
        cancelValue: false,
        group: ['userId'],
        filters: ['type' => 'email', 'verified' => true]
    )]
    #[ORM\Column(type: 'boolean')]
    private bool $isPreferredEmail = false;

    #[ORM\Column(type: 'boolean')]
    private bool $verified = false;

    // ... getters and setters
}
```

### Example with ensureFilters (Fallback Logic)

The `ensureFilters` parameter is useful when you want to control which entities can automatically receive the exclusive value as a fallback.

```php
use ExprAs\Doctrine\Behavior\Singleable\SingleableInterface;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ShippingAddress implements SingleableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string')]
    private string $address;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    // Only one default address per user
    // When the default address is deleted, only active addresses can become the new default
    #[Singleable(
        value: true,
        cancelValue: false,
        group: ['userId'],
        ensureFilters: ['active' => true]  // Only active addresses can be auto-selected as default
    )]
    #[ORM\Column(type: 'boolean')]
    private bool $isDefault = false;

    // ... getters and setters
}
```

**Use Case**: If a user deletes their default address, the system will automatically set another address as default, but **only if** it's an active address. Inactive addresses won't be considered for the fallback.

```php
use ExprAs\Doctrine\Behavior\Singleable\SingleableInterface;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PaymentMethod implements SingleableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string')]
    private string $cardNumber;

    #[ORM\Column(type: 'boolean')]
    private bool $verified = false;

    #[ORM\Column(type: 'boolean')]
    private bool $expired = false;

    // Only one primary payment method per user
    // Fallback only to verified and non-expired cards
    #[Singleable(
        value: true,
        cancelValue: false,
        group: ['userId'],
        ensureFilters: ['verified' => true, 'expired' => false]
    )]
    #[ORM\Column(type: 'boolean')]
    private bool $isPrimary = false;

    // ... getters and setters
}
```

**Use Case**: When a user removes their primary payment method, the system will only auto-select a replacement that is both verified and not expired.

## Annotation Parameters

### `value` (required)
The value that should be exclusive within the group. Typically `true` for boolean fields, but can be any type.

```php
#[Singleable(value: true)]
#[Singleable(value: 'primary')]
#[Singleable(value: 1)]
```

### `cancelValue` (optional, default: `null`)
The value to set on other entities when one entity gets the exclusive value.

```php
#[Singleable(value: true, cancelValue: false)]
#[Singleable(value: 'active', cancelValue: 'inactive')]
```

### `cancelEscorted` (optional, default: `true`)
Whether to automatically reset other entities. If set to `false`, the behavior only ensures the first entity gets the value but doesn't reset others.

```php
#[Singleable(value: true, cancelEscorted: false)]
```

### `group` (optional, default: `[]`)
Fields that define the grouping scope. Exclusivity is enforced within each unique combination of group field values.

```php
// One per user
#[Singleable(value: true, group: ['userId'])]

// One per user per category
#[Singleable(value: true, group: ['userId', 'categoryId'])]
```

### `filters` (optional, default: `[]`)
Additional static filters to narrow down the exclusivity scope when canceling other entities.

```php
// Only among verified items
#[Singleable(value: true, filters: ['verified' => true])]

// Only for specific type
#[Singleable(value: true, filters: ['type' => 'shipping', 'active' => true])]
```

### `ensureFilters` (optional, default: `[]`)
Additional filters applied only when ensuring the first entity has the exclusive value (fallback logic). This is useful when you want to restrict which entities can automatically receive the exclusive value.

```php
// Only active entities can become default
#[Singleable(
    value: true, 
    cancelValue: false,
    group: ['userId'],
    ensureFilters: ['active' => true]
)]

// Only published and verified items can be featured
#[Singleable(
    value: true,
    cancelValue: false,
    ensureFilters: ['published' => true, 'verified' => true]
)]
```

**Note**: Both `filters` and `ensureFilters` are merged when counting entities in the fallback logic, but only `filters` is used when canceling other entities.

## How It Works

1. **When creating/updating an entity**: If the entity gets the exclusive value, all other entities in the same group (matching `filters`) are automatically reset to `cancelValue`.

2. **When deleting an entity**: If the deleted entity had the exclusive value, the behavior automatically assigns the value to the first available entity in the group (matching both `filters` and `ensureFilters` if specified).

3. **Fallback filtering**: The `ensureFilters` parameter allows you to restrict which entities can automatically receive the exclusive value during fallback logic, while `filters` applies to both canceling and fallback operations.

4. **Database agnostic**: Uses Doctrine DQL (platform-agnostic query language) instead of native SQL, ensuring compatibility with all supported databases.

## Behavior Lifecycle

- **loadClassMetadata**: Reads Singleable attributes from entity properties
- **prePersist**: Enforces exclusivity when creating new entities
- **preUpdate**: Enforces exclusivity when updating existing entities
- **preRemove**: Ensures group has a fallback entity when deleting

## Configuration

The behavior is automatically registered if you're using the ExprAs Doctrine package. No additional configuration is required.

If you need to manually register it:

```php
// In your Doctrine configuration
$evm = new EventManager();
$evm->addEventSubscriber(new \ExprAs\Doctrine\Behavior\Singleable\SingleableListener());
```

## Best Practices

1. **Always set cancelValue**: Explicitly define what value other entities should get when one becomes exclusive.

2. **Use meaningful groups**: Group by the appropriate scope (user, category, etc.) to ensure correct exclusivity boundaries.

3. **Consider performance**: For large datasets, ensure group fields are indexed in the database.

4. **Test edge cases**: Verify behavior when deleting the exclusive entity, when no entities exist, and with concurrent updates.

## Platform Support

✅ MySQL / MariaDB  
✅ PostgreSQL  
✅ SQLite  
✅ Oracle  
✅ Microsoft SQL Server  
✅ All Doctrine-supported platforms

The behavior uses DQL queries which are automatically translated by Doctrine to the appropriate SQL dialect for your database platform.

