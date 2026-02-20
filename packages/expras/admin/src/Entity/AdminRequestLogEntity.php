<?php

namespace ExprAs\Admin\Entity;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use ExprAs\Logger\Entity\AbstractLogEntity;
use ExprAs\Rest\Mappings\Queryable;
use ExprAs\User\Entity\UserSuper;
use Doctrine\ORM\Mapping as ORM;

/**
 * Admin Request Log Entity
 * 
 * Stores admin REST API request logs including user, resource, action,
 * request data, and response information.
 */
#[ORM\Table(name: 'expras_admin_request_logs')]
#[ORM\Entity]
class AdminRequestLogEntity extends AbstractLogEntity
{
    /**
     * Admin user who performed the action
     */
    #[ORM\ManyToOne(targetEntity: UserSuper::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?UserSuper $user = null;

    /**
     * Resource name (e.g., 'users', 'posts', 'settings')
     * HTTP/1.1 specification (RFC 7230) recommend that servers be cautious about URLs longer than 8000 characters
     */
    #[Queryable]
    #[ORM\Column(type: DoctrineTypes::STRING, length: 8000, nullable: true)]
    protected ?string $resource = null;

    /**
     * Action name (e.g., 'create', 'update', 'delete', 'list')
     */
    #[Queryable]
    #[ORM\Column(type: DoctrineTypes::STRING, length: 64, nullable: true)]
    protected ?string $action = null;

    /**
     * HTTP method
     */
    #[Queryable]
    #[ORM\Column(name: 'http_method', type: DoctrineTypes::STRING, length: 12, nullable: true)]
    protected ?string $httpMethod = null;

    /**
     * Request URI
     */
    #[Queryable]
    #[ORM\Column(name: 'request_uri', type: DoctrineTypes::STRING, nullable: true, length: 8000)]
    protected ?string $requestUri = null;

    /**
     * Request body/parameters
     */
    #[ORM\Column(name: 'request_data', type: DoctrineTypes::JSON, nullable: true)]
    protected ?array $requestData = null;

    /**
     * Response HTTP status code
     */
    #[ORM\Column(name: 'response_status', type: DoctrineTypes::INTEGER, nullable: true)]
    protected ?int $responseStatus = null;

    /**
     * Entity ID (for create/update/delete actions)
     */
    #[Queryable]
    #[ORM\Column(name: 'entity_id', type: DoctrineTypes::STRING, length: 64, nullable: true)]
    protected ?string $entityId = null;

    /**
     * IP address of the requester
     */
    #[ORM\Column(name: 'ip_address', type: DoctrineTypes::STRING, length: 128, nullable: true)]
    protected ?string $ipAddress = null;

    public function getUser(): ?UserSuper
    {
        return $this->user;
    }

    public function setUser(?UserSuper $user): void
    {
        $this->user = $user;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(?string $resource): void
    {
        $this->resource = $resource;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    public function getHttpMethod(): ?string
    {
        return $this->httpMethod;
    }

    public function setHttpMethod(?string $httpMethod): void
    {
        $this->httpMethod = $httpMethod;
    }

    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    public function setRequestUri(?string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    public function getRequestData(): ?array
    {
        return $this->requestData;
    }

    public function setRequestData(?array $requestData): void
    {
        $this->requestData = $requestData;
    }

    public function getResponseStatus(): ?int
    {
        return $this->responseStatus;
    }

    public function setResponseStatus(?int $responseStatus): void
    {
        $this->responseStatus = $responseStatus;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): void
    {
        $this->entityId = $entityId;
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

