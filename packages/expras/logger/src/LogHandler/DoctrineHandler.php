<?php

namespace ExprAs\Logger\LogHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use ExprAs\Logger\Entity\ErrorLogEntity;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Laminas\Hydrator\HydratorInterface;

/**
 * Doctrine Handler for Monolog
 * 
 * Stores log records in database using Doctrine ORM.
 * Supports any entity class extending AbstractLogEntity.
 */
class DoctrineHandler extends AbstractProcessingHandler
{
    protected string $entityName;
    protected HydratorInterface $hydrator;

    public function __construct(
        protected EntityManager $entityManager,
        ?HydratorInterface $hydrator = null,
        ?string $entityName = null,
        int|Level $level = Level::Debug,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->hydrator = $hydrator ?? new DoctrineObject($this->entityManager);
        $this->entityName = $entityName ?? ErrorLogEntity::class;
    }

    protected function write(LogRecord $record): void
    {
        // Check if EntityManager is closed - if so, skip database write
        if (!$this->entityManager->isOpen()) {
            // Fallback to PHP error log to not lose the log entry
            error_log(sprintf(
                '[%s] %s.%s: %s %s',
                $record->datetime->format('Y-m-d H:i:s'),
                $record->channel,
                $record->level->name,
                $record->message,
                !empty($record->context) ? json_encode($record->context) : ''
            ));
            return;
        }
        
        try {
            $entity = new $this->entityName();
            
            // Map Monolog record to entity (common fields from AbstractLogEntity)
            $entity->setDatetime($record->datetime);
            $entity->setLevel($record->level->value);
            $entity->setLevelName($record->level->name);
            $entity->setMessage($record->message);
            $entity->setChannel($record->channel);
            $entity->setContext($record->context);
            
            // Set extra data if available
            if (!empty($record->extra)) {
                $entity->setExtra($record->extra);
            }
            
            // Allow subclasses to map module-specific fields
            $this->mapAdditionalFields($entity, $record);
            
            $this->entityManager->persist($entity);
            $this->entityManager->flush($entity);
        } catch (\Throwable $e) {
            // If any exception occurs during database write, fallback to error_log
            error_log(sprintf(
                '[DoctrineHandler Error] Failed to write log to database: %s. Original log: [%s] %s.%s: %s',
                $e->getMessage(),
                $record->datetime->format('Y-m-d H:i:s'),
                $record->channel,
                $record->level->name,
                $record->message
            ));
        }
    }

    /**
     * Map additional module-specific fields from context to entity
     * 
     * Override this method in subclasses to add custom field mapping.
     * 
     * @param object $entity The log entity instance
     * @param LogRecord $record The log record with context
     */
    protected function mapAdditionalFields(object $entity, LogRecord $record): void
    {
        // Default: no additional fields
        // Subclasses should override this to map their own fields
    }

    /**
     * Set optional field on entity if method exists and value is not null
     */
    protected function setOptionalField(object $entity, string $method, mixed $value): void
    {
        if ($value !== null && method_exists($entity, $method)) {
            $entity->$method($value);
        }
    }
}
