<?php

namespace ExprAs\Doctrine\Hydrator\Strategy;

use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;
use Doctrine\Common\Collections\Collection;
use Laminas\Filter\StaticFilter;
use ExprAs\Doctrine\Hydrator\DoctrineEntity as DoctrineEntityHydrator;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy as BaseAbstractCollectionStrategy;

/**
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
abstract class AbstractCollectionStrategy extends BaseAbstractCollectionStrategy
{
}
