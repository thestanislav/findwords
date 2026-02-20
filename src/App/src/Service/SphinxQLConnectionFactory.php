<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 10/21/2017
 * Time: 18:14
 */


namespace  App\Service;

use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;


class SphinxQLConnectionFactory implements FactoryInterface {



    /**
     * Create an object
     *
     * @param  string             $requestedName
     * @param  null|array<mixed>  $options
     * @return object
     * @throws ServiceNotFoundException If unable to resolve the service.
     * @throws ServiceNotCreatedException If an exception is raised when creating a service.
     * @throws ContainerException If any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null) {

        $config = $container->get('config')['sphinx-ql'];
        $instance = new Connection();
        $instance->setParams($config['connection']);

        return $instance;
    }
}