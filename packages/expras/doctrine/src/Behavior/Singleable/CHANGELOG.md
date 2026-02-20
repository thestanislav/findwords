# Singleable Behavior - Changelog

## Version 2.0 (Current)

### New Features

#### ensureFilters Parameter
Added `ensureFilters` parameter to provide fine-grained control over fallback entity selection.

**Purpose**: Control which entities can automatically receive the exclusive value when the current holder is deleted or updated.

**Syntax**:
```php
#[Singleable(
    value: true,
    cancelValue: false,
    group: ['userId'],
    filters: ['type' => 'shipping'],           // Applied to cancel + fallback
    ensureFilters: ['active' => true]          // Applied only to fallback
)]
private bool $isDefault;
```

**Use Cases**:
1. **Address Management**: Only active addresses can become default
2. **Payment Methods**: Only verified and non-expired cards can be auto-selected as primary
3. **Content Items**: Only published articles can be auto-featured
4. **User Profiles**: Only verified profiles can be auto-promoted

**Implementation Details**:
- `filters` + `ensureFilters` are merged when checking for fallback candidates
- Only `filters` is used when canceling other entities
- Provides backward compatibility (existing code works without changes)

### Bug Fixes & Improvements

#### Fixed Critical SQL Bug
**Before**: `WHERE 1=1 OR id != :id` - Would update ALL entities
**After**: `WHERE (id != :id)` - Correctly excludes only current entity

#### Fixed Query Execution
**Before**: `$query->getOneOrNullResult()` - Fetched but didn't execute UPDATE
**After**: `$query->execute()` - Properly executes UPDATE query

#### Cross-Platform Support
- Uses DQL exclusively (no native SQL)
- Compatible with MySQL, PostgreSQL, SQLite, Oracle, SQL Server

#### Code Quality
- Added comprehensive PHPDoc comments
- Improved method naming
- Added type hints
- Better variable naming
- Optimized refresh calls

### Renamed from Loneliable

**Old Name**: Loneliable (with typo: LonelibaleListener)
**New Name**: Singleable (consistent, clear naming)

## Version 1.0 (Legacy - Loneliable)

### Initial Features
- Basic exclusive value enforcement
- Group-based scoping
- Static filters
- Fallback logic

### Known Issues (Fixed in 2.0)
- SQL logic bug affecting all entities
- Wrong query method for UPDATE
- No control over fallback entity selection
- Typo in class name
- MySQL-specific syntax

---

## Migration Guide

See [MIGRATION.md](./MIGRATION.md) for detailed migration instructions from Loneliable to Singleable.

## Documentation

- [README.md](./README.md) - Comprehensive usage guide
- [MIGRATION.md](./MIGRATION.md) - Migration from Loneliable
- [CHANGELOG.md](./CHANGELOG.md) - This file

## Examples

### Basic Usage
```php
#[Singleable(value: true, cancelValue: false, group: ['userId'])]
private bool $isDefault;
```

### With ensureFilters
```php
#[Singleable(
    value: true,
    cancelValue: false,
    group: ['userId'],
    ensureFilters: ['active' => true, 'verified' => true]
)]
private bool $isPrimary;
```

### With Separate Filters
```php
#[Singleable(
    value: true,
    cancelValue: false,
    group: ['userId'],
    filters: ['type' => 'shipping'],      // Applied when canceling others
    ensureFilters: ['active' => true]     // Applied when selecting fallback
)]
private bool $isDefault;
```

