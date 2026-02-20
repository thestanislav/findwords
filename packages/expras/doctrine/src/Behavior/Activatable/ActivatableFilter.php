<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 27.05.13
 * Time: 18:11
 */

namespace ExprAs\Doctrine\Behavior\Activatable;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class ActivatableFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        
        if (!in_array(ActivatableTrait::class, class_uses($targetEntity->getName()))) {
            return "";
        }

        return $targetTableAlias.'.is_active = 1';
    }
}
