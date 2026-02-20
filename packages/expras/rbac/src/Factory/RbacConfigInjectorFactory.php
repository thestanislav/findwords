<?php

namespace ExprAs\Rbac\Factory;

use Doctrine\ORM\EntityManager;
use ExprAs\Rbac\Entity\Permission;
use ExprAs\Rbac\Entity\Role;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\RoleInterface;
use Mezzio\Application;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Psr\Container\ContainerInterface;

class RbacConfigInjectorFactory
{
    /**
     * @param $serviceName
     *
     * @return Application
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {

        /**
 * @var $app Application 
*/
        $app = $callback();
        /**
 * @var EntityManager $em 
*/
        $em = $container->get(EntityManager::class);

        /**
 * @var NestedTreeRepository $roleRepo 
*/
        $roleRepo = $em->getRepository(Role::class);


        /**
 * @var LaminasRbac $rbac 
*/
        $laminasRbac = $container->get(LaminasRbac::class);
        $ref = new \ReflectionObject($laminasRbac);
        $prop = $ref->getProperty('rbac');
        $rbac = $prop->getValue($laminasRbac);

        $roots = $roleRepo->getRootNodes();
        if (count($roots)) {
            $this->injectRolesAndPermissions($rbac, current($roots));
        }


        return $app;
    }

    protected function injectRolesAndPermissions(Rbac $rbac, Role $role): void
    {
        $rbac->addRole($role->getRoleName(), $role->getParent() ? $role->getParent()->getName() : null);
        if ($role->getPermissions()->count()) {
            foreach ($role->getPermissions() as $_permission) {
                $rbac->getRole($role->getRoleName())->addPermission($_permission->getPermName());
            }

        }

        foreach ($role->getChildren() as $child) {
            $this->injectRolesAndPermissions($rbac, $child);
        }
    }


}
