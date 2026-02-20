<?php

namespace ExprAs\Doctrine\Behavior\InverseCountable\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver;
use Gedmo\Exception\InvalidMappingException;

class Annotation extends AbstractAnnotationDriver
{
    /**
     * Annotation to mark field as one which will store inverse entities count
     */
    public const COUNT = 'ExprAs\Doctrine\\Behavior\\Mapping\\Annotation\\CountableStore';

    /**
     * Annotation to mark field as count group
     */
    public const GROUP = 'ExprAs\Doctrine\\Behavior\\Mapping\\Annotation\\CountableGroup';

    /**
     * List of types which are valid for position fields
     *
     * @var array
     */
    protected $validTypes = [
        'integer',
        'smallint',
        'bigint'
    ];

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);

        // property annotations
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() 
                || $meta->isInheritedField($property->name) 
                || isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }
            // position
            if ($position = $this->reader->getPropertyAnnotation($property, self::POSITION)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find 'position' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$this->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Sortable position field - [{$field}] type is not valid and must be 'integer' in class - {$meta->name}");
                }
                $config['position'] = $field;
            }
            // group
            if ($group = $this->reader->getPropertyAnnotation($property, self::GROUP)) {
                $field = $property->getName();
                if (!$meta->hasField($field) && !$meta->hasAssociation($field)) {
                    throw new InvalidMappingException("Unable to find 'group' - [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!isset($config['groups'])) {
                    $config['groups'] = [];
                }
                $config['groups'][] = $field;
            }
        }

        if (!$meta->isMappedSuperclass && $config) {
            if (!isset($config['position'])) {
                throw new InvalidMappingException("Missing property: 'position' in class - {$meta->name}");
            }
        }
    }
}
