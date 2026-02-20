# Telegram Authentication Guide

This document explains how Telegram authentication works in the ExprAs Nutgram module, allowing users to authenticate via Telegram bots and link their Telegram accounts to application User entities.

## Overview

The Telegram authentication system provides a secure way to authenticate users through Telegram bots using deep links and token-based authentication. The flow consists of:

1. **Web-to-Telegram Flow**: User clicks "Login with Telegram" on web app → Redirects to Telegram bot with auth token
2. **Token Generation**: A unique authentication token is generated and stored in cache
3. **Bot Command Processing**: User starts the bot with the token → Bot links Telegram user to app User entity
4. **HTTP Authentication**: User can authenticate on web using the same token

## Architecture Components

### 1. TelegramAdapter (`ExprAs\Nutgram\MezzioAuthentication\TelegramAdapter`)

A Mezzio Authentication adapter that authenticates HTTP requests using Telegram authentication tokens.

**Features:**
- Extracts auth tokens from query parameters, headers, or cookies
- Validates tokens using `TelegramAuthService`
- Retrieves linked User entities via `DefaultUser` → `User` relationship
- Checks if user is active before authenticating

**Token Extraction Priority:**
1. Query parameter: `?telegram_auth_token=...`
2. HTTP header: `X-Telegram-Auth-Token: ...`
3. Cookie: `telegram_auth_token=...`

**Usage in Configuration:**
```php
// config/autoload/authentication.global.php
return [
    'authentication' => [
        'adapters' => [
            \ExprAs\Nutgram\MezzioAuthentication\TelegramAdapter::class,
        ],
    ],
];
```

### 2. TelegramAuthService (`ExprAs\Nutgram\Service\TelegramAuthService`)

Service for managing authentication tokens using PSR-16 cache.

**Key Methods:**
- `generateToken()`: Creates a unique 32-character hexadecimal token
- `storeToken(string $token, int $appUserId, int $ttl)`: Stores token with app user ID
- `validateToken(string $token)`: Validates token and returns app user ID
- `getAuthLink(string $token)`: Generates Telegram deep link URL
- `removeToken(string $token)`: Removes token from cache (for single-use tokens)

**Configuration:**
```php
// config/autoload/authentication.global.php or nutgram.global.php
return [
    'nutgram' => [
        'auth' => [
            'token_ttl' => 900, // 15 minutes default
            'bot_username' => 'your_bot_username', // Required for deep links
        ],
    ],
];
```

### 3. TelegramAuthLinkTrait (`ExprAs\Nutgram\Trait\TelegramAuthLinkTrait`)

Injectable trait for Nutgram handlers to link Telegram users to application User entities.

**Features:**
- Automatically checks if Telegram authentication is enabled
- Creates new User entities when linking for the first time
- Links existing User entities to Telegram users
- Stores authentication tokens in cache when provided

**Usage in Handlers:**
```php
use ExprAs\Nutgram\Trait\TelegramAuthLinkTrait;
use ExprAs\Nutgram\SubscriberInterface;
use SergiX44\Nutgram\Nutgram;

class MyHandler implements SubscriberInterface
{
    use TelegramAuthLinkTrait;

    public function subscribeToEvents(Nutgram $bot): void
    {
        $bot->onCommand('start', [$this, 'onStart']);
    }

    public function onStart(Nutgram $bot): void
    {
        $parameters = $bot->getParameters();
        $authToken = $parameters[0] ?? null;
        
        $appUser = $this->linkTelegramUserToAppUser($bot, $authToken);
        
        if ($appUser) {
            $bot->sendMessage("Welcome, {$appUser->getDisplayName()}!");
        }
    }
}
```

### 4. TelegramAuthRedirectHandler (`ExprAs\Nutgram\Mezzio\Handler\TelegramAuthRedirectHandler`)

HTTP handler for generating authentication tokens and redirecting users to Telegram.

**Features:**
- Generates unique authentication token
- Sets secure HttpOnly cookie with token for automatic authentication
- Creates Telegram deep link with token
- Redirects user to Telegram bot

**Cookie Security:**
- `HttpOnly`: Not accessible via JavaScript (prevents XSS attacks)
- `Secure`: Only transmitted over HTTPS
- `SameSite=Lax`: CSRF protection while allowing redirects
- Expiration: Matches token TTL (default 15 minutes)

