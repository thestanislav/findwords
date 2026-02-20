# ExprAs Doctrine Package

Database and storage solutions for Mezzio applications with Doctrine ORM integration.

## Installation

```bash
composer require expras/doctrine
```

## Features

### Doctrine ORM Integration
- Entity manager configuration
- Repository factory
- Custom types support
- Event subscribers
- Query builders

### CRUD Operations
- Generic CRUD repository
- Automated CRUD operations
- Batch operations support
- Soft delete functionality
- Timestampable entities

### Repository Pattern
- Abstract repository class
- Custom repository factories
- Query builder helpers
- Pagination support
- Criteria API

### Database Migrations
- Migration generation
- Schema updates
- Data fixtures
- Rollback support
- Console commands

### File Upload Handling
- File upload management
- Storage strategies
- Image processing
- File validation
- Cleanup handling

## Usage

### Entity Configuration

```php
use Doctrine\ORM\Mapping as ORM;
use Expras\Doctrine\Entity\AbstractEntity;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string')]
    private $name;

    // Getters and setters
}
```

### Repository Usage

```php
use Expras\Doctrine\Repository\AbstractRepository;

class UserRepository extends AbstractRepository
{
    public function findByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function getActiveUsers()
    {
        return $this->createQueryBuilder('u')
            ->where('u.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
```

### CRUD Operations

```php
use Expras\Doctrine\Service\CrudService;

class UserService
{
    public function __construct(
        private CrudService $crudService
    ) {}

    public function createUser(array $data)
    {
        return $this->crudService->create(User::class, $data);
    }

    public function updateUser($id, array $data)
    {
        return $this->crudService->update(User::class, $id, $data);
    }
}
```

## Configuration

Create a `config/autoload/doctrine.global.php` configuration file:

```php
return [
    'doctrine' => [
        'connection' => [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'your_database',
            'user' => 'your_user',
            'password' => 'your_password',
        ],
        'entity_paths' => [
            'src/Entity',
        ],
        'dev_mode' => false,
        'cache_dir' => 'data/cache/doctrine',
    ],
];
```

## Console Commands

Available console commands:

```bash
# Generate migration
php bin/console doctrine:migrations:generate

# Run migrations
php bin/console doctrine:migrations:migrate

# Create database schema
php bin/console doctrine:schema:create

# Update database schema
php bin/console doctrine:schema:update
```

## Requirements

- PHP 8.0 or higher
- Mezzio 3.x
- PDO PHP Extension
- Doctrine ORM ^2.0

## Documentation

For detailed documentation, please visit the [official documentation](https://docs.expras.com/doctrine).

## Contributing

Please read [CONTRIBUTING.md](../CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests. 