<?php

namespace ExprAs\Nutgram\MezzioAuthentication;

use Doctrine\ORM\EntityManager;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Nutgram\Service\TelegramAuthService;
use ExprAs\User\Entity\User;
use ExprAs\User\MezzioAuthentication\AbstractAdapter;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TelegramAdapter extends AbstractAdapter
{
    protected DefaultRepository $userRepository;
    protected TelegramAuthService $authService;
    
    /**
     * @var callable
     */
    protected $responseFactory;

    public function __construct(
        DefaultRepository $userRepository,
        TelegramAuthService $authService,
        callable $responseFactory
    ) {
        $this->userRepository = $userRepository;
        $this->authService = $authService;
        $this->responseFactory = $responseFactory;
    }

    #[\Override]
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        // Check if user is already authenticated from request attribute (set by other adapters)
        if (($user = parent::authenticate($request))) {
            return $user;
        }

        // Extract auth token from request (query parameter, header, or cookie)
        $authToken = $this->extractAuthToken($request);
        if (!$authToken) {
            return null;
        }

        // Validate token and get app user ID
        $appUserId = $this->authService->validateToken($authToken);
        if (!$appUserId) {
            return null;
        }

        // Find User entity directly by app user ID
        $user = $this->userRepository->find($appUserId);
        if (!$user || !$user->isActive()) {
            return null;
        }

        // Remove token from cache after successful authentication (single-use tokens)
        $this->authService->removeToken($authToken);

        return $user;
    }

    protected function extractAuthToken(ServerRequestInterface $request): ?string
    {
        // Try query parameter first
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['telegram_auth_token'])) {
            return $queryParams['telegram_auth_token'];
        }

        // Try header
        $headers = $request->getHeaders();
        if (isset($headers['X-Telegram-Auth-Token'])) {
            return $headers['X-Telegram-Auth-Token'][0] ?? null;
        }

        // Try cookie
        $cookies = $request->getCookieParams();
        if (isset($cookies['telegram_auth_token'])) {
            return $cookies['telegram_auth_token'];
        }

        return null;
    }

    #[\Override]
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->responseFactory)();
    }
}