**Usage:**
Can be registered as a route handler in your application routes configuration.

## Authentication Flow

### Complete Flow Diagram

```
┌─────────────┐
│  Web App    │
│  User clicks│
│  "Login"    │
└──────┬──────┘
       │
       │ GET /auth/telegram
       ▼
┌─────────────────────────────┐
│ TelegramAuthRedirectHandler  │
│ - Generates token            │
│ - Sets cookie with token     │
│ - Creates deep link          │
└──────┬──────────────────────┘
       │
       │ Redirect to Telegram
       ▼
┌──────────────────────────────┐
│ https://t.me/bot?start=TOKEN │
└──────┬───────────────────────┘
       │
       │ User starts bot
       ▼
┌──────────────────────────────┐
│  Telegram Bot                │
│  TelegramAuthHandler         │
│  - Extracts token from /start │
│  - Links Telegram user to    │
│    App User entity           │
│  - Stores token in cache     │
└──────┬───────────────────────┘
       │
       │ Token stored with
       │ app user ID
       ▼
┌──────────────────────────────┐
│  User returns to web app     │
│  - Token in URL or cookie    │
└──────┬───────────────────────┘
       │
       │ HTTP Request with token
       ▼
┌──────────────────────────────┐
│  TelegramAdapter            │
│  - Validates token          │
│  - Gets app user ID         │
│  - Finds User directly      │
│  - Returns User             │
└──────────────────────────────┘
```

### Step-by-Step Flow

1. **User Initiates Login**
   - User visits `/auth/telegram` on web application
   - `TelegramAuthRedirectHandler` generates a unique token
   - Secure cookie is set with the token for automatic authentication
   - User is redirected to `https://t.me/bot_username?start=TOKEN`

2. **Telegram Bot Processing**
   - User starts the bot in Telegram
   - Bot receives `/start TOKEN` command
   - Handler using `TelegramAuthLinkTrait`:
     - Extracts Telegram user from bot context
     - Creates or links User entity to Telegram user
     - Stores token with app user ID in cache
     - Sends welcome message

3. **Web Application Authentication**
   - User returns to web application
   - `TelegramAdapter` automatically extracts token from cookie (or URL/header as fallback)
   - Validates token and gets app user ID directly
   - Finds `User` entity by app user ID
   - Returns `User` entity for authentication
   - Token is removed from cache after successful authentication (single-use)

## Configuration

### Required Configuration

1. **Bot Token and Username**
   ```php
   // config/autoload/authentication.global.php
   return [
       'nutgram' => [
           'token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz', // From @BotFather
           'auth' => [
               'bot_username' => 'your_bot_username', // Without @
               'token_ttl' => 900, // Optional, defaults to 15 minutes
           ],
       ],
   ];
   ```

2. **Authentication Adapter**
   ```php
   // config/autoload/authentication.global.php
   return [
       'authentication' => [
           'adapters' => [
               \ExprAs\Nutgram\MezzioAuthentication\TelegramAdapter::class,
           ],
       ],
   ];
   ```

3. **User Entity Configuration**
   ```php
   // src/App/config/user.php
   return [
       'expras-user' => [
           'entity_name' => \App\Entity\User::class,
       ],
   ];
   ```

4. **Doctrine Entity Setup**
   - Your `User` entity must use `TelegramUserProvider` trait
   - `DefaultUser` entity must use `AppUserProvider` trait
   - `TelegramUserModifierListener` must be registered in Doctrine config

### Optional Configuration

**Token Storage:**
- Uses PSR-16 cache implementation (configured in `expras-core`)
- Default TTL: 900 seconds (15 minutes)
- Tokens can be single-use (delete after validation) or multi-use

**Custom Token TTL:**
```php
'auth' => [
    'token_ttl' => 3600, // 1 hour
],
```

## Entity Relationships

The authentication system relies on bidirectional OneToOne relationships:

- **User** (`App\Entity\User`) ↔ **DefaultUser** (`ExprAs\Nutgram\Entity\DefaultUser`)
  - `User.telegramUser` → `DefaultUser`
  - `DefaultUser.user` → `User`

These relationships are automatically mapped by `TelegramUserModifierListener` when entities use the appropriate traits:
- `User` entities use `TelegramUserProvider` trait
- `DefaultUser` entities use `AppUserProvider` trait

## Security Considerations

