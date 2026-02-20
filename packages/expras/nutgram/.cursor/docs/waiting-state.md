# Waiting State Functionality

The ExprAs Nutgram package now includes a powerful waiting state system that allows handlers to pause and wait for user text input, making it easy to build surveys, forms, and multi-step conversations.

## Overview

The waiting state system consists of:

1. **User Entity Fields**: New fields to track waiting state and context
2. **UserFallbackDelegator**: Automatically routes text messages to waiting handlers
3. **Handler Methods**: Methods that receive and process user input

## How It Works

1. A handler sets a waiting state on the user entity
2. The `UserFallbackDelegator` intercepts all text messages
3. If a user is waiting for input, the message is forwarded to the appropriate handler
4. The handler processes the input and either moves to the next step or completes the process

## User Entity Fields

The User entity now includes these waiting state fields:

```php
/**
 * The handler that should process the next text message
 * Format: "HandlerClass::methodName" or just "HandlerClass" for default method
 */
protected ?string $waitingForHandler = null;

/**
 * Additional context data for the waiting handler
 */
protected ?array $waitingContext = [];

/**
 * Timestamp when the waiting state was set
 */
protected ?\DateTime $waitingSince = null;
```

### Available Methods

```php
// Check if user is waiting for input
$user->isWaitingForInput(): bool

// Set waiting handler
$user->setWaitingForHandler('MyHandler::handleInput');

// Get/set waiting context
$user->setWaitingContext(['step' => 'name', 'type' => 'survey']);
$context = $user->getWaitingContext();

// Note: waitingContext is nullable in the database but will always return an array
// from getWaitingContext() method (empty array if null)

// Add individual context values
$user->addWaitingContext('name', 'John');
$name = $user->getWaitingContextValue('name', 'Unknown');

// Clear waiting state
$user->clearWaitingState();

// Set waiting timestamp
$user->setWaitingSince(new \DateTime());
```

## Nullable Field Handling

The `waitingContext` field is nullable in the database to handle cases where no context data exists. The entity methods automatically handle this:

- **`getWaitingContext()`**: Always returns an array (empty array if null)
- **`setWaitingContext()`**: Accepts nullable array, converts null to empty array
- **`addWaitingContext()`**: Automatically initializes empty array if null
- **`getWaitingContextValue()`**: Safely handles null context

This ensures backward compatibility and prevents "Cannot assign null to property" errors.

## Handler Implementation

### Basic Pattern

```php
class SurveyHandler implements SubscriberInterface
{
    public function subscribeToEvents(Nutgram $bot): void
    {
        $bot->onCommand('survey', fn(Nutgram $bot) => $this->startSurvey($bot));
    }

    public function startSurvey(Nutgram $bot): void
    {
        $user = $this->getCurrentUser($bot);

        $bot->sendMessage('What\'s your name?');

        // Set waiting state
        $user->setWaitingForHandler(SurveyHandler::class . '::handleName');
        $user->setWaitingContext(['step' => 'name', 'type' => 'survey']);
        $user->setWaitingSince(new \DateTime());

        $this->saveUser($user);
    }

    public function handleName(Nutgram $bot, User $user, array $context): void
    {
        $name = $bot->message()->text;

        // Validate input
        if (strlen($name) < 2) {
            $bot->sendMessage('Name must be at least 2 characters long. Please try again.');
            return; // Keep waiting for valid input
        }

        // Save name to context
        $context['name'] = $name;
        $user->setWaitingContext($context);

        $bot->sendMessage("Nice to meet you, $name! What's your age?");

        // Move to next step
        $user->setWaitingForHandler(SurveyHandler::class . '::handleAge');
        $user->setWaitingContext($context);

        $this->saveUser($user);
    }

    public function handleAge(Nutgram $bot, User $user, array $context): void
    {
        $age = $bot->message()->text;

        if (!is_numeric($age) || $age < 13 || $age > 120) {
            $bot->sendMessage('Please enter a valid age between 13 and 120.');
            return; // Keep waiting for valid input
        }

        $context['age'] = (int)$age;

        // Complete survey
        $bot->sendMessage("Thank you for completing the survey!");
        $bot->sendMessage("Summary:\nName: {$context['name']}\nAge: {$context['age']}");

        // Clear waiting state
        $user->clearWaitingState();
        $this->saveUser($user);
    }
}
```

### Handler Method Signature

Waiting state handler methods must have this signature:

```php
public function handleInput(Nutgram $bot, User $user, array $context): void
```

**Parameters:**
- `$bot`: The Nutgram bot instance
- `$user`: The current user entity
- `$context`: The waiting context data

## Configuration

The `UserFallbackDelegator` must be registered in your configuration:

