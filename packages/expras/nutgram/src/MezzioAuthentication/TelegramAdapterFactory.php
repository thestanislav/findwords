<?php

namespace ExprAs\Nutgram\MezzioAuthentication;

use Doctrine\ORM\EntityManager;
use ExprAs\Nutgram\Service\TelegramAuthService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class TelegramAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        // Get the configured user entity name from expras-user config (not nutgram config)
        $config = $container->get('config');
        $userConfig = $config['expras-user'] ?? [];
        $userEntityName = $userConfig['entity_name'] ?? \ExprAs\User\Entity\User::class;

        // Get EntityManager and create repository for the user entity
        $em = $container->get(EntityManager::class);
        $userRepository = $em->getRepository($userEntityName);

        // Get TelegramAuthService and ResponseInterface
        $authService = $container->get(TelegramAuthService::class);
        $responseFactory = $container->get(ResponseInterface::class);

        return new TelegramAdapter($userRepository, $authService, $responseFactory);
    }
}
