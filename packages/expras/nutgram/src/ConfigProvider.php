<?php

namespace ExprAs\Nutgram;

use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Nutgram\Console\HookInfoCommand;
use ExprAs\Nutgram\Console\HookRemoveCommand;
use ExprAs\Nutgram\Console\HookSetCommand;
use ExprAs\Nutgram\Console\LogoutCommand;
use ExprAs\Nutgram\Console\RegisterCommandsCommand;
use ExprAs\Nutgram\Console\DeleteScheduledMessage;
use ExprAs\Nutgram\Console\SendScheduledMessage;
use ExprAs\Nutgram\Console\UpdateScheduledMessage;
use ExprAs\Nutgram\Console\Factory\DeleteScheduledMessageFactory;
use ExprAs\Nutgram\Console\Factory\SendScheduledMessageFactory;
use ExprAs\Nutgram\Console\Factory\UpdateScheduledMessageFactory;
use ExprAs\Nutgram\Console\ListCommand;
use ExprAs\Nutgram\Console\MakeHandlerCommand;
use ExprAs\Nutgram\Mezzio\Handler\ChatActions;
use ExprAs\Nutgram\Mezzio\Handler\WebhookHandler;
use ExprAs\Nutgram\RunningMode\CliRunningMode;
use ExprAs\Nutgram\RunningMode\ExprAsWebhook;
use ExprAs\Nutgram\Service\Factory\ExprAsWebhookFactory;
use ExprAs\Nutgram\Service\Factory\NutgramFactory;
use ExprAs\Nutgram\Service\Factory\NutgramIsFirstConstructorParameterFactory;
use ExprAs\Nutgram\Service\Factory\WebhookHandlerFactory;
use SergiX44\Nutgram\Nutgram;
use ExprAs\Nutgram\Service\Delegator\MiddlewaresInjector;
use ExprAs\Nutgram\Service\Delegator\HandlersRegistrator;
use ExprAs\Nutgram\Service\Delegator\CallbackQueryCommandDelegator;
use ExprAs\Nutgram\Service\Delegator\SubscriberDelegator;
use ExprAs\Nutgram\Service\Delegator\UserFallbackDelegator;
use ExprAs\Nutgram\Mezzio\Handler\ScheduledMessageHandler;
use ExprAs\Core\ServiceManager\Factory\ContainerInvokableFactory;
use ExprAs\Nutgram\DoctrineListener\BotUserModifierListener;
use ExprAs\Nutgram\DoctrineListener\TelegramLogEntityModifierListener;
use ExprAs\Nutgram\DoctrineListener\TelegramUserModifierListener;
use ExprAs\Nutgram\Mezzio\Middleware\ValidateWebAppUser;
use ExprAs\Nutgram\Service\TelegramContextProcessor;
use ExprAs\Nutgram\Service\Factory\TelegramContextProcessorFactory;
use ExprAs\Nutgram\Handler\TelegramLogAdminHandler;
use ExprAs\Nutgram\Handler\NutgramDoctrineHandler;
use ExprAs\Nutgram\Handler\NutgramDoctrineHandlerFactory;
use ExprAs\Nutgram\MezzioAuthentication\TelegramAdapter;
use ExprAs\Nutgram\MezzioAuthentication\TelegramAdapterFactory;
use ExprAs\Nutgram\Service\TelegramAuthService;
use ExprAs\Nutgram\Service\Factory\TelegramAuthServiceFactory;
use ExprAs\Logger\Service\LoggerAbstractFactory;
use Psr\Log\LoggerInterface;
use ExprAs\Nutgram\Mezzio\Handler\TelegramAuthRedirectHandler;
use ExprAs\Nutgram\Mezzio\Handler\TelegramAuthRedirectHandlerFactory;


class ConfigProvider extends AbstractProvider
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                CliRunningMode::class,
                ScheduledMessageHandler::class,
                ChatActions::class,
                BotUserModifierListener::class,
                TelegramLogEntityModifierListener::class,
                ValidateWebAppUser::class,
                TelegramLogAdminHandler::class,
            ],
            'factories'  => [
                TelegramUserModifierListener::class => DoctrineListener\TelegramUserModifierListenerFactory::class,
                Nutgram::class       => NutgramFactory::class,
                'nutgram.webhook'    => NutgramFactory::class,

                // Logger infrastructure
                'nutgram.logger'     => LoggerAbstractFactory::class,
                TelegramContextProcessor::class => TelegramContextProcessorFactory::class,
                NutgramDoctrineHandler::class => NutgramDoctrineHandlerFactory::class,
                HookInfoCommand::class         => NutgramIsFirstConstructorParameterFactory::class,
                HookRemoveCommand::class       => NutgramIsFirstConstructorParameterFactory::class,
                HookSetCommand::class          => ContainerInvokableFactory::class,
                LogoutCommand::class           => ContainerInvokableFactory::class,
                RegisterCommandsCommand::class => ContainerInvokableFactory::class,

                ListCommand::class             => ContainerInvokableFactory::class,
                MakeHandlerCommand::class      => ContainerInvokableFactory::class,

                // Handlers and services
                WebhookHandler::class          => WebhookHandlerFactory::class,
                ExprAsWebhook::class           => ExprAsWebhookFactory::class,
                DeleteScheduledMessage::class => DeleteScheduledMessageFactory::class,
                SendScheduledMessage::class => SendScheduledMessageFactory::class,
                UpdateScheduledMessage::class => UpdateScheduledMessageFactory::class,

                // Telegram authentication
                TelegramAdapter::class         => TelegramAdapterFactory::class,
                TelegramAuthService::class     => TelegramAuthServiceFactory::class,
                TelegramAuthRedirectHandler::class => TelegramAuthRedirectHandlerFactory::class,
            ],
            'aliases'    => [
                'nutgram'          => Nutgram::class,
                'telegram'         => Nutgram::class,
            ],
            'delegators' => [
                'nutgram.webhook' => [
                    HandlersRegistrator::class,
                    CallbackQueryCommandDelegator::class,
                    SubscriberDelegator::class,
                    MiddlewaresInjector::class,
                    UserFallbackDelegator::class, // Must be last to handle fallback cases
                ],
            ],
            'shared' => [
                TelegramLogAdminHandler::class => false,
                ScheduledMessageHandler::class => false,
                ChatActions::class => false,
                WebhookHandler::class => false,
            ],
        ];
    }
}
