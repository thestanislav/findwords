<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/21/2018
 * Time: 00:30
 */

namespace ExprAs\Mailer\Console;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Mailer\Service\MailQueueService;
use ExprAs\Mailer\Service\ModuleOptions;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mailer:process', description: 'Process mail queue')]
class ProcessDispatcher extends Command
{
    use ServiceContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setDescription('Process mail queue')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('queue', InputArgument::OPTIONAL, 'Queue group name'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queue = $input->getArgument('queue');
        $output->writeln('Processing queue '. $queue);
        $this->getMailQueueService()->processQueue($queue, $this->getModuleOptions()->getSendLimit());

        return Command::SUCCESS;
    }

    /**
     * @return MailQueueService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getMailQueueService()
    {
        return $this->getContainer()->get(MailQueueService::class);
    }

    /**
     * @return ModuleOptions
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getModuleOptions()
    {
        return $this->getContainer()->get(ModuleOptions::class);
    }
}

