<?php

namespace ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation;

use Doctrine\Common\Collections\Criteria;
use Gedmo\Mapping\Annotation\Annotation as GedmoAnnotation;

/**
 * Ratable behavior annotation for automatic ranking/rating of entities.
 * 
 * This behavior automatically assigns sequential ratings (ranks) to entities
 * based on specified sorting criteria. Ratings are recalculated automatically
 * when entities are created, updated, or deleted.
 * 
 * @example Simple rating by score descending
 * ```php
 * #[Ratable(sort: ["score" => "desc"])]
 * protected int $rank = 0;
 * ```
 * 
 * @example Rating with criteria filter (only rate users with lamps earned)
 * ```php
 * #[Ratable(
 *     sort: ["totalLampsEarned" => "desc"],
 *     criteria: [["totalLampsEarned", "gt", 0]],
 *     start: 1,
 *     default: null
 * )]
 * protected ?int $rating = null;
 * ```
 * 
 * @example Rating within groups (separate ratings per tournament)
 * ```php
 * #[Ratable(
 *     sort: ["points" => "desc", "name" => "asc"],
 *     group: "tournament",
 *     start: 1
 * )]
 * protected int $position = 0;
 * ```
 * 
 * @example Multiple ratable fields with priority
 * ```php
 * #[Ratable(sort: ["totalScore" => "desc"], priority: 1)]
 * protected int $overallRank = 0;
 * 
 * #[Ratable(sort: ["weeklyScore" => "desc"], priority: 2)]
 * protected int $weeklyRank = 0;
 * ```
 * 
 * @DoctrineAnnotation\NamedArgumentConstructor
 * @DoctrineAnnotation\Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Ratable implements GedmoAnnotation
{
    /**
     * @param array<string,"asc"|"desc"> $sort Sorting criteria for rating calculation (e.g., ["score" => "desc", "name" => "asc"])
     * @param int $start Starting rating number (default: 1)
     * @param string|null $group Field name to group ratings by (creates separate rating sets per group value)
     * @param array $criteria Additional filtering criteria [[field, operator, ...args]] (e.g., [["active", "eq", true]])
     * @param int|null $default Default value for entities not matching criteria (null means don't assign rating)
     * @param int $priority Execution priority when multiple ratable fields exist (higher = executes first)
     */
    public function __construct(
        private readonly array $sort,
        private readonly int $start = 1,
        private readonly ?string $group = null,
        private readonly array $criteria = [],
        private readonly int|null $default = null,
        private readonly int $priority = 1
    )
    {
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getSort(): array
    {
        return $this->sort;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getDefault(): int|null
    {
        return $this->default;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
