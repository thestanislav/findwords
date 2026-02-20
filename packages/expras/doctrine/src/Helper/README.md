# Doctrine Helpers

## QueryBuilderFilterHelper

A flexible utility for applying filters to Doctrine QueryBuilder instances. Supports nested relations, OR expressions, and all Doctrine Expr operators with automatic parameter binding.

### Features

- **Multiple filter formats** with auto-normalization
- **Nested relations** with automatic JOIN creation
- **OR expressions** via `__or` field suffix
- **All Doctrine Expr operators** via reflection
- **Special operators** with custom logic (like, in, regexp, etc.)
- **Type-safe parameter binding**

### Basic Usage

```php
use ExprAs\Doctrine\Helper\QueryBuilderFilterHelper;

$helper = new QueryBuilderFilterHelper($entityManager);

$qb = $entityManager->createQueryBuilder()
    ->select('u')
    ->from(User::class, 'u');

// Simple equality
$filters = ['status' => 'active'];
$helper->applyFilters($qb, $filters, 'u');

$users = $qb->getQuery()->getResult();
```

### Filter Formats

#### 1. Simple Equality

```php
$filters = [
    'status' => 'active',
    'age' => 25
];
// Converts to: WHERE u.status = :status_0 AND u.age = :age_1
```

#### 2. IN Operator (List of Values)

```php
$filters = [
    'status' => ['active', 'pending', 'approved']
];
// Converts to: WHERE u.status IN (:status_0)
```

#### 3. Explicit Operator

```php
$filters = [
    'age' => ['op' => 'gte', 'args' => [18]]
];
// Result: WHERE u.age >= :age_0
```

#### 4. Multiple Conditions on Same Field

```php
$filters = [
    'age' => [
        ['op' => 'gte', 'args' => [18]],
        ['op' => 'lte', 'args' => [65]]
    ]
];
// Result: WHERE u.age >= :age_0 AND u.age <= :age_1
```

#### 5. Multi-Argument Operators

```php
$filters = [
    'age' => ['op' => 'between', 'args' => [18, 65]]
];
// Result: WHERE u.age BETWEEN :age_0 AND :age_1
```

#### 6. OR Expressions

Use the `__or` suffix on field names:

```php
$filters = [
    'status' => 'active',
    'email__or' => ['op' => 'like', 'args' => ['@gmail.com']]
];
// Result: WHERE u.status = :status_0 OR u.email LIKE :email_1
```

#### 7. Nested Relations

Use dot notation for related entities:

```php
$filters = [
    'category.name' => 'Electronics',
    'category.parent.id' => 5
];
// Creates JOINs automatically:
// LEFT JOIN u.category u_category
// LEFT JOIN u_category.parent u_category_parent
// WHERE u_category.name = :u_category_name_0 
//   AND u_category_parent.id = :u_category_parent_id_1
```

### Supported Operators

#### Standard Comparison Operators

All standard Doctrine `Expr` methods are supported:

- `eq` - Equal
- `neq` - Not equal
- `gt` - Greater than
- `gte` - Greater than or equal
- `lt` - Less than
- `lte` - Less than or equal
- `between` - Between two values
- `in` - In array
- `notIn` - Not in array (also `nin`)

Example:
```php
['price' => ['op' => 'gt', 'args' => [100]]]
['status' => ['op' => 'neq', 'args' => ['deleted']]]
```

#### String Operators

- `like` - Contains (auto-wraps with `%value%`)
- `notLike` - Does not contain (auto-wraps with `%value%`)
- `starts` - Starts with (uses SUBSTRING)
- `ends` - Ends with (uses SUBSTRING)

Example:
```php
['email' => ['op' => 'like', 'args' => ['gmail']]]
// Result: WHERE email LIKE '%gmail%'

['name' => ['op' => 'starts', 'args' => ['John']]]
// Result: WHERE SUBSTRING(name, 1, 4) = 'John'
```

#### Null Checks

- `is` - Check null/not null status

Example:
```php
['deletedAt' => ['op' => 'is', 'args' => ['null']]]
// Result: WHERE deletedAt IS NULL

['deletedAt' => ['op' => 'is', 'args' => ['notnull']]]
// Result: WHERE deletedAt IS NOT NULL
```

#### Regular Expressions

- `regexp` or `regex` - Matches regex
- `notregex`, `neregexp`, or `neregex` - Does not match regex

