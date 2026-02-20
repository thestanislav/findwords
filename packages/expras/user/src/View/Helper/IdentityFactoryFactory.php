<?php

declare(strict_types=1);

namespace ExprAs\User\View\Helper;

use ExprAs\Core\Http\CurrentRequestHolder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;

class IdentityFactoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Identity
    {
        $auth   = $container->get(AuthenticationInterface::class);
        $holder = $container->get(CurrentRequestHolder::class);

        $authCheck = static function () use ($auth, $holder) {
            $request = $holder->get();
            if ($request === null) {
                return null;
            }
            $user = $request->getAttribute(UserInterface::class);
            if ($user !== null) {
                return $user;
            }
            return $auth->authenticate($request);
        };

        return new Identity($authCheck);
    }
}
