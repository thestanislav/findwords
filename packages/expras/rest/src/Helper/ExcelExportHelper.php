<?php

namespace ExprAs\Rest\Helper;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types as DoctrineTypes;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Uploadable\Entity\Uploaded;
use Laminas\Hydrator\Filter\FilterProviderInterface;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;

class ExcelExportHelper
{
    public function __construct(
        private EntityManager $entityManager,
        private DoctrineEntity $hydrator,
        private UrlHelper $urlHelper,
        private ?ServerUrlHelper $serverUrlHelper = null
    ) {}

    public function getServerUrlHelper(): ?ServerUrlHelper
    {
        return $this->serverUrlHelper;
    }

    /**
     * Generate export data from DQL query
     * 
     * @param Query $dqlQuery
     * @param array $fields Field structure
     * @param array $fieldConfigs Field configurations
     * @return \Generator
     */
    public function generateExportData(Query $dqlQuery, array $fields, array $fieldConfigs): \Generator
    {
        $fieldNames = $this->buildFieldStructure($fields);
        $metadataFactory = $this->entityManager->getMetadataFactory();
        
        foreach ($dqlQuery->getResult() as $result) {
            yield $this->extractEntity($result, $fieldNames, $fieldConfigs, '', $metadataFactory);
        }
    }

    /**
     * Build nested field structure from field configuration
     */
    private function buildFieldStructure(array $fields): array
    {
        $fieldNames = [];
        foreach ($fields as $field => $value) {
            $fieldParts = explode('.', $field);
            $parts = &$fieldNames;
            foreach ($fieldParts as $level) {
                $parts = &$parts[$level];
            }
            unset($parts);
        }
        return $fieldNames;
    }

    /**
     * Extract entity data with transformations
     */
    private function extractEntity(
        $entity, 
        array $fields, 
        array $fieldConfigs, 
        string $pathPrefix,
        $metadataFactory
    ): array {
        // Configure hydrator filters
        $includeFilter = fn ($v) => array_key_exists($v, $fields);
        if ($entity instanceof FilterProviderInterface) {
            $entity->getFilter()->addFilter('include', $includeFilter);
        } else {
            $this->hydrator->addFilter('include', $includeFilter);
        }
        
        $result = [];
        $metadata = $metadataFactory->getMetadataFor($entity::class);
        
        // Apply date formatting strategies
        $this->applyDateStrategies($metadata, $fieldConfigs, $pathPrefix);
        
        // Extract and transform values
        foreach ($this->hydrator->extract($entity) as $key => $val) {
            if ($metadata->hasAssociation($key)) {
                $result[$key] = $this->handleAssociation(
                    $key, $val, $fields, $fieldConfigs, $pathPrefix, $metadata, $metadataFactory
                );
            } else {
                $result[$key] = $this->handleScalarField(
                    $key, $val, $fieldConfigs, $pathPrefix
                );
            }
        }
        
        return $result;
    }

    /**
     * Apply date formatting strategies to hydrator
     */
    private function applyDateStrategies($metadata, array $fieldConfigs, string $pathPrefix): void
    {
        foreach ($metadata->getFieldNames() as $_name) {
            if (!$this->isDateField($metadata, $_name)) {
                continue;
            }
            
            $fieldType = $metadata->getTypeOfField($_name);
            $fullFieldPath = $pathPrefix ? $pathPrefix . '.' . $_name : $_name;
            $format = $fieldConfigs[$fullFieldPath]['format'] ?? null;
            
            $strategy = $this->createDateStrategy($fieldType, $format);
            $this->hydrator->addStrategy($_name, $strategy);
        }
    }

    /**
     * Check if field is a date type
     */
    private function isDateField($metadata, string $fieldName): bool
    {
        return in_array($metadata->getTypeOfField($fieldName), [
            DoctrineTypes::DATETIMETZ_MUTABLE,
            DoctrineTypes::DATETIMETZ_IMMUTABLE,
            DoctrineTypes::DATETIME_IMMUTABLE,
            DoctrineTypes::DATETIME_MUTABLE,
            DoctrineTypes::DATE_IMMUTABLE,
            DoctrineTypes::DATE_MUTABLE,
            DoctrineTypes::TIME_IMMUTABLE,
            DoctrineTypes::TIME_MUTABLE,
        ]);
    }

