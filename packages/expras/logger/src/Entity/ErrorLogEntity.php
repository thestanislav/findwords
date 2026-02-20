<?php

namespace ExprAs\Logger\Entity;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use ExprAs\Rest\Mappings\Queryable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Error Log Entity for ExprAs framework errors and exceptions
 * 
 * Stores errors from Mezzio ErrorHandler, PHP errors, exceptions, etc.
 */
#[ORM\Table(name: 'expras_logs')]
#[ORM\Entity]
class ErrorLogEntity extends AbstractLogEntity
{
    #[Queryable]
    #[ORM\Column(type: DoctrineTypes::STRING, nullable: true)]
    protected ?string $file = null;

    #[Queryable]
    #[ORM\Column(type: DoctrineTypes::INTEGER, nullable: true)]
    protected ?int $line = null;

    /**
     * HTTP/1.1 specification (RFC 7230) recommend that servers be cautious about URLs longer than 8000 characters
     */
    #[Queryable]
    #[ORM\Column(name: 'request_uri', type: DoctrineTypes::STRING, length: 8000, nullable: true)]
    protected ?string $requestUri = null;

    #[Queryable]
    #[ORM\Column(name: 'request_method', type: DoctrineTypes::STRING, length: 12, nullable: true)]
    protected ?string $requestMethod = null;

    #[ORM\Column(name: 'request_body', type: DoctrineTypes::JSON, nullable: true)]
    protected ?array $requestBody = null;

    #[ORM\Column(name: 'ip_address', type: DoctrineTypes::STRING, length:128, nullable: true)]
    protected ?string $ipAddress = null;

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): void
    {
        $this->file = $file;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function setLine(?int $line): void
    {
        $this->line = $line;
    }

    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    public function setRequestUri(?string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(?string $requestMethod): void
    {
        $this->requestMethod = $requestMethod;
    }

    public function getRequestBody(): ?array
    {
        return $this->requestBody;
    }

    public function setRequestBody(?array $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }
}

