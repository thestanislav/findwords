# Admin API Logger Documentation

The Admin module has a dedicated logger (`admin.api.logger`) that stores admin REST API request logs in a database using a custom `AdminRequestLogEntity`.

## Features

- **Dedicated Database Table**: `expras_admin_request_logs` with admin-specific fields
- **Automatic Context Extraction**: AdminContextProcessor automatically adds request data
- **Automatic Field Mapping**: AdminDoctrineHandler maps context values to entity fields
- **Database Logging**: All API actions stored with structured data
- **User Tracking**: ManyToOne relation to UserSuper entity
- **Action Tracking**: Logs resource, action, HTTP method, request data, response status

## How It Works

**Complete Flow:**
1. **API Request**: Admin user performs action (create, update, delete, list)
2. **AdminAuthenticationMiddleware**: Attaches user to request
3. **AdminApiLoggingMiddleware**: Wraps request, extracts all context, logs result
4. **AdminDoctrineHandler**: Maps context to AdminRequestLogEntity
5. **Database**: Record saved with all admin-specific fields

**Context Keys → Entity Fields Mapping:**
- `user` → `$user` (ManyToOne relation, user_id foreign key)
- `resource` → `$resource` (resource column)
- `action` → `$action` (action column)
- `httpMethod` → `$httpMethod` (http_method column)
- `requestUri` → `$requestUri` (request_uri column)
- `requestData` → `$requestData` (request_data JSON column)
- `responseStatus` → `$responseStatus` (response_status column)
- `entityId` → `$entityId` (entity_id column)
- `ipAddress` → `$ipAddress` (ip_address column)

## Configuration

Logger is configured in `config/logger.php`:

```php
'admin.api.logger' => [
    'handlers' => [
        'doctrine' => [
            'name' => 'custom',
            'options' => [
                'service' => AdminDoctrineHandler::class,
            ],
        ],
    ],
    'processors' => [
        'adminContext' => [
            'name' => 'custom',
            'options' => [
                'service' => AdminContextProcessor::class,
            ],
        ],
    ],
],
```

## Usage

### Automatic Logging (Recommended)

All admin API requests are **automatically logged** by `AdminApiLoggingMiddleware`. No code changes needed!

Every request to `/.admin/api/*` is logged with full context:
- User, resource, action
- HTTP method, URI, request data
- Response status, execution time
- IP address, entity ID

### Manual Logging

For custom admin operations outside the API handler:

```php
use Psr\Log\LoggerInterface;

class MyAdminService
{
    public function __construct(
        #[Inject('admin.api.logger')]
        private LoggerInterface $logger
    ) {}
    
    public function doAdminAction()
    {
        $this->logger->info('Custom admin action', [
            'customField' => 'value',
        ]);
    }
}
```

### Automatic Context (via Middleware)

The `AdminApiLoggingMiddleware` automatically adds to context:

```php
$context = [
    'user' => UserSuper,              // User entity object
    'resource' => 'users',            // Resource name
    'action' => 'create',             // Action name  
    'httpMethod' => 'POST',           // HTTP method
    'requestUri' => '/.admin/api/users', // Request URI
    'requestData' => [...],           // Request body
    'entityId' => '123',              // Entity ID
    'ipAddress' => '192.168.1.1',    // Client IP
    'responseStatus' => 201,          // HTTP status
    'duration' => 0.045,              // Execution time
];
```

These are then mapped by `AdminDoctrineHandler` to the corresponding entity fields.

### Manual Context

For custom logging, add your own context:

```php
$this->logger->info('Custom operation', [
    'customField' => 'value',
    'data' => [...],
]);
```

## Database Schema

The `AdminRequestLogEntity` stores:

**From AbstractLogEntity:**
- `id` - Primary key
- `datetime` - Timestamp
- `level` - Log level (int)
- `levelName` - Log level name (INFO, ERROR, etc.)
- `message` - Log message
- `channel` - Logger channel (admin.api)
- `context` - Full context (JSON)
- `extra` - Extra data (JSON)

