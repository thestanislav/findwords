<?php

namespace ExprAs\User\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Rbac\Entity\Role;
use Psr\Container\ContainerInterface;

final class UserLoader extends AbstractFixture implements OrderedFixtureInterface
{
    use ServiceContainerAwareTrait;

    public function __construct(?ContainerInterface $container = null)
    {
        if ($container) {
            $this->setContainer($container);
        }
    }

    private function getUserEntityClass(): string
    {
        try {
            $container = $this->getContainer();
            if (!$container) {
                return \ExprAs\User\Entity\User::class;
            }
            
            $config = $container->get('config');
            return $config['expras-user']['entity_name'] ?? \ExprAs\User\Entity\User::class;
        } catch (\Throwable $e) {
            return \ExprAs\User\Entity\User::class;
        }
    }

    public function load(ObjectManager $manager): void
    {
        $userEntityClass = $this->getUserEntityClass();
        
        if (!($user = $manager->getRepository($userEntityClass)->findOneBy(['username' => 'thestanislav']))) {
            $user = new $userEntityClass();
            $user->setUsername('thestanislav');
        }

        $user->setPassword('$2y$10$6SD9D6akyw3XimDiUVAYDO7FsqaU5qo0stMCZ0QIcuPsvsW1Nc.OO');
        $user->setDisplayName('Стас');
        $user->setEmail('stanislav@ww9.ru');
        if (($role = $this->getReference('developer-role', Role::class))) {
            $user->addRbacRoles($role);
        }
        $user->setActive(true);


        $manager->persist($user);


        if (!($user = $manager->getRepository($userEntityClass)->findOneBy(['username' => 'dashman']))) {
            $user = new $userEntityClass();
            $user->setUsername('dashman');
        }

        $user->setPassword('$2y$10$Jsj69iTac6tCSLvd6Jy9HuHETjLdpRLXekWkR/qsUs.2bv02PeL06');
        $user->setDisplayName('Данил');
        $user->setEmail('danil@ya.ru');
        if (($role = $this->getReference('admin-role', Role::class))) {
            $user->addRbacRoles($role);
        }
        $user->setActive(true);


        $manager->persist($user);
        
        
        if (!($user = $manager->getRepository($userEntityClass)->findOneBy(['username' => 'griff_13']))) {
            $user = new $userEntityClass();
            $user->setUsername('griff_13');
        }

        $user->setPassword('$2y$10$JUjuCtqEjRt1a36kYUJ9QOPvh6/eKlxcUMGwCeWSBA.rb5GrrSgSi');
        $user->setDisplayName('Ксения');
        $user->setEmail('grivskaya.ksenia@fu2re.ru');
        if (($role = $this->getReference('admin-role', Role::class))) {
            $user->addRbacRoles($role);
        }
        $user->setActive(true);


        $manager->persist($user);




        $manager->flush();
    }

    public function getOrder(): int
    {
        return 10;
    }
}
