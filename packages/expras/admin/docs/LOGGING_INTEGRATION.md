# Admin API Logging Integration

This document explains how admin API requests are automatically logged.

## Architecture

```
Admin API Request
       ↓
AdminAuthenticationMiddleware (priority 5000)
├── Authenticates user
└── Attaches user to request: $request->withAttribute(UserInterface::class, $user)
       ↓
AdminApiLoggingMiddleware (priority -5000)
├── Wraps handler call
├── Captures start time
├── Calls handler
├── Captures response/error
├── Extracts ALL context:
│   ├── user (from request attribute)
│   ├── resource, action (from request attributes)
│   ├── httpMethod, requestUri, requestData
│   ├── entityId, ipAddress
│   └── responseStatus, duration
└── Logs to admin.api.logger with full context
       ↓
JsonServerRestApiHandler
├── Processes request
└── Executes action (create, update, delete, list)
       ↓
AdminDoctrineHandler (Monolog handler)
├── Receives log record with full context
├── Maps context values → AdminRequestLogEntity fields
└── Saves to expras_admin_request_logs table
```

---

## Middleware Configuration

**File:** `config/middleware.php`

```php
'post_pipe_routing_middleware' => [
    [
        'priority' => 5000,
        'middleware' => AdminAuthenticationMiddleware::class,
        'path' => '/.admin'
    ],
    [
        'priority' => 4900,  // After auth, before handler
        'middleware' => AdminApiLoggingMiddleware::class,
        'path' => '/.admin/api'  // Only API requests
    ]
],
```

**Execution Order:**
1. **AdminAuthenticationMiddleware** (5000) - Authenticates user
2. **AdminApiLoggingMiddleware** (4900) - Logs the request
3. **JsonServerRestApiHandler** - Processes the action

---

## What Gets Logged

### **Successful Request**

```
Level: INFO
Message: "POST users.create [200] - 45ms"
Context: {
    user: UserSuper{...},
    resource: "users",
    action: "create",
    httpMethod: "POST",
    requestUri: "/.admin/api/users",
    requestData: {...},
    responseStatus: 200,
    entityId: "123",
    ipAddress: "192.168.1.1",
    duration: 0.045
}
```

### **Failed Request**

```
Level: ERROR
Message: "POST users.create [ERROR] - 120ms"
Context: {
    user: UserSuper{...},
    resource: "users",
    action: "create",
    httpMethod: "POST",
    requestUri: "/.admin/api/users",
    requestData: {...},
    error: "Validation failed",
    errorFile: "/path/to/file.php",
    errorLine: 123,
    duration: 0.120
}
```

---

## Logged Actions

All admin REST API actions are automatically logged:

| Action | Method | Description |
|--------|--------|-------------|
| `getList` | GET | List resources with filters/pagination |
| `getOne` | GET | Get single resource by ID |
| `create` | POST | Create new resource |
| `update` | PUT/PATCH | Update existing resource |
| `delete` | DELETE | Delete resource |
| Custom actions | ANY | Any custom action in resource config |

---

## Performance Tracking

The middleware tracks execution time:

```php
$startTime = microtime(true);
$response = $handler->handle($request);
$duration = microtime(true) - $startTime;

// Logged as: "POST users.create [200] - 45ms"
```

**Use cases:**
- Identify slow API endpoints
- Monitor performance degradation
- Optimize heavy operations

---

## Filtering in Admin Interface

The admin log view allows filtering by:
- **Level**: Debug, Info, Warning, Error
- **Resource**: Which entity (users, posts, etc.)
- **Action**: Which operation (create, update, delete)
- **User**: Which admin user

**Example queries:**
- "Show all delete actions by user 'admin'"
- "Show all failed requests (errors) in last 7 days"
- "Show all operations on 'users' resource"

---

## Customization

### Skip Logging for Specific Resources

Modify the middleware to skip certain resources:

```php
class AdminApiLoggingMiddleware
{
    private function logRequest(...): void
    {
        // Skip logging for certain resources
        if (in_array($resource, ['health', 'ping'])) {
            return;
        }
        
        // Continue logging...
    }
}
```

### Log Only Specific Actions

```php
// Log only mutations (create, update, delete)
if (!in_array($action, ['create', 'update', 'delete'])) {
    return;
}
```

### Add Custom Context

```php
$context = [
    'duration' => $duration,
    'userAgent' => $request->getHeaderLine('User-Agent'),
    'referer' => $request->getHeaderLine('Referer'),
];
```

---

## Log Levels

- **INFO**: Successful operations (200-299 status codes)
- **ERROR**: Failed operations (exceptions thrown)
- **WARNING**: Could be added for 4xx errors
- **DEBUG**: Could be added for development debugging

---

## Example Log Entries

```
2025-10-10 14:30:15 [INFO] POST users.create [201] - 45ms
  user: admin
  resource: users
  action: create
  entityId: 123
  requestData: {username: "john", email: "john@example.com"}

2025-10-10 14:31:22 [INFO] PUT users.update [200] - 32ms
  user: admin
  resource: users  
  action: update
  entityId: 123
  requestData: {email: "newemail@example.com"}

2025-10-10 14:32:10 [ERROR] DELETE posts.delete [ERROR] - 120ms
  user: editor
  resource: posts
  action: delete
  entityId: 456
  error: "Cannot delete published post"
```

---

## Benefits

✅ **Automatic**: No code changes in handlers needed  
✅ **Audit Trail**: Complete history of all admin actions  
✅ **Security**: Track who did what and when  
✅ **Performance**: Monitor slow operations  
✅ **Debugging**: Full context for troubleshooting  
✅ **Compliance**: Meet audit requirements  

---

## Related

- [Admin Logger Documentation](LOGGING.md)
- [Logger Architecture](../../logger/docs/EXTENDING.md)