1. **Token Expiration**: Tokens expire after TTL (default 15 minutes)
2. **Token Storage**: Tokens are stored in cache, not database
3. **Single-Use Tokens**: Tokens are automatically deleted from cache after first successful authentication
4. **Cookie Security**: Authentication cookies are HttpOnly, Secure, and use SameSite=Lax to prevent XSS and CSRF attacks
5. **HTTPS Required**: Always use HTTPS in production for token transmission
6. **Token Validation**: Tokens are validated on both bot and web sides

## Route Configuration

The `TelegramAuthRedirectHandler` is already registered in the Nutgram module's `ConfigProvider` and can be used directly in your application routes.

### Registering the Route

Add the route to your application's route configuration file:

```php
// src/App/config/routes.php
use ExprAs\Nutgram\Mezzio\Handler\TelegramAuthRedirectHandler;

return [
    'routes' => [
        // ... other routes ...
        [
            'name'            => 'telegram-auth-redirect',
            'path'            => '/auth/telegram',
            'middleware'      => TelegramAuthRedirectHandler::class,
            'allowed_methods' => ['GET'],
            'options'         => [
                'defaults' => [
                    'action' => 'redirect',
                ]
            ]
        ],
    ],
];
```

### Route Parameters

The handler supports an optional `return` query parameter for post-authentication redirect:

```
GET /auth/telegram?return=/dashboard
```

This will redirect the user back to `/dashboard` after successful Telegram authentication (you'll need to implement this redirect logic in your bot handler or frontend).

### Custom Handler Implementation (Optional)

If you need custom behavior (e.g., JSON responses for AJAX requests), you can create your own handler:

```php
// src/App/src/Handler/TelegramAuthHandler.php
namespace App\Handler;

use ExprAs\Nutgram\Service\TelegramAuthService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TelegramAuthHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TelegramAuthService $authService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Generate authentication token
        $token = $this->authService->generateToken();

        // Get return URL from query params
        $queryParams = $request->getQueryParams();
        $returnUrl = $queryParams['return'] ?? '/';

        // Generate Telegram deep link
        $telegramUrl = $this->authService->getAuthLink($token);

        // For AJAX requests, return JSON with URL
        $acceptHeader = $request->getHeaderLine('Accept');
        if (strpos($acceptHeader, 'application/json') !== false) {
            return new JsonResponse([
                'success' => true,
                'telegram_url' => $telegramUrl,
                'token' => $token, // Optional: for testing
                'return_url' => $returnUrl,
            ]);
        }

        // For regular requests, redirect to Telegram
        return new RedirectResponse($telegramUrl);
    }
}
```

Then register it in your `ConfigProvider` and routes:

```php
// src/App/src/ConfigProvider.php
'invokables' => [
    // ... other handlers ...
    Handler\TelegramAuthHandler::class => Handler\TelegramAuthHandler::class,
],

// src/App/config/routes.php
[
    'name'            => 'telegram-auth',
    'path'            => '/auth/telegram',
    'middleware'      => \App\Handler\TelegramAuthHandler::class,
    'allowed_methods' => ['GET'],
],
```

## Troubleshooting

### Token Not Validating

1. Check token TTL hasn't expired
2. Verify token was stored correctly in cache
3. Ensure cache is working (check cache configuration)
4. Verify app user ID is correctly stored and retrieved

### User Not Linking

1. Ensure `TelegramAuthLinkTrait` is used in bot handler
2. Check `isTelegramAuthEnabled()` returns true
3. Verify User entity uses `TelegramUserProvider` trait
4. Ensure `TelegramUserModifierListener` is registered

### Authentication Not Working

1. Verify `TelegramAdapter` is in authentication adapters list
2. Check token extraction (query param, header, or cookie)
3. Ensure User entity is active (`isActive() === true`)
4. Verify DefaultUser → User relationship exists

## Best Practices

1. **Always use HTTPS** in production for secure token transmission
2. **Set appropriate TTL** based on your security requirements
3. **Consider single-use tokens** for higher security
4. **Log authentication events** for audit purposes
5. **Handle errors gracefully** with user-friendly messages
6. **Test both flows** (web-to-telegram and direct token use)

## Related Documentation

- [Configuration Guide](configuration.md)
- [Handlers Guide](handlers-guide.md)
- [Mezzio Authentication Documentation](https://docs.mezzio.dev/mezzio-authentication/)