Example:
```php
['phone' => ['op' => 'regexp', 'args' => ['^\\+1']]]
// Result: WHERE REGEXP(phone, :phone_0) = 1
```

#### Collection Membership

- `member` - Check if value is member of collection

Example:
```php
['roles' => ['op' => 'member', 'args' => ['ROLE_ADMIN']]]
// Result: WHERE :roles_0 MEMBER OF roles
```

### Complex Example

```php
use ExprAs\Doctrine\Helper\QueryBuilderFilterHelper;

$helper = new QueryBuilderFilterHelper($entityManager);

$qb = $entityManager->createQueryBuilder()
    ->select('p')
    ->from(Product::class, 'p');

$filters = [
    // Simple equality
    'status' => 'active',
    
    // Range condition
    'price' => [
        ['op' => 'gte', 'args' => [50]],
        ['op' => 'lte', 'args' => [500]]
    ],
    
    // Nested relation
    'category.name' => 'Electronics',
    'category.parent.slug' => 'tech',
    
    // String search with OR
    'name__or' => ['op' => 'like', 'args' => ['laptop']],
    'description__or' => ['op' => 'like', 'args' => ['laptop']],
    
    // IN operator
    'brand.id' => [1, 5, 10, 15],
    
    // Null check
    'deletedAt' => ['op' => 'is', 'args' => ['null']],
];

$helper->applyFilters($qb, $filters, 'p');

// Add ordering, limit, etc.
$qb->orderBy('p.price', 'ASC')
   ->setMaxResults(20);

$products = $qb->getQuery()->getResult();
```

### Usage in Doctrine Event Listeners

The helper is particularly useful in Doctrine event listeners where you need to build filtered queries dynamically:

```php
use ExprAs\Doctrine\Helper\QueryBuilderFilterHelper;
use Doctrine\ORM\EntityManagerInterface;

class MyListener
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly QueryBuilderFilterHelper $filterHelper
    ) {}
    
    public function calculateAggregate($entity, array $filters): float
    {
        $qb = $this->em->createQueryBuilder()
            ->select('SUM(t.amount)')
            ->from(Transaction::class, 't')
            ->where('t.user = :user')
            ->setParameter('user', $entity);
        
        // Apply dynamic filters
        $this->filterHelper->applyFilters($qb, $filters, 't');
        
        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}
```

### Integration with Aggregatable Behavior

```php
use ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Annotation\Aggregatable;

#[Aggregatable(
    function: "sum",
    collection: "transactions",
    aggregateField: "amount",
    filters: [
        'type' => 'credit',
        'status' => ['approved', 'completed'],
        'category.name' => 'Sales'
    ]
)]
protected float $totalCredits = 0;
```

The QueryBuilderFilterHelper will automatically handle the normalization and application of these filters.

### Performance Considerations

1. **JOIN Caching**: The helper caches created JOINs to avoid duplicates
2. **Parameter Binding**: Uses prepared statements for all values
3. **Lazy DQL Generation**: Only generates DQL when needed
4. **Efficient Normalization**: Minimal overhead for filter normalization

### Error Handling

The helper gracefully handles:
- Invalid operators (silently skipped)
- Missing arguments (uses defaults)
- Invalid field paths (Doctrine will throw exception on query execution)

For production use, consider wrapping in try-catch blocks:

```php
try {
    $helper->applyFilters($qb, $filters, 'u');
    $results = $qb->getQuery()->getResult();
} catch (\Doctrine\ORM\Query\QueryException $e) {
    // Handle invalid field paths or DQL errors
    error_log("Query error: " . $e->getMessage());
}
```

### Tips and Best Practices

1. **Always specify the alias parameter** if using custom aliases
2. **Use explicit operators** for complex queries (more readable)
3. **Test nested relations** to ensure proper JOIN creation
4. **Combine with Doctrine filters** for global entity filtering
5. **Use `__or` suffix sparingly** - consider subqueries for complex OR logic
6. **Cache QueryBuilder instances** when applying same filters repeatedly

### Thread Safety

Each call to `applyFilters()` resets the internal state (params, counter, join cache), making the helper safe to reuse across multiple queries within the same request.

```php
// Safe to reuse
$helper->applyFilters($qb1, $filters1, 'u');
$helper->applyFilters($qb2, $filters2, 'p'); // Fresh state
```

