<?php

namespace ExprAs\Admin\Handler;

use ExprAs\Admin\ResourceMapping\Configuration;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Rbac\Entity\Role;
use ExprAs\User\Entity\UserSuper;
use ExprAs\User\MezzioAuthentication\AdapterChain;
use Laminas\Authentication\AuthenticationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Hydrator\Filter\FilterInterface;
use Laminas\Hydrator\Filter\FilterProviderInterface;
use Laminas\Stdlib\Parameters;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationHandler extends AbstractActionHandler implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    /**
     * @var DoctrineEntity
     */
    protected $_hydrator;

    /**
     * @return array
     */
    protected function _extractUserObject(UserSuper $user): array
    {
        if (!$this->_hydrator) {

            $this->_hydrator = $this->getContainer()->get(DoctrineEntity::class);
            $this->_hydrator->addFilter(
                'exclude', function ($v) {
                    if (in_array($v, ['password', 'ctime', 'mtime'])) {
                        return false;
                    }

                    return true;
                }
            );
        }
        $identity = $this->_hydrator->extract($user);
        $identity['roles'] = [];
        $roles = $user->getRbacRoles()->toArray();
        while (count($roles)) {
            $childRoles = [];
            $identity['roles'] = [...$identity['roles'], ...array_map(
                function (Role $role) use (&$childRoles) {
                    $childRoles = [...$childRoles, ...$role->getChildren()->toArray()];
                    return $role->getRoleName();
                }, $roles
            )];
            $roles = $childRoles;
        }
        $identity['roles'] = $this->_extractUserRoles($user);
        $identity['permissions'] = $this->_extractUserPermissions($user, $identity['roles']);
        unset($identity['rbacRoles']);

        return $identity;
    }

    protected function _extractUserRoles(UserSuper $user): array
    {
        $roles = $user->getRbacRoles()->toArray();
        $roleNames = [];
        while (count($roles)) {
            $childRoles = [];
            $roleNames = [...$roleNames, ...array_map(
                function (Role $role) use (&$childRoles) {
                    $childRoles = [...$childRoles, ...$role->getChildren()->toArray()];
                    return $role->getRoleName();
                }, $roles
            )];
            $roles = $childRoles;
        }

        return $roleNames;
    }

    protected function _extractUserPermissions(UserSuper $user, array $roles): array
    {
        /**
 * @var Configuration $config 
*/
        $config = $this->getContainer()->get('config');
        $permissions = $config['exprass_admin']['permissions'];
        $userPermissions = [];
        foreach ($permissions as $_role => $_perm) {
            if (in_array($_role, $roles)) {
                if ($user->hasRole($_role)) {
                    $userPermissions = array_merge($userPermissions, $_perm);
                } else {
                    $userPermissions = array_merge(
                        $userPermissions, array_filter(
                            $_perm, function ($perm) {
                                if (!isset($perm['type'])) {
                                    return true;
                                }
                                return $perm['type'] !== 'deny';
                            }
                        )
                    );
                }

            }
        }
        return array_values(array_unique($userPermissions, SORT_REGULAR));
    }

    public function loginAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {

        $config = $this->getContainer()->get('config');
        $allowedRoles = array_keys($config['exprass_admin']['permissions']);

        if (!($user = $request->getAttribute(UserInterface::class)) || (count(array_intersect($allowedRoles, $user->getRoles())) === 0)) {
            return new JsonResponse(
                [
                'success' => false,
                ]
            );
        }

        if ($user instanceof FilterProviderInterface) {
            $user->getFilter()->addFilter(
                'exclude', function ($v) {
                    if (in_array($v, ['password', 'ctime', 'mtime'])) {
                        return false;
                    }

                    return true;
                }
            );
        }


        return new JsonResponse(
            [
            'success'  => true,
            'identity' => $this->_extractUserObject($user)
            ]
        );

    }

    public function logoutAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        try {
            /**
 * @var AdapterChain $auth 
*/
            $auth = $this->getContainer()->get(AuthenticationInterface::class);
            $response = $auth->unauthorizedResponse($request);
            return new JsonResponse(
                [
                'success' => true,
                ], 200, $response->getHeaders()
            );
        } catch (\Throwable $e) {
            return new JsonResponse(
                [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace(),
                ]
            );
        }

    }
}
