<?php

namespace ExprAs\Logger\Entity;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use ExprAs\Rest\Mappings\Queryable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Abstract base class for all log entities
 * 
 * Provides common fields that all log types share:
 * - id, datetime, level, levelName, message, channel, context, extra
 * 
 * Module-specific log entities should extend this class and add their own fields
 */
abstract class AbstractLogEntity
{
    #[ORM\Id, 
    ORM\Column(type: DoctrineTypes::BIGINT), 
    ORM\GeneratedValue]
    protected string $id;

    #[ORM\Column(type: DoctrineTypes::DATETIME_MUTABLE, nullable: false)]
    protected \DateTimeInterface $datetime;

    #[ORM\Column(type: DoctrineTypes::INTEGER, nullable: false)]
    protected int $level;

    #[Queryable]
    #[ORM\Column(name: 'level_name', type: DoctrineTypes::STRING, length: 24, nullable: false)]
    protected string $levelName;

    #[Queryable]
    #[ORM\Column(type: DoctrineTypes::TEXT, nullable: false)]
    protected string $message;

    #[Queryable]
    #[ORM\Column(type: DoctrineTypes::STRING, length: 64, nullable: false)]
    protected string $channel;

    #[ORM\Column(type: DoctrineTypes::JSON, nullable: true)]
    protected ?array $context = null;

    #[ORM\Column(type: DoctrineTypes::JSON, nullable: true)]
    protected ?array $extra = null;

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getDatetime(): \DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): void
    {
        $this->datetime = $datetime;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getLevelName(): string
    {
        return $this->levelName;
    }

    public function setLevelName(string $levelName): void
    {
        $this->levelName = $levelName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    public function getExtra(): ?array
    {
        return $this->extra;
    }

    public function setExtra(?array $extra): void
    {
        $this->extra = $extra;
    }
}

