<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 3/25/2018
 * Time: 12:28
 */

namespace ExprAs\Rbac\Console;

use App\Entity\Attendee;
use Doctrine\ORM\EntityManager;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Rbac\Entity\Role;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RoleAdd extends Command
{
    use ServiceContainerAwareTrait;

    protected static $defaultName = 'rbac:role-add';

    /**
     * @var \ExprAs\Rbac\Repository\Role 
     */
    protected $_repository;


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
            ->setDescription('Add roles')
            ->setDefinition(
                new InputDefinition(
                    [
                    new InputArgument('name', InputArgument::REQUIRED, 'Role defined name to insert'),
                    new InputArgument('label', InputArgument::OPTIONAL, 'Role label to insert'),
                    new InputArgument('parent', InputArgument::OPTIONAL, 'Specify parent role'),
                    ]
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $role = new Role();
        $role->setRoleName($input->getArgument('name'));
        $role->setLabel($input->getArgument('label') ?? $input->getArgument('name'));

        if ($existingRole = $this->getRepository()->findOneBy(['role_name' => $input->getArgument('name')])) {

            $output->writeln('Role with name ' . $input->getArgument('name') . ' already defined');
            return 0;
        }

        if (($parentName = $input->getArgument('parent'))) {
            if (!$parentRole = $this->getRepository()->findOneBy(['role_name' => $parentName])) {
                return $output->writeln('Could not find any role named ' . $parentName);
            }

            $role->setParent($parentRole);
        }

        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->flush();

        $output->writeln(sprintf('Role named %s successfully added', $role->getRoleName()));

        return 1;
    }

    /**
     * @return \ExprAs\Rbac\Repository\Role
     */
    public function getRepository()
    {
        if (!$this->_repository) {
            $this->_repository = $this->getEntityManager()->getRepository(Role::class);
        }
        return $this->_repository;
    }




}
