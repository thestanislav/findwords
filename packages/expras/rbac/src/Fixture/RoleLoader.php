<?php

namespace ExprAs\Rbac\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ExprAs\Rbac\Entity\Role;

final class RoleLoader extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ([
            ['developer', 'Разработчик'],
            ['admin', 'Администратор'],
            ['user', 'Пользователь'],
            ['guest', 'Гость'],
        ] as $_item) {
            $parentRole = null;
            if (isset($role)) {
                $parentRole = $role;
            }

            if (!($role = $manager->getRepository(Role::class)->findOneBy(['role_name' => $_item[0]]))) {
                $role = new Role();
                $role->setRoleName($_item[0]);
            }

            $role->setLabel($_item[1]);
            $role->setParent($parentRole);

            $manager->persist($role);

            if ($role->getName() === 'developer') {
                $this->addReference('developer-role', $role);
            } elseif ($role->getName() === 'admin') {
                $this->addReference('admin-role', $role);
            }
        }

        $manager->flush();

    }

    public function getOrder(): int
    {
        return 1;
    }
}
