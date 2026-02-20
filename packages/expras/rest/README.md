# ExprAs REST API Package

RESTful API framework and messaging platform integrations for Mezzio applications.

## Installation

```bash
composer require expras/rest
```

## Features

### RESTful API Framework
- Resource-based routing
- Content negotiation
- Request/Response formatting
- API versioning
- Rate limiting
- CORS support

### OpenAPI/Swagger Integration
- Automatic API documentation
- Swagger UI integration
- Request/Response validation
- Schema generation
- API testing tools

### Response Formatting
- JSON/XML response formatting
- HAL/JSON-LD support
- Error response standardization
- Pagination support
- Data transformation

### Security
- API key authentication
- JWT token support
- OAuth2 integration
- Rate limiting
- IP whitelisting

## Usage

### Basic REST Controller

```php
use Expras\Rest\Controller\AbstractRestController;

class UserController extends AbstractRestController
{
    public function get($id)
    {
        return $this->response()->withJson([
            'id' => $id,
            'name' => 'John Doe',
        ]);
    }

    public function getList()
    {
        return $this->response()->withJson([
            'items' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            'total' => 2,
        ]);
    }
}
```

### API Documentation

```php
/**
 * @OA\Get(
 *     path="/api/users/{id}",
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="User found"
 *     )
 * )
 */
```

### Error Handling

```php
use Expras\Rest\Exception\ApiException;

try {
    // Your code
} catch (Exception $e) {
    throw new ApiException(
        'Resource not found',
        404,
        ['resource_id' => $id]
    );
}
```

## Configuration

Create a `config/autoload/rest.global.php` configuration file:

```php
return [
    'rest' => [
        'cors' => [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        ],
        'rate_limit' => [
            'enabled' => true,
            'limit' => 100,
            'period' => 3600,
        ],
    ],
];
```

## Requirements

- PHP 8.0 or higher
- Mezzio 3.x
- JSON PHP Extension
- OpenAPI-PHP

## Documentation

For detailed documentation, please visit the [official documentation](https://docs.expras.com/rest).

## Contributing

Please read [CONTRIBUTING.md](../CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests. 