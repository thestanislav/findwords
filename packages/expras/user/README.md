# ExprAs User Module

User authentication and authorization module for ExprAs framework with RBAC integration, activity tracking, and remember-me functionality.

## Features

- **Multi-adapter Authentication Chain**: Doctrine (credentials), Session, and Remember-Me adapters
- **User Activity Tracking**: Track last login and activity timestamps
- **RBAC Integration**: Role-based access control with hierarchical roles
- **Remember Me**: Secure persistent login sessions
- **User Profiles**: Extended user information with avatar support
- **Owner Awareness**: Automatic entity ownership tracking

## Installation

Configure the module in your application config:

```php
// config/autoload/user.php or src/App/config/user.php
return [
    'expras-user' => [
        'entity_name' => \ExprAs\User\Entity\User::class
    ]
];
```

## Entity Structure

### User Entity

The `User` entity extends `UserSuper` with the following core fields:

- `username` - unique username (string, max 128 chars)
- `email` - unique email address (string, max 128 chars)
- `password` - bcrypt hashed password
- `displayName` - optional display name (string, max 50 chars)
- `active` - account status (boolean)
- `rbacRoles` - collection of RBAC roles
- `profile` - one-to-one relation with Profile entity
- `lastLoginAt` - timestamp of last credential-based login (nullable)
- `lastActivityAt` - timestamp of last authenticated request (nullable)

### Profile Entity

Extended user information:

- `name` - first name
- `surname` - last name
- `url` - profile URL
- `avatar` - uploaded avatar image

## Authentication

### Login Flow

Authentication uses a chain of adapters executed in priority order:

1. **DoctrineAdapter**: Validates credentials (username/email + password)
2. **SessionAdapter**: Checks for existing session
3. **RememberMeAdapter**: Validates remember-me cookie

```php
// Login request with credentials
POST /login
{
    "identity": "user@example.com",  // or username
    "credential": "password123",
    "remember_me": 1                 // optional
}
```

### Authentication Configuration

```php
// config/autoload/authentication.php
return [
    'authentication' => [
        'redirect' => '',
        'username' => 'identity',
        'password' => 'credential',
        'adapters' => [
            \ExprAs\User\MezzioAuthentication\DoctrineAdapter::class,
            \ExprAs\User\MezzioAuthentication\SessionAdapter::class,
            \ExprAs\User\MezzioAuthentication\RememberMeAdapter::class
        ],
        'remember_me' => [
            'cookie_expire' => 2_592_000,  // 30 days
            'cookie_domain' => null,
            'cookie_name' => 'remember_me'
        ]
    ]
];
```

### Password Hashing

Passwords are automatically hashed using bcrypt:

```php
// Password is automatically hashed on create/update
$user->setPassword('plaintext_password');
// Stored as: $2y$10$...

// Password cost is automatically updated if PHP settings change
```

## User Activity Tracking

The module automatically tracks user login and activity:

- **lastLoginAt**: Updated only when user logs in with credentials (not on session/remember-me)
- **lastActivityAt**: Updated on every authenticated request

```php
// Access timestamps
$user = $request->getAttribute(UserInterface::class);
$lastLogin = $user->getLastLoginAt();        // \DateTimeImmutable|null
$lastActivity = $user->getLastActivityAt();  // \DateTimeImmutable|null

// Check if user logged in recently
if ($lastLogin && $lastLogin > new \DateTimeImmutable('-1 hour')) {
    // User logged in within the last hour
}
```

## RBAC Integration

### Role Management

```php
// Add roles to user
$user->addRbacRoles($role);
$user->addRbacRoles([$role1, $role2]);

// Remove roles
$user->removeRbacRoles($role);

// Get all roles (including inherited from parent roles)
$roles = $user->getRoles();  // Returns array of role names

// Check if user has specific role
if ($user->hasRole('admin')) {
    // User has admin role
}
```

### Accessing Authenticated User

```php
// In middleware/handler
$user = $request->getAttribute(UserInterface::class);

// In templates (using view helper)
$user = $this->identity();
if ($user) {
    echo $user->getDisplayName();
}
```

## Middleware Pipeline

The module registers several middlewares:

```php
// Middleware execution order:
// 1. MezzioAuthenticationMiddleware - authenticates user
// 2. OwnerListenerInjectorMiddleware - injects user into owner listener
// 3. UserActivityTrackerMiddleware - updates lastActivityAt timestamp
// 4. IdentityFactoryMiddleware - provides identity view helper
```

## Owner-Aware Entities

Entities implementing `OwnerAwareInterface` automatically get the current user assigned:

```php
class MyEntity implements OwnerAwareInterface {
    use OwnerAwareTrait;
    
    // Owner is automatically set on persist
}
```

## User Management API

### Create User

```php
POST /admin-panel-path/user
{
    "username": "johndoe",
    "email": "john@example.com",
    "password": "secure_password",
    "displayName": "John Doe",
    "active": true,
    "rbacRoles": [1, 2]  // role IDs
}
```

### Update User

```php
PUT /admin-panel-path/user/{id}
{
    "displayName": "John Smith",
    "password": "new_password"  // optional
}
```
By default admin-panel-path is `.admin`

## Extending the User Entity

Create your own User entity extending UserSuper:

```php
namespace App\Entity;

use ExprAs\User\Entity\UserSuper;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'users')]
#[ORM\Entity]
class User extends UserSuper
{
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $phone = null;
    
    // Add your custom fields and methods
}
```

Update configuration:

```php
return [
    'expras-user' => [
        'entity_name' => \App\Entity\User::class
    ]
];
```

## Security Features

- **BCrypt password hashing** with automatic cost updates
- **Secure remember-me** using token rotation
- **Session-based authentication** with secure storage
- **Active account check** before authentication
- **Username/email uniqueness** validation

## Dependencies

- ExprAs Core
- ExprAs RBAC
- ExprAs Doctrine
- Mezzio Authentication
- Laminas Session

## License

Proprietary - ExprAs Framework

