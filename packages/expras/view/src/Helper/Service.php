<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 11.11.13
 * Time: 15:57
 */

namespace ExprAs\View\Helper;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Psr\Container\ContainerInterface;
use Laminas\View\Helper\AbstractHelper;

class Service extends AbstractHelper
{
    protected $_container;

    /**
     * ProfiledPaginationControl constructor.
     */
    public function __construct(ContainerInterface $_container)
    {
        $this->_container = $_container;
    }

    public function __invoke($name)
    {
        return $this->_container->get($name);
    }
}
