<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 21.08.13
 * Time: 20:11
 */

namespace ExprAs\Mailer\Service;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Factory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $args = func_get_args();
        $args = array_slice($args, 1);

        if (array_intersect(array(MailQueueService::class), $args)) {
            return new MailQueueService();
        } elseif (array_intersect(array(ServiceOptions::class), $args)) {
            $config = $container->get('Config');
            $config = $config['expras_mailer'];
            return new ServiceOptions($config['transport']);
        } elseif (array_intersect(array(ModuleOptions::class), $args)) {
            $config = $container->get('Config');
            $config = $config['expras_mailer'];
            return new ModuleOptions($config['module']);
        } elseif (array_intersect(array(MessageFactory::class), $args)) {
            $config = $container->get('Config');
            $config = $config['expras_mailer'];
            return new MessageFactory($config['message']['default']);
        }
    }
}

