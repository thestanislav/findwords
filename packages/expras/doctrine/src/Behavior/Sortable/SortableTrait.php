<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 09.06.13
 * Time: 16:49
 */

namespace ExprAs\Doctrine\Behavior\Sortable;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation as Form;

trait SortableTrait
{
    /**
     * @Form\Type("hidden")
     */
    #[ORM\Column(type: DoctrineTypes::INTEGER, nullable: true)]
    #[Gedmo\SortablePosition]
    protected ?int $position = null;

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     */
    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }


}
