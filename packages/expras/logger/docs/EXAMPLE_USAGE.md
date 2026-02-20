# LoggerProviderTrait - Usage Examples

This document provides practical examples of using `LoggerProviderTrait` for automatic logger injection.

## Example 1: Simple Service (No Dependencies)

```php
<?php

namespace App\Service;

use ExprAs\Logger\Provider\LoggerProviderTrait;

/**
 * Simple notification service that uses automatic logger injection
 */
class NotificationService
{
    use LoggerProviderTrait;
    
    public function sendEmail(string $to, string $subject, string $body): void
    {
        $this->logger->info('Sending email', [
            'to' => $to,
            'subject' => $subject,
        ]);
        
        try {
            // Send email logic here
            mail($to, $subject, $body);
            
            $this->logger->info('Email sent successfully', ['to' => $to]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
```

**Usage:**
```php
$service = $container->get(NotificationService::class);
$service->sendEmail('user@example.com', 'Welcome', 'Hello!');
```

## Example 2: Service with Dependencies (ConfigAbstractFactory)

```php
<?php

namespace App\Service;

use ExprAs\Logger\Provider\LoggerProviderTrait;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;

/**
 * User service with dependencies and automatic logger injection
 */
class UserService
{
    use LoggerProviderTrait;
    
    public function __construct(
        private EntityManager $entityManager,
        private UserRepository $userRepository
    ) {}
    
    public function createUser(array $data): User
    {
        $this->logger->info('Creating new user', ['email' => $data['email']]);
        
        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $this->logger->info('User created successfully', [
            'userId' => $user->getId(),
            'email' => $user->getEmail(),
        ]);
        
        return $user;
    }
    
    public function deleteUser(int $userId): void
    {
        $this->logger->warning('Deleting user', ['userId' => $userId]);
        
        $user = $this->userRepository->find($userId);
        if (!$user) {
            $this->logger->error('User not found for deletion', ['userId' => $userId]);
            throw new \Exception('User not found');
        }
        
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        $this->logger->info('User deleted', ['userId' => $userId]);
    }
}
```

**Configuration (config/autoload/dependencies.php):**
```php
<?php

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;

return [
    ConfigAbstractFactory::class => [
        UserService::class => [
            EntityManager::class,
            UserRepository::class,
        ],
    ],
];
```

**Usage:**
```php
$userService = $container->get(UserService::class);
$user = $userService->createUser([
    'email' => 'john@example.com',
    'name' => 'John Doe',
]);
```

## Example 3: Telegram Bot Command

```php
<?php

namespace Bot\Command;

use ExprAs\Logger\Provider\LoggerProviderTrait;
use SergiX44\Nutgram\Nutgram;

/**
 * Telegram bot command with automatic logger injection
 */
class StartCommand
{
    use LoggerProviderTrait;
    
    public function __invoke(Nutgram $bot): void
    {
        $userId = $bot->userId();
        $username = $bot->user()->username;
        
        $this->logger->info('User started bot', [
            'userId' => $userId,
            'username' => $username,
        ]);
        
        $bot->sendMessage('Welcome to the bot!');
    }
}
```

## Example 4: Doctrine Event Listener

```php
<?php

namespace App\Listener;

use ExprAs\Logger\LoggerProviderTrait;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use App\Entity\User;

/**
 * Doctrine listener with automatic logger injection
 */
class UserActivityListener
{
    use LoggerProviderTrait;
    
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof User) {
            $this->logger->info('New user created in database', [
                'userId' => $entity->getId(),
                'email' => $entity->getEmail(),
            ]);
        }
    }
    
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof User) {
            $this->logger->warning('User being removed from database', [
                'userId' => $entity->getId(),
                'email' => $entity->getEmail(),
            ]);
        }
    }
}
```

**Configuration (config/autoload/doctrine.php):**
```php
return [
    'doctrine' => [
        'event_manager' => [
            'orm_default' => [
                'subscribers' => [
                    UserActivityListener::class,
                ],
            ],
        ],
    ],
];
```

## Example 5: API Handler/Middleware

```php
<?php

namespace App\Handler;

use ExprAs\Logger\Provider\LoggerProviderTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * API handler with automatic logger injection
 */
class UserApiHandler implements RequestHandlerInterface
{
    use LoggerProviderTrait;
    
    public function __construct(
        private UserService $userService
    ) {}
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('API request received', [
            'uri' => $request->getUri()->getPath(),
            'method' => $request->getMethod(),
        ]);
        
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            $user = $this->userService->createUser($data);
            
            $this->logger->info('API request successful', [
                'userId' => $user->getId(),
            ]);
            
            return new JsonResponse([
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ],
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
```

