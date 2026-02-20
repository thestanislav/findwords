<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Factory for creating Symfony Mailer instance
 */

namespace ExprAs\Mailer\Service;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

class MailerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MailerInterface
    {
        $config = $container->get('Config');
        $mailerConfig = $config['expras_mailer']['transport'] ?? [];
        
        $dsn = $mailerConfig['dsn'] ?? 'native://default';
        
        $transport = Transport::fromDsn($dsn);
        
        return new Mailer($transport);
    }
}

