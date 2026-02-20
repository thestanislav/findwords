<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 24.12.12
 * Time: 13:21
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Rest\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Hydrator\Filter\FilterComposite;
use Laminas\Hydrator\Filter\FilterInterface;
use Laminas\Hydrator\Filter\FilterProviderInterface;

abstract class AbstractEntity
{
    use DefaultFieldsTrait;



}