**Configuration:**
```php
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;

return [
    ConfigAbstractFactory::class => [
        UserApiHandler::class => [
            UserService::class,
        ],
    ],
];
```

## Example 6: Custom Factory (Advanced)

If you need complete control over service creation, you can manually call `setLogger()`:

```php
<?php

namespace App\Factory;

use App\Service\ComplexService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ComplexServiceFactory
{
    public function __invoke(ContainerInterface $container): ComplexService
    {
        // Create service with complex logic
        $service = new ComplexService(
            $container->get('some.dependency'),
            $container->get('another.dependency'),
            ['custom' => 'config']
        );
        
        // Manually inject logger
        $logger = $container->get('expras_logger');
        $service->setLogger($logger);
        
        return $service;
    }
}
```

```php
<?php

namespace App\Service;

use ExprAs\Logger\Provider\LoggerProviderTrait;

class ComplexService
{
    use LoggerProviderTrait;
    
    public function __construct(
        private SomeDependency $dep1,
        private AnotherDependency $dep2,
        private array $config
    ) {}
    
    public function doComplexOperation(): void
    {
        $this->logger->info('Starting complex operation', [
            'config' => $this->config,
        ]);
        
        // Complex logic here
    }
}
```

## Benefits Summary

### ✅ No Logger in Constructor
```php
// Without trait (verbose)
class Service {
    public function __construct(
        private Dependency $dep,
        private LoggerInterface $logger  // <-- Extra parameter
    ) {}
}

// With trait (clean)
class Service {
    use LoggerProviderTrait;
    
    public function __construct(
        private Dependency $dep
    ) {}
}
```

### ✅ Consistent Logger Everywhere
All services use the same `expras_logger` automatically. No need to pass different loggers around.

### ✅ Easy to Add Logging to Existing Code
```php
// Before
class OldService {
    public function doWork() {
        // No logging
    }
}

// After (just add trait)
class OldService {
    use LoggerProviderTrait;
    
    public function doWork() {
        $this->logger->info('Work started');
        // ... work ...
        $this->logger->info('Work completed');
    }
}
```

## Common Patterns

### Pattern 1: Log Method Entry/Exit
```php
public function processData(array $data): Result
{
    $this->logger->debug('Entering processData', ['dataSize' => count($data)]);
    
    $result = $this->doProcessing($data);
    
    $this->logger->debug('Exiting processData', ['result' => $result]);
    
    return $result;
}
```

### Pattern 2: Log Errors with Context
```php
public function handleRequest(Request $request): Response
{
    try {
        return $this->processRequest($request);
    } catch (\Exception $e) {
        $this->logger->error('Request handling failed', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request' => [
                'uri' => $request->getUri(),
                'method' => $request->getMethod(),
            ],
        ]);
        
        throw $e;
    }
}
```

### Pattern 3: Log Important State Changes
```php
public function updateUserStatus(User $user, string $newStatus): void
{
    $oldStatus = $user->getStatus();
    
    $this->logger->info('User status changing', [
        'userId' => $user->getId(),
        'oldStatus' => $oldStatus,
        'newStatus' => $newStatus,
    ]);
    
    $user->setStatus($newStatus);
    $this->entityManager->flush();
    
    $this->logger->info('User status changed successfully', [
        'userId' => $user->getId(),
        'status' => $newStatus,
    ]);
}
```

## Testing

When testing services that use `LoggerProviderTrait`, you can inject a test logger:

```php
<?php

namespace AppTest\Service;

use App\Service\UserService;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testCreateUser(): void
    {
        $service = new UserService(
            $this->createMock(EntityManager::class),
            $this->createMock(UserRepository::class)
        );
        
        // Inject test logger
        $service->setLogger(new NullLogger());
        
        // Test the service
        // ...
    }
}
```

Or use a mock logger to verify logging behavior:

```php
$mockLogger = $this->createMock(LoggerInterface::class);
$mockLogger->expects($this->once())
    ->method('info')
    ->with('User created successfully', $this->anything());

$service->setLogger($mockLogger);
$service->createUser(['email' => 'test@example.com']);
```

