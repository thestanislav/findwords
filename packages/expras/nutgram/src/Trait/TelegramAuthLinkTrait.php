<?php

namespace ExprAs\Nutgram\Trait;

use Doctrine\ORM\EntityManager;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Nutgram\Entity\User as TelegramUserBase;
use ExprAs\Nutgram\Service\TelegramAuthService;
use ExprAs\User\Entity\UserSuper;
use ExprAs\Rbac\Entity\Role;
use SergiX44\Nutgram\Nutgram;

/**
 * Injectable trait for linking Telegram users to application User entities
 * Only active when TelegramAuth adapter is configured in authentication chain
 */
trait TelegramAuthLinkTrait
{
    use ServiceContainerAwareTrait;

    public function subscribeToEvents(Nutgram $bot): void
    {
        $bot->onCommand('start {param}', [$this, 'onStartCommand']);
    }

    protected function onSuccessAuth(Nutgram $bot, ?UserSuper $appUser): void
    {
        if ($appUser) {
            $bot->sendMessage(sprintf(
                "Welcome, %s! You have been successfully authenticated.",
                $appUser->getDisplayName() ?: $appUser->getUsername()
            ));
        } else {
            $bot->sendMessage("Welcome! Telegram authentication is not currently available.");
        }
    }

    protected function onFailedAuth(Nutgram $bot): void
    {
        $bot->sendMessage("Welcome! Telegram authentication is not currently available.");
    }

    /**
     * Handle /start command with optional authentication token
     */
    public function onStartCommand(Nutgram $bot, string $param): void
    {
        // Extract auth token from command parameters
        $decodedParam = base64_decode($param);
        if (false === $decodedParam) {
            return;
        }

        parse_str($decodedParam, $params);
        $authToken = $params['auth'] ?? null;
        if (null === $authToken) {
            return;
        }

        // Attempt to link Telegram user to application user
        $appUser = $this->linkTelegramUserToAppUser($bot, $authToken);

        if ($appUser) {

            $this->onSuccessAuth($bot, $appUser);
        } else {
            $this->onFailedAuth($bot);
        }
    }

    /**
     * Check if Telegram authentication is enabled
     */
    protected function isTelegramAuthEnabled(): bool
    {
        $config = $this->getContainer()->get('config');
        $authenticationConfig = $config['authentication'] ?? [];
        $adapters = $authenticationConfig['adapters'] ?? [];

        // Check if TelegramAdapter is in the authentication chain
        foreach ($adapters as $adapter) {
            $adapterName = is_array($adapter) ? ($adapter['adapter'] ?? null) : $adapter;
            if ($adapterName === \ExprAs\Nutgram\MezzioAuthentication\TelegramAdapter::class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Link Telegram user to application User entity
     *
     * @param Nutgram $bot The bot instance
     * @param string|null $authToken Authentication token from /start command
     * @return AppUser|null The linked User entity or null if linking failed/disabled
     */
    protected function linkTelegramUserToAppUser(Nutgram $bot, ?string $authToken = null): ?UserSuper
    {
        // Only proceed if Telegram auth is enabled
        if (!$this->isTelegramAuthEnabled()) {
            return null;
        }

        // Get Telegram user entity from bot context
        // The entity class is configured via 'nutgram.userEntity' config
        $config = $this->getContainer()->get('config');
        $telegramUserEntityClass = $config['nutgram']['userEntity'] ?? \ExprAs\Nutgram\Entity\DefaultUser::class;

        // Try configured entity class first, then fallback to User (mapped superclass key)
        /**
         * @var TelegramUserBase $telegramUser 
         */
        $telegramUser = $bot->get($telegramUserEntityClass)
            ?? $bot->get(\ExprAs\Nutgram\Entity\User::class);

        if (!$telegramUser) {
            return null;
        }

        // Check if User entity already exists linked to this Telegram user
        if ($telegramUser->getUser()) {
            // If auth token provided and user already exists, store app user ID in cache
            if ($authToken) {
                $authService = $this->getContainer()->get(TelegramAuthService::class);
                $authService->storeToken($authToken, $telegramUser->getUser()->getId());
            }
            return $telegramUser->getUser();
        }

        // Get the configured user entity class from expras-user config
        $config = $this->getContainer()->get('config');
        $userConfig = $config['expras-user'] ?? [];
        $userEntityClass = $userConfig['entity_name'] ?? \ExprAs\User\Entity\User::class;

        // Get EntityManager
        $em = $this->getContainer()->get(EntityManager::class);

        // Create new User entity with Telegram user data
        $appUser = new $userEntityClass();

        // Set basic user information from Telegram user
        $displayName = trim(($telegramUser->getFirstName() ?? '') . ' ' . ($telegramUser->getLastName() ?? ''));
        if ($telegramUser->getUsername()) {
            $userRepository = $em->getRepository($userEntityClass);
            if (!($userRepository->findOneBy(['username' => $telegramUser->getUsername()]))) {
                $appUser->setUsername($telegramUser->getUsername());
            } else {
                $appUser->setUsername($telegramUser->getUsername() . '_' . $telegramUser->getId());
            }
        } else {
            $appUser->setUsername('tg_' . $telegramUser->getId());
        }

        $appUser->setEmail($telegramUser->getId() . '@telegram.local'); // Placeholder email
        $appUser->setDisplayName($displayName ?: null);
        $appUser->setActive(true);

        $role = $em->getRepository(Role::class)->findOneBy(['role_name' => 'user']);
        if ($role) {
            $appUser->addRbacRoles($role);
        }

        // Generate a random password (user will authenticate via Telegram only)
        $appUser->setPassword(password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT));

        // Establish OneToOne relationship
        $appUser->setTelegramUser($telegramUser);
        $telegramUser->setUser($appUser);

        // Save entities
        $em->persist($appUser);
        $em->persist($telegramUser);
        $em->flush();

        // If auth token provided, store app user ID in cache after successful linking
        if ($authToken) {
            $authService = $this->getContainer()->get(TelegramAuthService::class);
            $authService->storeToken($authToken, $appUser->getId());
        }

        return $appUser;
    }
}
