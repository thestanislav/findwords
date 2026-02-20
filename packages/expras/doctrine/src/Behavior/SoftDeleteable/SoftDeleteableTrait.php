<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 06.12.12
 * Time: 12:18
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Doctrine\Behavior\SoftDeleteable;

use Gedmo\Mapping\Annotation as Gedmo;
use Laminas\Form\Annotation as Form;

/**
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="dtime")
 */
trait SoftDeleteableTrait
{
    /**
     * @ORM\Column(name="dtime", type="datetime", nullable=true)
     * @Form\Exclude
     */
    protected $dtime;
}
