<?php
namespace ExprAs\Nutgram\Console;

use Psr\Container\ContainerInterface;
use SergiX44\Nutgram\Nutgram;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nutgram:logout',
    description: 'Log out from the cloud Bot API server',
)]
class LogoutCommand extends Command
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

    protected function configure(): void
    {
        $this->addOption('drop-pending-updates', null, InputOption::VALUE_NONE, 'Drop all pending updates');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dropPendingUpdates = (bool)$input->getOption('drop-pending-updates');

        try {
            $this->getBot()->deleteWebhook($dropPendingUpdates);
        } finally {
            $io->info('Webhook deleted.');
        }

        try {
            $this->getBot()->close();
        } finally {
            $io->info('Bot closed.');
        }

        try {
            $this->getBot()->logOut();
        } finally {
            $io->info('Logged out.');
        }

        $io->newLine();
        $io->success('Done.');
        $io->warning('Remember to set the webhook again if needed!');

        return Command::SUCCESS;
    }
}
