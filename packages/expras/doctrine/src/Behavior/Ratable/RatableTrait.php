<?php

namespace ExprAs\Doctrine\Behavior\Ratable;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait providing common implementation for ratable entities.
 * 
 * This trait provides a standard `rating` field and getter/setter methods
 * for entities that use the Ratable behavior.
 * 
 * @example Basic usage
 * ```php
 * use ExprAs\Doctrine\Behavior\Ratable\RatableTrait;
 * use ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable;
 * 
 * #[ORM\Entity]
 * class Player
 * {
 *     use RatableTrait;
 *     
 *     #[Ratable(sort: ["score" => "desc"])]
 *     protected int $rating = 0;
 * }
 * ```
 */
trait RatableTrait
{
    /**
     * Rating/rank value assigned by the Ratable behavior.
     * 
     * Note: You should also add the #[Ratable] attribute to this field
     * with appropriate sorting criteria.
     */
    #[ORM\Column(name: "rating", type: "integer", nullable: false, options: ["default" => 0])]
    protected int $rating = 0;

    /**
     * Get the rating/rank value.
     * 
     * @return int The rating value
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * Set the rating/rank value.
     * 
     * This method is called automatically by the Ratable behavior listener.
     * 
     * @param int $rating The rating value to set
     * @return void
     */
    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }
}