    /**
     * Create appropriate date formatting strategy
     */
    private function createDateStrategy(string $fieldType, ?string $format)
    {
        $dateFormat = match($fieldType) {
            DoctrineTypes::DATE_IMMUTABLE, DoctrineTypes::DATE_MUTABLE => $format ?: 'Y-m-d',
            DoctrineTypes::TIME_MUTABLE, DoctrineTypes::TIME_IMMUTABLE => $format ?: 'H:i',
            default => $format ?: 'c',
        };
        
        return new \Laminas\Hydrator\Strategy\DateTimeImmutableFormatterStrategy(
            new \Laminas\Hydrator\Strategy\DateTimeFormatterStrategy($dateFormat)
        );
    }

    /**
     * Handle association field extraction
     */
    private function handleAssociation(
        string $key,
        $val,
        array $fields,
        array $fieldConfigs,
        string $pathPrefix,
        $metadata,
        $metadataFactory
    ) {
        $targetClass = $metadata->getAssociationTargetClass($key);
        $nestedPathPrefix = $pathPrefix ? $pathPrefix . '.' . $key : $key;
        $fullFieldPath = $pathPrefix ? $pathPrefix . '.' . $key : $key;
        
        // Check if attachment field
        $isAttachment = $this->isAttachmentField($fullFieldPath, $fieldConfigs);
        $attachmentConfig = $fieldConfigs[$fullFieldPath] ?? [];
        $attachmentRoute = $attachmentConfig['route'] ?? 'fetch-uploaded';
        
        if ($val instanceof Collection && array_key_exists($key, $fields)) {
            return $this->handleCollection(
                $val, $targetClass, $fields[$key] ?? [], $fieldConfigs, 
                $nestedPathPrefix, $isAttachment, $attachmentRoute, $attachmentConfig, $metadataFactory
            );
        } elseif (array_key_exists($key, $fields) && $val) {
            return $this->handleSingleAssociation(
                $val, $targetClass, $fields[$key] ?? [], $fieldConfigs,
                $nestedPathPrefix, $isAttachment, $attachmentRoute, $attachmentConfig, $metadataFactory
            );
        }
        
        return null;
    }

    /**
     * Check if field is configured as attachment
     */
    private function isAttachmentField(string $fieldPath, array $fieldConfigs): bool
    {
        return isset($fieldConfigs[$fieldPath]) 
            && isset($fieldConfigs[$fieldPath]['type'])
            && $fieldConfigs[$fieldPath]['type'] === 'attachment';
    }

    /**
     * Handle collection association
     */
    private function handleCollection(
        Collection $collection,
        string $targetClass,
        ?array $fields,
        array $fieldConfigs,
        string $pathPrefix,
        bool $isAttachment,
        string $attachmentRoute,
        array $attachmentConfig,
        $metadataFactory
    ): array {
        $result = [];
        // Use empty array if fields is null
        $nestedFields = $fields ?? [];
        foreach ($collection as $item) {
            if (!$item instanceof $targetClass) {
                continue;
            }
            
            if ($isAttachment && $item instanceof Uploaded) {
                $result[] = $this->generateAttachmentUrl($item, $attachmentRoute, $attachmentConfig);
            } else {
                $result[] = $this->extractEntity($item, $nestedFields, $fieldConfigs, $pathPrefix, $metadataFactory);
            }
        }
        // If collection is empty and it's a hyperlink type, return empty array (not array with empty string)
        if (empty($result) && $isAttachment && isset($attachmentConfig['excelType']) && $attachmentConfig['excelType'] === 'hyperlink') {
            return [];
        }
        return $result;
    }

    /**
     * Handle single association
     */
    private function handleSingleAssociation(
        $val,
        string $targetClass,
        ?array $fields,
        array $fieldConfigs,
        string $pathPrefix,
        bool $isAttachment,
        string $attachmentRoute,
        array $attachmentConfig,
        $metadataFactory
    ) {
        if ($isAttachment) {
            if ($val instanceof Uploaded) {
                return $this->generateAttachmentUrl($val, $attachmentRoute, $attachmentConfig);
            } elseif (is_array($val) && isset($val['id'])) {
                return $this->generateAttachmentUrlFromArray($val, $attachmentRoute, $attachmentConfig);
            }
            // For hyperlink type, return empty structure instead of empty string
            if (isset($attachmentConfig['excelType']) && $attachmentConfig['excelType'] === 'hyperlink') {
                return ['url' => '', 'text' => ''];
            }
            return '';
        }
        
        // Use empty array if fields is null
        $nestedFields = $fields ?? [];
        return $this->extractEntity($val, $nestedFields, $fieldConfigs, $pathPrefix, $metadataFactory);
    }

