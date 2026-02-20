<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\Dom;

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Mailer\Console\ProcessDispatcher;
use ExprAs\Mailer\Service\Factory;
use ExprAs\Mailer\Service\MailQueueService;
use ExprAs\Mailer\Service\MessageFactory;
use ExprAs\Mailer\Service\ModuleOptions;
use ExprAs\Mailer\Service\ServiceOptions;
use ExprAs\Mailer\Transport\MailQueue;

class ConfigProvider extends AbstractProvider
{
}
