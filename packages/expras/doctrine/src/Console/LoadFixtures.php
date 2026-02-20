<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 3/25/2018
 * Time: 12:28
 */

namespace ExprAs\Doctrine\Console;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\Stdlib\ArrayUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'doctrine:fixtures:load', description: 'Load/purge doctrine fixtures')]
class LoadFixtures extends Command
{
    use ServiceContainerAwareTrait;

    


    protected $_entityManager;

    /**
     * @return EntityManager
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getEntityManager()
    {
        if (!$this->_entityManager) {
            $this->_entityManager = $this->getContainer()->get(EntityManager::class);
        }
        return $this->_entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Load/purge doctrine fixtures')
            ->setDefinition(
                new InputDefinition(
                    [
                    new InputArgument('append', InputArgument::OPTIONAL, 'Append the fixtures instead of purging before loading', true),
                    ]
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->get('config');
        $config = $config['fixtures'] ?? [];

        $loader = new Loader();
        foreach ($config['entities'] ?? [] as $_item) {
            $entityObject = $this->getContainer()->has($_item) ? $this->getContainer()->get($_item) : new $_item();
            $loader->addFixture($entityObject);
        }
        foreach ($config['files'] ?? [] as $_item) {
            $loader->loadFromFile($_item);
        }

        foreach ($config['paths'] ?? [] as $_item) {
            $loader->loadFromDirectory($_item);
        }

        $executor = new ORMExecutor($this->getEntityManager(), new ORMPurger());
        $executor->execute($loader->getFixtures(), $input->getArgument('append'));

        return Command::SUCCESS;
    }




}