**Admin-Specific:**
- `user` - ManyToOne relation to UserSuper
- `resource` - Resource name (e.g., 'users', 'posts')
- `action` - Action name (e.g., 'create', 'update', 'delete')
- `httpMethod` - HTTP method (GET, POST, PUT, DELETE)
- `requestUri` - Request URI
- `requestData` - Request body/parameters (JSON)
- `responseStatus` - HTTP response status code
- `entityId` - Entity ID (for CRUD actions)
- `ipAddress` - IP address of requester

## Querying Logs

```php
$repository = $entityManager->getRepository(AdminRequestLogEntity::class);

// Get all actions by a user
$user = $entityManager->find(UserSuper::class, 1);
$logs = $repository->findBy([
    'user' => $user
], ['datetime' => 'DESC'], 100);

// Get all create actions
$logs = $repository->findBy([
    'action' => 'create'
], ['datetime' => 'DESC']);

// Complex query with joins
$qb = $repository->createQueryBuilder('l');
$logs = $qb
    ->select('l', 'u')
    ->leftJoin('l.user', 'u')
    ->where('l.resource = :resource')
    ->andWhere('l.action = :action')
    ->andWhere('l.datetime > :since')
    ->setParameter('resource', 'users')
    ->setParameter('action', 'delete')
    ->setParameter('since', new \DateTime('-30 days'))
    ->orderBy('l.datetime', 'DESC')
    ->getQuery()
    ->getResult();

// Access related data
foreach ($logs as $log) {
    echo $log->getUser()?->getUsername();
    echo $log->getResource();
    echo $log->getAction();
}
```

## Admin Interface

Access admin request logs via the admin panel:
- **Menu**: "Журнал API запросов" (API Request Log)
- **Icon**: AdminPanelSettings

**Features:**
- List view with filters (level, resource, action, user)
- Shows: datetime, user, resource, action, HTTP method, URI, entity ID
- Color-coded rows (errors in red)
- Truncate action to clear old logs
- User reference clickable (navigate to user details)

## Use Cases

### Audit Trail

Track who did what and when:

```php
// Automatically logged on every admin API action
// User 'john' created product #123
// User 'admin' deleted user #456
// User 'jane' updated post #789
```

### Security Monitoring

Monitor suspicious activities:

```php
// Filter by action: 'delete'
// Filter by user: specific admin
// Filter by resource: sensitive resources
```

### Troubleshooting

Debug API issues:

```php
// See full request data
$log->getRequestData();  // ['field' => 'value', ...]

// See response status
$log->getResponseStatus();  // 500

// See what went wrong
$log->getMessage();  // Error message
$log->getContext();  // Full context
```

## Configuration

### Configurable User Entity

The `AdminRequestLogEntity` uses a ManyToOne relation to the user entity. By default, it references `UserSuper::class`, but you can configure it:

```php
// config/admin.php
return [
    'exprass_admin' => [
        'userEntity' => App\Entity\CustomUser::class,  // Custom user entity
    ],
];
```

The `AdminLogEntityModifierListener` dynamically updates the relation target based on configuration.

**Fallback Order:**
1. `config['exprass_admin']['userEntity']` - Admin-specific config
2. `config['user']['entity']` - Global user config
3. `UserSuper::class` - Default

## Migration

Generate and run migration for the `expras_admin_request_logs` table:

```bash
vendor/bin/doctrine-module migrations:diff
vendor/bin/doctrine-module migrations:migrate
```

**Important**: The migration will create a foreign key to the users table. The target table is determined by the configured user entity.

## Best Practices

1. **Log Important Actions**: Create, update, delete operations
2. **Don't Log Sensitive Data**: Passwords, tokens, credit card numbers
3. **Use Appropriate Levels**:
   - `Info`: Normal CRUD operations
   - `Warning`: Unusual but valid actions
   - `Error`: Failed operations
4. **Keep Request Data Clean**: Filter out unnecessary fields before logging
5. **Set Retention Policy**: Truncate old logs periodically

## Related

- [ExprAs Logger Module](../../logger/README.md)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)

