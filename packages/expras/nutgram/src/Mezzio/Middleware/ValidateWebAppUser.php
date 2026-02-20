<?php

namespace ExprAs\Nutgram\Mezzio\Middleware;

use ExprAs\Nutgram\Entity\User;
use SergiX44\Nutgram\Nutgram;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ExprAs\Core\Handler\RequestParamsTrait;
use ExprAs\Nutgram\Entity\DefaultUser;
use Doctrine\ORM\EntityManager;
use SergiX44\Nutgram\Telegram\Web\WebAppData;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;

class ValidateWebAppUser implements MiddlewareInterface
{
    use RequestParamsTrait;
    use ServiceContainerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $bot = $this->getContainer()->get(Nutgram::class);

        $initData = null;
        $authorizationHeaderValue = $this->params($request)->fromHeader('authorization');
        if ($authorizationHeaderValue) {
            foreach ($authorizationHeaderValue as $value) {
                if (is_string($value) && str_starts_with($value, 'TMA ')) {
                    $initData = trim(substr($value, 4));
                    break;
                }
            }
        }
        if (!$initData) {
            #deprecate: use fromHeader('Authorization: TMA <initData>') instead
            $initData = base64_decode($this->params($request)->fromQuery(
                'initData',
                $this->params($request)->fromParsedBody(
                    'initData',
                    $this->params($request)->fromHeader('x-telegram-init-data', '')
                )
            ));
            // from headers returns array
            if (is_array($initData)) {
                $initData = $initData[0];
            }
        }

        $userEntityClass = $this->getContainer()->get('config')['nutgram']['userEntity'];

        if (empty($initData)) {
            // No initData provided, continue without user validation
            return $handler->handle($request->withAttribute($userEntityClass, null));
        }

        // Validate the Telegram Web App init data
        $data = $bot->validateWebAppData($initData);

        // Find user in database based on validated data
        $user = $this->findUserFromWebAppData($data);

        // Append user to request attributes
        $request = $request->withAttribute('webAppData', $data);
        $request = $request->withAttribute($userEntityClass, $user);
        $request = $request->withAttribute(DefaultUser::class, $user);

        return $handler->handle($request);
    }

    private function findUserFromWebAppData(WebAppData $data): ?User
    {
        try {
            $entityManager = $this->getContainer()->get(EntityManager::class);
            $userEntityClass = $this->getContainer()->get('config')['nutgram']['userEntity'] ?? DefaultUser::class;

            // Extract user ID from validated data
            $userId = $data->user?->id ?? null;

            if (!$userId) {
                return null;
            }

            // Find user in database
            $user = $entityManager->find($userEntityClass, $userId);

            return $user instanceof $userEntityClass ? $user : null;
        } catch (\Exception $e) {
            // Log error but don't break the flow
            error_log("Failed to find user from WebApp data: " . $e->getMessage());
            return null;
        }
    }
}
