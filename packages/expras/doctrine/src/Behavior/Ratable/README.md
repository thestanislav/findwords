# Ratable Behavior

The Ratable behavior automatically assigns sequential ratings (rankings) to entities based on specified sorting criteria. When entities are created, updated, or deleted, the behavior recalculates ratings to maintain correct ordering.

## Features

- **Automatic Rating Assignment**: Rates entities based on sorting criteria (e.g., rank users 1, 2, 3... by score)
- **Database-Agnostic**: Uses DQL instead of raw SQL, works with MySQL, PostgreSQL, SQLite, etc.
- **Group-Based Rating**: Create separate rating sets per group (e.g., separate rankings per tournament)
- **Criteria Filtering**: Only rate entities matching specific criteria
- **Priority-Based Execution**: Control execution order when multiple ratable fields exist
- **Default Values**: Assign default ratings to entities not matching criteria
- **Performance Optimized**: Only re-rates affected groups, not entire tables

## Basic Usage

### Simple Rating

```php
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable;

#[ORM\Entity]
class Player
{
    #[ORM\Column(type: "integer")]
    #[Ratable(sort: ["score" => "desc"])]
    protected int $rank = 0;

    #[ORM\Column(type: "integer")]
    protected int $score = 0;
    
    // Getters and setters...
}
```

This will automatically rank players by score in descending order (highest score = rank 1).

### Rating with Criteria

Only rate entities matching specific criteria:

```php
#[ORM\Column(type: "integer", nullable: true)]
#[Ratable(
    sort: ["totalLampsEarned" => "desc"],
    criteria: [["totalLampsEarned", "gt", 0]],
    start: 1,
    default: null
)]
protected ?int $rating = null;
```

This will:
- Only rate users with `totalLampsEarned > 0`
- Start rating from 1
- Set rating to `null` for users with 0 lamps

### Group-Based Rating

Create separate ratings per group:

```php
#[ORM\Column(type: "integer")]
#[Ratable(
    sort: ["points" => "desc", "name" => "asc"],
    group: "tournament"
)]
protected int $position = 0;

#[ORM\ManyToOne(targetEntity: Tournament::class)]
protected Tournament $tournament;
```

This creates separate position rankings for each tournament. Only entities in the same tournament group are re-rated when one changes.

### Multiple Ratable Fields with Priority

```php
// Executed first (priority 2)
#[ORM\Column(type: "integer")]
#[Ratable(sort: ["totalScore" => "desc"], priority: 2)]
protected int $overallRank = 0;

// Executed second (priority 1)
#[ORM\Column(type: "integer")]
#[Ratable(sort: ["weeklyScore" => "desc"], priority: 1)]
protected int $weeklyRank = 0;
```

Higher priority values execute first.

## Using RatableTrait

For convenience, use the provided trait:

```php
use ExprAs\Doctrine\Behavior\Ratable\RatableTrait;
use ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable;

#[ORM\Entity]
class User
{
    use RatableTrait;
    
    // Override the Ratable attribute on the trait's $rating field
    // by redefining it with your criteria
}
```

## Annotation Parameters

### `sort` (required)
Array of field names and directions for rating calculation.

```php
sort: ["score" => "desc", "name" => "asc"]
```

### `start` (optional, default: 1)
Starting rating number.

```php
start: 1  // Rankings: 1, 2, 3, 4...
start: 0  // Rankings: 0, 1, 2, 3...
```

### `group` (optional, default: null)
Field name to group ratings by. Creates separate rating sets per group value.

```php
group: "tournament"  // Separate rankings per tournament
```

### `criteria` (optional, default: [])
Array of filtering criteria. Only entities matching criteria are rated.

Format: `[[field, operator, ...args]]`

```php
criteria: [
    ["active", "eq", true],
    ["score", "gte", 100]
]
```

