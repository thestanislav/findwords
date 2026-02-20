<?php

namespace ExprAs\Doctrine\Behavior\Singleable;

/**
 * Interface for entities that use the Singleable behavior.
 * 
 * This behavior ensures that only one entity in a group can have a specific field value.
 * Commonly used for "default" flags, "primary" status, or any scenario where 
 * exclusivity is required within a group.
 */
interface SingleableInterface
{
}

