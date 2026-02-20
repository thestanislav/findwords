<?php

namespace ExprAs\Nutgram\Service\Factory;

use ExprAs\Nutgram\Mezzio\Handler\WebhookHandler;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class WebhookHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): WebhookHandler
    {
        $bot = $container->get('nutgram.webhook');

        return new WebhookHandler($bot);
    }
}
