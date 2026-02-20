<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 25.01.13
 * Time: 1:46
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\View\Helper;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Router\RouteResult;
use Laminas\Paginator;
use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHelper;

class ProfiledPaginationControl extends AbstractHelper
{
    /**
     * @var ContainerInterface 
     */
    protected $_container;

    /**
     * @var ServerRequestInterface 
     */
    protected $_request;

    protected $_config = [];

    /**
     * ProfiledPaginationControl constructor.
     */
    public function __construct(ContainerInterface $_container)
    {
        $this->_container = $_container;
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->_request = $request;
    }



    /**
     * @return array
     */
    protected function _getConfig()
    {
        if (!$this->_config) {
            $config = $this->_container->get('config');
            $this->_config = $config['pagination'];
        }
        return $this->_config;
    }

    protected function _getProfileValue($profile, $configkey)
    {

        $config = $this->_getConfig();
        $config = $config['profiles'];
        if (!isset($config[$profile])) {
            throw new Exception\RuntimeException('Could not find profiles with name ' . $profile);
        }
        if (is_string($config[$profile])) {
            return $this->_getProfileValue($config[$profile], $configkey);
        }

        return $config[$profile][$configkey];
    }


    public function __invoke($profile = 'default', ?Paginator\Paginator $paginator = null, $params = null)
    {

        if ($paginator === null) {
            if (isset($this->view->paginator)
                && $this->view->paginator !== null
                && $this->view->paginator instanceof Paginator\Paginator
            ) {
                $paginator = $this->view->paginator;
            } else {
                throw new Exception\RuntimeException('No paginator instance provided or incorrect type');
            }
        }

        $partial = $this->_getProfileValue($profile, 'partial');
        $scrollingStyle = $this->_getProfileValue($profile, 'style');


        $pages = get_object_vars($paginator->getPages($scrollingStyle));

        $result = $this->_request->getAttribute(RouteResult::class, false);

        if ($result instanceof RouteResult) {
            $pages = [...$pages, 'route' => $result->getMatchedRouteName()];
            $pages = array_merge($pages, ['routeParams' => $result->getMatchedParams()]);
        }

        $pages = [...$pages, 'queryParams' => $this->_request->getQueryParams()];

        if ($params !== null) {
            $pages = array_merge($pages, (array) $params);
        }

        if (is_array($partial)) {
            if (count($partial) != 2) {
                throw new Exception\InvalidArgumentException(
                    'A view partial supplied as an array must contain two values: the filename and its module'
                );
            }

            if ($partial[1] !== null) {
                $partialHelper = $this->view->plugin('partial');
                return $partialHelper($partial[0], $pages);
            }

            $partial = $partial[0];
        }

        $partialHelper = $this->view->plugin('partial');
        return $partialHelper($partial, $pages);

    }
}
