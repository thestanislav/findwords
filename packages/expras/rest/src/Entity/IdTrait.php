<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 24.12.12
 * Time: 13:08
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Rest\Entity;

use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    /**
     * @var integer
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
