# Migration from Loneliable to Singleable

## Overview

The `Loneliable` behavior has been renamed to `Singleable` with bug fixes, optimizations, and improved cross-platform support.

## What Changed

### 1. Naming
- **Old**: `Loneliable` (with typo in class name: `LonelibaleListener`)
- **New**: `Singleable` (consistent naming throughout)

### 2. Namespace Changes
```php
// Before
use ExprAs\Doctrine\Behavior\Loneliable\LoneliableInterface;
use ExprAs\Doctrine\Behavior\Loneliable\Mapping\Annotation\Loneliable;

// After
use ExprAs\Doctrine\Behavior\Singleable\SingleableInterface;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
```

### 3. Critical Bug Fixes

#### Fixed SQL Logic Bug (Line 107)
**Before** (incorrect - would update ALL entities):
```php
$dql .= ' and ( 1= 1';
foreach ($ids as $_id => $_v) {
    $dql .= ' or e.' . $_id . ' != :' . $_id;
}
```

**After** (correct - excludes only current entity):
```php
$dql .= ' AND (';
$first = true;
foreach ($ids as $idField => $idValue) {
    if (!$first) {
        $dql .= ' OR ';
    }
    $dql .= 'e.' . $idField . ' != :' . $idField;
    $parameters[$idField] = $idValue;
    $first = false;
}
$dql .= ')';
```

#### Fixed Query Execution (Line 117)
**Before**:
```php
$query->getOneOrNullResult(); // Fetches but doesn't execute UPDATE
```

**After**:
```php
$query->execute(); // Properly executes UPDATE query
```

#### Removed Unused Import
Removed non-existent `LoneliableGroup` class reference from Driver/Annotation.php

### 4. Cross-Platform Support

**Improvements**:
- Uses DQL (Doctrine Query Language) exclusively instead of native SQL
- Compatible with MySQL, PostgreSQL, SQLite, Oracle, SQL Server
- Platform-agnostic date/time functions
- No database-specific syntax

### 5. Code Improvements

- Added comprehensive PHPDoc comments
- Improved method names: `_cancelEntities` → `cancelOtherEntities`, `_handleFirstEntity` → `ensureFirstEntityHasValue`
- Better variable naming and code organization
- Added type hints for method parameters and return types
- Fixed inverted logic in field value checks

### 6. Documentation

- Created comprehensive README.md with usage examples
- Added inline code documentation
- Provided examples for common use cases
- Documented all annotation parameters

## Migration Steps

### For Existing Code Using Loneliable

1. **Update Entity Interfaces**:
```php
// Before
class Address implements LoneliableInterface

// After  
class Address implements SingleableInterface
```

2. **Update Use Statements**:
```php
// Before
use ExprAs\Doctrine\Behavior\Loneliable\LoneliableInterface;
use ExprAs\Doctrine\Behavior\Loneliable\Mapping\Annotation\Loneliable;

// After
use ExprAs\Doctrine\Behavior\Singleable\SingleableInterface;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
```

3. **Update Attributes**:
```php
// Before
#[Loneliable(value: true, cancelValue: false, group: ['userId'])]

// After
#[Singleable(value: true, cancelValue: false, group: ['userId'])]
```

4. **Update Configuration** (Already done in `/sites/expras/doctrine/config/doctrine.php`):
```php
// Before
use ExprAs\Doctrine\Behavior\Loneliable\LonelibaleListener;
// ...
'subscribers' => [
    // LonelibaleListener::class, // was commented out
]

// After
use ExprAs\Doctrine\Behavior\Singleable\SingleableListener;
// ...
'subscribers' => [
    SingleableListener::class,
]
```

## Files Modified

### Created
- `/sites/expras/doctrine/src/Behavior/Singleable/SingleableInterface.php`
- `/sites/expras/doctrine/src/Behavior/Singleable/SingleableListener.php`
- `/sites/expras/doctrine/src/Behavior/Singleable/Mapping/Annotation/Singleable.php`
- `/sites/expras/doctrine/src/Behavior/Singleable/Mapping/Driver/Annotation.php`
- `/sites/expras/doctrine/src/Behavior/Singleable/Mapping/Driver/Attribute.php`
- `/sites/expras/doctrine/src/Behavior/Singleable/README.md`
- `/sites/expras/doctrine/src/Behavior/Singleable/MIGRATION.md` (this file)

### Updated
- `/sites/expras/doctrine/config/doctrine.php` - Updated listener registration
- `/sites/expras/doctrine/src/ConfigProvider.php` - Updated import statement

### Deleted
- `/sites/expras/doctrine/src/Behavior/Loneliable/` (entire directory)

## Behavior Changes

The core behavior logic remains the same, but with these improvements:

1. **More reliable**: Fixed SQL bug that could affect all entities
2. **Cross-platform**: Works with all Doctrine-supported databases
3. **Better performance**: Optimized refresh calls and query execution
4. **Clearer code**: Better naming and documentation
5. **New feature - ensureFilters**: Added `ensureFilters` parameter to control which entities can automatically receive the exclusive value during fallback logic

## New Features

### ensureFilters Parameter

A new optional parameter `ensureFilters` has been added to the Singleable annotation. This allows you to specify additional filters that only apply when the behavior automatically assigns the exclusive value to a fallback entity.

**Use Case**: When a user deletes their default address, you want the system to only consider **active** addresses as potential replacements.

```php
#[Singleable(
    value: true,
    cancelValue: false,
    group: ['userId'],
    ensureFilters: ['active' => true]  // Only active entities can be auto-selected
)]
private bool $isDefault = false;
```

**Difference from `filters`**:
- `filters`: Applied when canceling other entities AND during fallback logic
- `ensureFilters`: Applied ONLY during fallback logic (merged with `filters`)

This provides fine-grained control over the automatic fallback behavior.

## Testing Recommendations

After migration:

1. Test entity creation with Singleable fields
2. Test entity updates that change Singleable field values
3. Test entity deletion where the entity has the exclusive value
4. Verify grouping works correctly (per user, per category, etc.)
5. Test with your specific database platform (MySQL, PostgreSQL, etc.)

## Support

For questions or issues, refer to:
- `/sites/expras/doctrine/src/Behavior/Singleable/README.md` - Comprehensive usage guide
- Examples in README.md for common scenarios

