<?php


namespace ExprAs\Nutgram\Console;

use Psr\Container\ContainerInterface;
use SergiX44\Nutgram\Nutgram;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nutgram:hook:set',
    description: 'Set the bot webhook using configuration from nutgram.webhook',
)]
class HookSetCommand extends Command
{
    private ?Nutgram $bot = null;

    public function __construct(private readonly ContainerInterface $container, ?string $name = null)
    {
        parent::__construct($name);
    }

    private function getBot(): Nutgram
    {
        if ($this->bot === null) {
            $this->bot = $this->container->get(Nutgram::class);
        }
        return $this->bot;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->container->get('config');
        $webhookConfig = $config['nutgram']['webhook'] ?? [];

        if (empty($webhookConfig['url'])) {
            $io->error('Webhook URL is not configured. Please set it in the configuration.');
            return Command::FAILURE;
        }

        $url = $webhookConfig['url'];
        $ip_address = $webhookConfig['ip_address'] ?? null;
        $max_connections = $webhookConfig['max_connections'] ?? 40;
        $drop_pending_updates = $webhookConfig['drop_pending_updates'] ?? false;
        $secret_token = $webhookConfig['secret_token'] ?? null;
        $allowed_updates = $webhookConfig['allowed_updates'] ?? null;

        try {
            $this->getBot()->setWebhook(
                $url,
                certificate: null,
                ip_address: $ip_address,
                max_connections: $max_connections,
                allowed_updates: $allowed_updates,
                drop_pending_updates: $drop_pending_updates,
                secret_token: $secret_token
            );

            $io->success([
                'Bot webhook configured successfully:',
                sprintf('URL: %s', $url),
                sprintf('IP Address: %s', $ip_address ?: 'Not set'),
                sprintf('Max Connections: %d', $max_connections),
                sprintf('Secret Token: %s', $secret_token ? 'Set' : 'Not set'),
                sprintf('Drop Pending Updates: %s', $drop_pending_updates ? 'Yes' : 'No'),
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error([
                'Failed to set webhook:',
                $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
