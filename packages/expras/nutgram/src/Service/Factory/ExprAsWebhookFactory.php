<?php

namespace ExprAs\Nutgram\Service\Factory;

use ExprAs\Nutgram\RunningMode\ExprAsWebhook;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExprAsWebhookFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config') ?? [];
        $nutgramConfig = $config['nutgram'] ?? [];

        /**
         * @var ServerRequestInterface $request
         */
        $request = $container->get(ServerRequestInterface::class)();
        $webhook = new ExprAsWebhook(
            getToken: fn() => $request->getHeaderLine('X-Telegram-Bot-Api-Secret-Token'),
            secretToken: md5((string) $nutgramConfig['secretToken'])
        );
        $webhook->setRequest($request);
        return $webhook;
    }
}