Valid operators: `eq`, `neq`, `lt`, `lte`, `gt`, `gte`, `in`, `notIn`, `isNull`, `isNotNull`, etc.

### `default` (optional, default: null)
Value for entities not matching criteria.

```php
default: 0     // Assign 0 to non-matching entities
default: null  // Assign null to non-matching entities
```

### `priority` (optional, default: 1)
Execution priority when multiple ratable fields exist. Higher values execute first.

```php
priority: 2  // Executes before priority: 1
```

## How It Works

1. **Entity Changes**: When an entity with a Ratable field is created, updated, or deleted, the listener queues a rating recalculation.

2. **Dependency Tracking**: The listener tracks which fields affect each ratable field (sort fields, criteria fields, group fields). Only queues recalculation if those fields change.

3. **Group-Based Queue**: Queue key includes entity class, field name, and group value. This ensures only affected groups are re-rated.

4. **Post-Flush Execution**: After Doctrine flushes changes, the listener:
   - Fetches entities matching criteria, in correct sort order
   - Assigns sequential ratings starting from `start` value
   - Persists and flushes changes

## Performance Considerations

- **Group Optimization**: When using groups, only entities in the affected group are re-rated
- **Dependency Tracking**: Only re-rates when dependent fields change
- **Batch Updates**: All rating changes are batched in a single flush
- **Deletion Handling**: Skips entities scheduled for deletion

## Example: User Rating System

```php
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable;
use ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Annotation\Aggregatable;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected int $id;

    #[ORM\Column(type: "string")]
    protected string $username;

    // Aggregated from transactions
    #[ORM\Column(type: "integer", options: ["default" => 0])]
    #[Aggregatable(
        function: "sum",
        collection: "lampTransactions",
        aggregateField: "amount"
    )]
    protected int $totalLampsEarned = 0;

    // Automatically rated based on totalLampsEarned
    #[ORM\Column(type: "integer", nullable: true)]
    #[Ratable(
        sort: ["totalLampsEarned" => "desc"],
        criteria: [["totalLampsEarned", "gt", 0]],
        start: 1,
        default: null
    )]
    protected ?int $rating = null;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: LampTransaction::class)]
    protected Collection $lampTransactions;

    // Getters and setters...
}
```

In this example:
1. User earns lamps via transactions
2. `Aggregatable` behavior auto-updates `totalLampsEarned`
3. `Ratable` behavior auto-updates `rating` based on lamp totals
4. Only users with lamps > 0 get a rating (others get null)
5. Highest lamp count = rating 1

## Refactoring Changes

### Previous Issues (Fixed)

1. **MySQL-Only SQL**: Used `SET @rating := ...` variables that only work in MySQL
2. **SQL Injection Risk**: String concatenation without proper escaping
3. **Performance**: Re-rated entire table even when 1 entity changed
4. **No Validation**: Didn't check if fields exist or have correct types
5. **Complex Logic**: Hard-to-understand nested array criteria handling
6. **No Documentation**: Missing docblocks and usage examples

### New Implementation

1. **Database-Agnostic**: Uses DQL, works on all database engines
2. **Secure**: No raw SQL concatenation, uses parameterized queries
3. **Performant**: Group-based queue only re-rates affected entities
4. **Validated**: Checks field existence and types, throws descriptive errors
5. **Clean Code**: Simplified criteria handling, extracted helper methods
6. **Well-Documented**: Comprehensive docblocks and usage examples
7. **Added Features**: Priority parameter, default value, better error handling

## Testing

The behavior is automatically registered in the Doctrine event manager. To test:

1. Enable Ratable on an entity field
2. Create/update/delete entities that affect the rating
3. Verify ratings are assigned correctly

Example test scenario:
- Create 3 users with scores 100, 200, 150
- Verify ratings: User(200) = 1, User(150) = 2, User(100) = 3
- Update User(100) score to 250
- Verify ratings recalculated: User(250) = 1, User(200) = 2, User(150) = 3

