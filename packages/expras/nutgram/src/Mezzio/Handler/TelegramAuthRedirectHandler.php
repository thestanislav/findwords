<?php

namespace ExprAs\Nutgram\Mezzio\Handler;

use ExprAs\Nutgram\Service\TelegramAuthService;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Http\Header\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTTP handler for Telegram authentication redirect
 *
 * Generates an authentication token and redirects users to Telegram bot deep link.
 * Can be used as an endpoint like /auth/telegram or /telegram/auth
 *
 * Example usage:
 * - User visits /auth/telegram
 * - Handler generates token and redirects to https://t.me/bot_username?start=token
 * - User starts bot, handler processes token and links accounts
 */
class TelegramAuthRedirectHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TelegramAuthService $authService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Generate authentication token
        $token = $this->authService->generateToken();

        // Optional: Store return URL for post-authentication redirect
        $queryParams = $request->getQueryParams();
        $returnUrl = $queryParams['return'] ?? null;

        // Generate Telegram deep link
        $telegramUrl = $this->authService->getAuthLink($token);

        // Create cookie with the token for automatic authentication when user returns
        $cookieTtl = $this->authService->getDefaultTtl();
        $setCookie = new SetCookie(
            'telegram_auth_token', // Cookie name
            $token,                // Cookie value
            time() + $cookieTtl,   // Expiration time
            '/',                   // Path
            null,                  // Domain (null = current domain)
        
        );

        // Create redirect response and add the cookie
        $response = new RedirectResponse($telegramUrl);
        return $response->withAddedHeader('Set-Cookie', $setCookie->getFieldValue());
    }
}
