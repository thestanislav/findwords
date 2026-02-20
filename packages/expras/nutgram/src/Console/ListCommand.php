<?php

namespace ExprAs\Nutgram\Console;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Handler;
use SergiX44\Nutgram\Handlers\Type\Command as NutgramCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
    name: 'nutgram:list',
    description: 'List all registered handlers',
)]
class ListCommand extends Command
{
    private readonly Nutgram $bot;

    public function __construct(Nutgram $bot, ?string $name = null)
    {
        parent::__construct($name);
        $this->bot = $bot;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Registered Nutgram Handlers:</info>');

        $handlers = $this->getHandlers();

        if (empty($handlers)) {
            $output->writeln('<comment>No handlers have been registered.</comment>');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['Type', 'Pattern', 'Handler']);

        foreach ($handlers as $handler) {
            $table->addRow([
                $handler['type'],
                $handler['pattern'],
                $handler['handler']
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }

    private function getHandlers(): array
    {
        $handlers = [];

        // Get handlers using reflection since they're private
        $reflection = new \ReflectionClass($this->bot);
        $handlersProperty = $reflection->getProperty('handlers');
        $botHandlers = $handlersProperty->getValue($this->bot);

        foreach ($botHandlers as $type => $typeHandlers) {
            if (is_array($typeHandlers)) {
                foreach ($typeHandlers as $pattern => $handler) {
                    $handlers[] = [
                        'type' => $this->getHandlerType($type),
                        'pattern' => $pattern,
                        'handler' => $this->getHandlerName($handler)
                    ];
                }
            }
        }

        return $handlers;
    }

    private function getHandlerType(string $type): string
    {
        return match ($type) {
            'message' => 'Message',
            'callback_query' => 'Callback Query',
            'command' => 'Command',
            'inline_query' => 'Inline Query',
            'chosen_inline_result' => 'Chosen Inline Result',
            'shipping_query' => 'Shipping Query',
            'pre_checkout_query' => 'Pre Checkout Query',
            'poll' => 'Poll',
            'poll_answer' => 'Poll Answer',
            'my_chat_member' => 'My Chat Member',
            'chat_member' => 'Chat Member',
            'chat_join_request' => 'Chat Join Request',
            'chat_boost' => 'Chat Boost',
            'removed_chat_boost' => 'Removed Chat Boost',
            'api_error' => 'API Error',
            'exception' => 'Exception',
            'fallback' => 'Fallback',
            default => ucfirst($type)
        };
    }

    private function getHandlerName($handler): string
    {
        if (is_callable($handler)) {
            if (is_array($handler)) {
                if (is_object($handler[0])) {
                    return $handler[0]::class . '::' . $handler[1];
                }
                return $handler[0] . '::' . $handler[1];
            }
            if (is_string($handler)) {
                return $handler;
            }
            return 'Closure';
        }

        if (is_object($handler)) {
            return $handler::class;
        }

        return 'Unknown';
    }
}
