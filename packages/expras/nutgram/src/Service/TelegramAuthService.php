<?php

namespace ExprAs\Nutgram\Service;

use Psr\SimpleCache\CacheInterface;

class TelegramAuthService
{
    private CacheInterface $cache;
    private string $botUsername;
    private int $defaultTtl;

    public function __construct(CacheInterface $cache, string $botUsername, int $defaultTtl = 900)
    {
        $this->cache = $cache;
        $this->botUsername = $botUsername;
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Generate a unique authentication token
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(16)); // 32-character hexadecimal string
    }

    /**
     * Store a token with associated Telegram user ID
     */
    public function storeToken(string $token, int $telegramUserId, ?int $ttl = null): void
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheKey = $this->getCacheKey($token);

        $this->cache->set($cacheKey, $telegramUserId, $ttl);
    }

    /**
     * Validate a token and return the associated Telegram user ID
     * Returns null if token is invalid or expired
     */
    public function validateToken(string $token): ?int
    {
        $cacheKey = $this->getCacheKey($token);

        $telegramUserId = $this->cache->get($cacheKey);

        // If token exists and is valid, return the user ID
        if ($telegramUserId !== null) {
            // Optionally remove the token after first use for security
            // $this->cache->delete($cacheKey);
            return (int) $telegramUserId;
        }

        return null;
    }

    /**
     * Generate Telegram deep link for authentication
     */
    public function getAuthLink(string $token): string
    {
        $startParam = base64_encode(http_build_query(['auth' => $token]));
        return sprintf('https://t.me/%s?start=%s', $this->botUsername, $startParam);
    }

    /**
     * Generate cache key for token
     */
    private function getCacheKey(string $token): string
    {
        return sprintf('telegram_auth_token_%s', $token);
    }

    /**
     * Remove a token from cache (useful for cleanup or single-use tokens)
     */
    public function removeToken(string $token): bool
    {
        $cacheKey = $this->getCacheKey($token);
        return $this->cache->delete($cacheKey);
    }

    /**
     * Check if a token exists (without retrieving the value)
     */
    public function tokenExists(string $token): bool
    {
        $cacheKey = $this->getCacheKey($token);
        return $this->cache->has($cacheKey);
    }

    /**
     * Get the default token TTL
     */
    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }
}
