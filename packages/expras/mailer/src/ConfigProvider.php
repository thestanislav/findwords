<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */
namespace ExprAs\Mailer;

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Mailer\Console\ProcessDispatcher;
use ExprAs\Mailer\Service\Factory;
use ExprAs\Mailer\Service\MailerFactory;
use ExprAs\Mailer\Service\MailQueueService;
use ExprAs\Mailer\Service\MessageFactory;
use ExprAs\Mailer\Service\ModuleOptions;
use ExprAs\Mailer\Service\ServiceOptions;
use ExprAs\Mailer\Transport\MailQueue;
use Symfony\Component\Mailer\MailerInterface;


class ConfigProvider extends AbstractProvider
{

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
                MailQueue::class,
                ProcessDispatcher::class
            ],
            'factories' => [
                ServiceOptions::class => Factory::class,
                ModuleOptions::class => Factory::class,
                MailQueueService::class => Factory::class,
                MessageFactory::class => Factory::class,
                MailerInterface::class => MailerFactory::class,
            ],
            'aliases' => [
                'ExprAs\MailerQueue' => MailQueueService::class
            ]
        ];
    }

    public function getDependantModules()
    {
        return [
            //new InvokableProvider(\ExprAs\Cron\ConfigProvider::class),
        ];
    }
}

