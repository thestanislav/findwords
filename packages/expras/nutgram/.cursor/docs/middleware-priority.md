# Middleware Priority and Dependencies

## Overview

The ExprAs\Nutgram package uses a priority-based middleware system where **execution order is critical** for proper functionality. This document explains why the priority order matters and how to avoid common pitfalls.

## Critical Priority Order

```php
'middlewares' => [
    [
        'middleware' => UserEntityInjectorMiddleware::class,
        'priority'   => PHP_INT_MAX, // Highest priority - executes FIRST
    ],
    [
        'middleware' => ChatEntityInjectorMiddleware::class,
        'priority'   => PHP_INT_MAX - 1, // Second priority
    ],
    [
        'middleware' => UserMessageListenerMiddleware::class,
        'priority'   => PHP_INT_MAX - 2, // Third priority - depends on user entity
    ],
]
```

## Why Priority Order Matters

### 1. UserEntityInjectorMiddleware (Priority: PHP_INT_MAX)
- **Purpose**: Creates and injects user entities into the bot context
- **Dependencies**: None - this is the foundation middleware
- **What it does**: 
  - Fetches user from Telegram update
  - Creates or retrieves user entity from database
  - Injects user entity into bot context via `$bot->set(User::class, $userEntity)`

### 2. ChatEntityInjectorMiddleware (Priority: PHP_INT_MAX - 1)
- **Purpose**: Creates and injects chat entities into the bot context
- **Dependencies**: None - works independently
- **What it does**:
  - Fetches chat from Telegram update
  - Creates or retrieves chat entity from database
  - Injects chat entity into bot context via `$bot->set(Chat::class, $chatEntity)`

### 3. UserMessageListenerMiddleware (Priority: PHP_INT_MAX - 2)
- **Purpose**: Logs user messages and interactions
- **Dependencies**: **REQUIRES UserEntityInjectorMiddleware to execute first**
- **What it does**:
  - Retrieves user entity via `$bot->get(User::class)`
  - Creates UserMessage records with user association
  - **Will fail if user entity is not available**

## Dependency Chain

```
Telegram Update
    ↓
UserEntityInjectorMiddleware (FIRST)
    ↓ User entity available in bot context
ChatEntityInjectorMiddleware (SECOND)  
    ↓ Chat entity available in bot context
UserMessageListenerMiddleware (THIRD)
    ↓ Can access both user and chat entities
Command Handlers
    ↓ Can access all injected entities
```

## What Happens If Order Is Wrong

### Scenario 1: UserMessageListenerMiddleware Executes First
```php
// This will FAIL because user entity hasn't been injected yet
$user = $bot->get(User::class); // Returns null
$userMessage->setUser($user);    // Error: Cannot set null user
```

### Scenario 2: Middlewares Execute in Random Order
- Some updates might work, others might fail
- Inconsistent behavior across different message types
- Silent failures that are hard to debug

## How Priority System Works

The `MiddlewaresInjector` delegator uses `SplPriorityQueue` to ensure proper ordering:

```php
// Higher priority numbers execute first
$queue->insert($item, [$priority, $serial]);

// PHP_INT_MAX = highest priority (executes first)
// PHP_INT_MAX - 1 = second priority
// PHP_INT_MAX - 2 = third priority
```

## Adding Custom Middlewares

When adding custom middlewares, consider their dependencies:

```php
'middlewares' => [
    // Core middlewares (never change order)
    [
        'middleware' => UserEntityInjectorMiddleware::class,
        'priority'   => PHP_INT_MAX,
    ],
    [
        'middleware' => ChatEntityInjectorMiddleware::class,
        'priority'   => PHP_INT_MAX - 1,
    ],
    [
        'middleware' => UserMessageListenerMiddleware::class,
        'priority'   => PHP_INT_MAX - 2,
    ],
    
    // Custom middlewares (add after core)
    [
        'middleware' => CustomLoggingMiddleware::class,
        'priority'   => PHP_INT_MAX - 10, // Lower priority
    ],
    [
        'middleware' => AnalyticsMiddleware::class,
        'priority'   => PHP_INT_MAX - 20, // Even lower priority
    ],
]
```

## Testing Middleware Order

To verify middleware order is correct:

```bash
# Check if user entity is available in UserMessageListenerMiddleware
vendor/bin/mezzio-sf-console nutgram:hook:info

# Send a message to your bot and check logs
# UserMessageListenerMiddleware should not throw errors about missing user entity
```

## Common Mistakes

### ❌ Don't Do This
```php
'middlewares' => [
    [
        'middleware' => UserMessageListenerMiddleware::class, // WRONG! Too early
        'priority'   => PHP_INT_MAX,
    ],
    [
        'middleware' => UserEntityInjectorMiddleware::class, // WRONG! Too late
        'priority'   => PHP_INT_MAX - 2,
    ],
]
```

### ✅ Do This Instead
```php
'middlewares' => [
    [
        'middleware' => UserEntityInjectorMiddleware::class, // Correct: First
        'priority'   => PHP_INT_MAX,
    ],
    [
        'middleware' => UserMessageListenerMiddleware::class, // Correct: After user injection
        'priority'   => PHP_INT_MAX - 2,
    ],
]
```

## Summary

**Never change the core middleware priority order.** The current system is designed to ensure proper dependency injection and prevent runtime errors. If you need to add custom middlewares, place them after the core middlewares with lower priorities.

Remember: **UserEntityInjectorMiddleware must always execute first** as it's the foundation that other middlewares depend on.
