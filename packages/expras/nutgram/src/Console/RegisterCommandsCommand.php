<?php
namespace ExprAs\Nutgram\Console;

use Psr\Container\ContainerInterface;
use SergiX44\Nutgram\Nutgram;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nutgram:register-commands',
    description: 'Register the bot commands',
)]
class RegisterCommandsCommand extends Command
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

        $this->getBot()->registerMyCommands();

        $io->success('Bot commands set.');

        return Command::SUCCESS;
    }
}