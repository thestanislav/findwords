<?php

namespace ExprAs\Doctrine\Behavior\Ratable;

/**
 * Interface for entities that support automatic rating/ranking.
 * 
 * Entities implementing this interface can use the Ratable behavior
 * to automatically calculate and assign rankings based on sorting criteria.
 * 
 * @see \ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable
 */
interface RatableInterface
{
    /**
     * Get the rating/rank value.
     * 
     * @return int|null The rating value, or null if not rated
     */
    public function getRating(): ?int;

    /**
     * Set the rating/rank value.
     * 
     * This method is called automatically by the Ratable behavior listener.
     * 
     * @param int|null $rating The rating value to set
     * @return void
     */
    public function setRating(?int $rating): void;
}