```php
// config/autoload/nutgram.global.php
'delegators' => [
    Nutgram::class => [
        CommandRegistrator::class,
        SubscriberDelegator::class,
        MiddlewaresInjector::class,
        UserFallbackDelegator::class, // Must be last to handle fallback cases
    ],
],
```

## Advanced Usage

### Form Validation

```php
public function handleEmail(Nutgram $bot, User $user, array $context): void
{
    $email = $bot->message()->text;

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $bot->sendMessage('Please enter a valid email address.');
        return; // Keep waiting for valid input
    }

    $context['email'] = $email;
    $user->setWaitingContext($context);

    // Move to next step
    $user->setWaitingForHandler(FormHandler::class . '::handlePhone');
    $user->setWaitingContext($context);

    $this->saveUser($user);
}
```

### Dynamic Context Management

```php
public function handleStep(Nutgram $bot, User $user, array $context): void
{
    $input = $bot->message()->text;
    $currentStep = $context['currentStep'];

    // Save input
    $context['data'][$currentStep] = $input;

    // Find next step
    $steps = ['name', 'email', 'phone'];
    $currentIndex = array_search($currentStep, $steps);
    $nextIndex = $currentIndex + 1;

    if ($nextIndex < count($steps)) {
        // Move to next step
        $nextStep = $steps[$nextIndex];
        $context['currentStep'] = $nextStep;

        $user->setWaitingForHandler(FormHandler::class . '::handleStep');
        $user->setWaitingContext($context);

        $bot->sendMessage("Please enter your $nextStep:");
    } else {
        // Complete form
        $this->completeForm($bot, $user, $context);
    }
}
```

### Timeout Handling

```php
public function checkTimeout(User $user): bool
{
    if (!$user->getWaitingSince()) {
        return false;
    }

    $timeout = new \DateInterval('PT30M'); // 30 minutes
    $expiryTime = $user->getWaitingSince()->add($timeout);

    return new \DateTime() > $expiryTime;
}

public function handleTimeout(User $user): void
{
    $bot->sendMessage('Your session has expired. Please start over.');
    $user->clearWaitingState();
    $this->saveUser($user);
}
```

## Best Practices

### 1. **Always Validate Input**
```php
if (!$this->validateInput($input)) {
    $bot->sendMessage('Invalid input. Please try again.');
    return; // Keep waiting for valid input
}
```

### 2. **Provide Clear Instructions**
```php
$bot->sendMessage('Please enter your name (minimum 2 characters):');
```

### 3. **Handle Cancellation**
```php
$bot->onCommand('cancel', fn(Nutgram $bot) => $this->cancelWaitingState($bot));

public function cancelWaitingState(Nutgram $bot): void
{
    $user = $this->getCurrentUser($bot);
    if ($user && $user->isWaitingForInput()) {
        $user->clearWaitingState();
        $this->saveUser($user);
        $bot->sendMessage('Operation cancelled. You can start over anytime!');
    }
}
```

### 4. **Save State Frequently**
```php
// Save after each step
$user->setWaitingContext($context);
$this->saveUser($user);
```

### 5. **Use Descriptive Context Keys**
```php
$context = [
    'step' => 'email',
    'type' => 'registration',
    'data' => ['name' => 'John'],
    'attempts' => 0
];
```

## Error Handling

The `UserFallbackDelegator` includes built-in error handling:

- **Handler Not Found**: Automatically clears waiting state and notifies user
- **Method Not Found**: Automatically clears waiting state and notifies user
- **Exceptions**: Catches and logs errors, clears waiting state, and notifies user

## Database Schema

You'll need to add these columns to your users table:

```sql
ALTER TABLE expras_nutgram_users
ADD COLUMN waiting_for_handler VARCHAR(255) NULL,
ADD COLUMN waiting_context JSON NULL,
ADD COLUMN waiting_since DATETIME NULL;
```

## Example Use Cases

1. **User Registration**: Collect name, email, phone, etc.
2. **Surveys**: Multi-question surveys with validation
3. **Settings Configuration**: Step-by-step bot configuration
4. **Order Processing**: Collect shipping details, preferences
5. **Support Tickets**: Collect issue description, priority, etc.
6. **Booking Systems**: Collect dates, times, requirements

## Troubleshooting

### Issue: Handler not being called
**Solution**: Ensure `UserFallbackDelegator` is registered last in the delegator chain

### Issue: Waiting state not persisting
**Solution**: Check that your database schema includes the new columns

### Issue: Context data lost
**Solution**: Ensure you're calling `setWaitingContext()` after modifying the context array

### Issue: Multiple handlers responding
**Solution**: Use the `priority` parameter in your `onText` handlers to control execution order

This waiting state system provides a robust foundation for building conversational interfaces that can handle complex multi-step interactions while maintaining clean, maintainable code.