    /**
     * Handle scalar field with transformations
     */
    private function handleScalarField(string $key, $val, array $fieldConfigs, string $pathPrefix)
    {
        $value = $val;
        $fullFieldPath = $pathPrefix ? $pathPrefix . '.' . $key : $key;
        
        if (!isset($fieldConfigs[$fullFieldPath])) {
            return $value;
        }
        
        $config = $fieldConfigs[$fullFieldPath];
        
        // Apply select/choice transformation
        if (isset($config['type']) && $config['type'] === 'select' && isset($config['choices'])) {
            $value = $config['choices'][$value] ?? $value;
        }
        
        // Apply attachment transformation
        if (isset($config['type']) && $config['type'] === 'attachment') {
            $value = $this->handleAttachmentValue($value, $config);
        }
        
        return $value;
    }

    /**
     * Handle attachment field value
     */
    private function handleAttachmentValue($value, array $config)
    {
        $routeName = $config['route'] ?? 'fetch-uploaded';
        
        if ($value instanceof Uploaded) {
            return $this->generateAttachmentUrl($value, $routeName, $config);
        } elseif (is_array($value) && isset($value['id'])) {
            return $this->generateAttachmentUrlFromArray($value, $routeName, $config);
        } elseif ($value === null || $value === '') {
            // For hyperlink type, return empty structure instead of empty string
            if (isset($config['excelType']) && $config['excelType'] === 'hyperlink') {
                return ['url' => '', 'text' => ''];
            }
            return '';
        }
        
        return $value;
    }

    /**
     * Generate URL for Uploaded entity
     */
    private function generateAttachmentUrl(Uploaded $uploaded, string $routeName, array $config = []): string|array
    {
        // Skip URL generation in CLI environment
        if (php_sapi_name() === 'cli') {
            $fileName = $uploaded->getName() ?? 'File';
            if (isset($config['excelType']) && $config['excelType'] === 'hyperlink') {
                return ['url' => '', 'text' => $fileName];
            }
            return $fileName;
        }
        
        try {
            $uploadedId = $uploaded->getId();
            $url = ($this->urlHelper)($routeName, ['uploaded_id' => $uploadedId]);
            
            // Wrap with ServerUrlHelper to make it absolute
            if ($this->serverUrlHelper) {
                $url = ($this->serverUrlHelper)($url);
            }
            
            // Check if excelType is hyperlink - return structured data
            if (isset($config['excelType']) && $config['excelType'] === 'hyperlink') {
                return [
                    'url' => $url,
                    'text' => $uploaded->getName() ?? 'Download'
                ];
            }
            
            return $url;
        } catch (\Exception $e) {
            return $uploaded->getName() ?? 'File';
        }
    }

    /**
     * Generate URL from array representation of Uploaded
     */
    private function generateAttachmentUrlFromArray(array $data, string $routeName, array $config = []): string|array
    {
        // Skip URL generation in CLI environment
        if (php_sapi_name() === 'cli') {
            $fileName = $data['name'] ?? 'File';
            if (isset($config['excelType']) && $config['excelType'] === 'hyperlink') {
                return ['url' => '', 'text' => $fileName];
            }
            return $fileName;
        }
        
        try {
            $url = ($this->urlHelper)($routeName, ['uploaded_id' => $data['id']]);
            
            // Wrap with ServerUrlHelper to make it absolute
            if ($this->serverUrlHelper) {
                $url = ($this->serverUrlHelper)($url);
            }
            
            // Check if excelType is hyperlink - return structured data
            if (isset($config['excelType']) && $config['excelType'] === 'hyperlink') {
                return [
                    'url' => $url,
                    'text' => $data['name'] ?? 'Download'
                ];
            }
            
            return $url;
        } catch (\Exception $e) {
            return $data['name'] ?? 'File';
        }
    }
}
