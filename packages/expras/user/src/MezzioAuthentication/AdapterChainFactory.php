<?php

namespace ExprAs\User\MezzioAuthentication;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class AdapterChainFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config')['authentication'];
        $adapters = $config['adapters'];
        $instance = new AdapterChain($container->get(ResponseInterface::class));

        $cnt = is_countable($adapters) ? count($adapters) : 0;
        foreach ($adapters as $priority => $_adapter) {
            if (is_array($_adapter)) {
                if (isset($_adapter['priority'])) {
                    $priority = $_adapter['priority'];
                }
                $_adapter = $_adapter['adapter'];
            }

            if (is_string($_adapter)) {
                if (!$container->has($_adapter)) {
                    throw new \RuntimeException('Could not find adapter with name ' . $_adapter);
                }
                $_adapter = $container->get($_adapter);
            }

            $instance->inertAdapter($_adapter, $cnt - $priority);
        }

        return $instance;
    }
}
