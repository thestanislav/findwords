<?php

namespace ExprAs\Nutgram\Trait;

use ExprAs\Core\Cache\CacheManagerAwareTrait;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Trait for sending Telegram files with file_id caching
 * 
 * This trait provides methods to send photos, videos, audio, and documents
 * with automatic caching of Telegram file_ids to avoid re-uploading files.
 */
trait CachedFileSendTrait
{
    use CacheManagerAwareTrait;

    /**
     * Generate cache key for file including modification time
     * This ensures cache invalidation when files are updated
     */
    protected function getCacheKey(string $filePath): string
    {
        $mtime = file_exists($filePath) ? filemtime($filePath) : 0;
        return 'telegram_file_id_' . md5($filePath . '_' . $mtime);
    }

    /**
     * Send photo with cached file_id if available, otherwise send file and cache the file_id
     */
    protected function sendCachedPhoto(
        Nutgram $bot,
        string $filePath,
        ?string $caption = null,
        ?string $parseMode = null,
        ?InlineKeyboardMarkup $replyMarkup = null,
        ?int $chatId = null
    ): mixed {
        $cacheKey = $this->getCacheKey($filePath);
        $cache = $this->getCacheManager();
        
        $photoInput = null;
        $usingCachedFileId = false;
        if ($cache) {
            try {
                $cachedFileId = $cache->getItem($cacheKey);
                if ($cachedFileId && is_string($cachedFileId)) {
                    $photoInput = InputFile::make($cachedFileId);
                    $usingCachedFileId = true;
                }
            } catch (\Throwable) {
                // Cache miss or error, continue to send file
            }
        }
        
        if (!$photoInput) {
            $photoInput = InputFile::make($filePath);
        }
        
        if ($chatId !== null) {
            $response = $bot->sendPhoto(
                chat_id: $chatId,
                photo: $photoInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        } else {
            $response = $bot->sendPhoto(
                photo: $photoInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        }
        
        if (!$usingCachedFileId && $response && $response->photo && count($response->photo) > 0) {
            $fileId = $response->photo[count($response->photo) - 1]->file_id;
            if ($cache && $fileId) {
                try {
                    $cache->setItem($cacheKey, $fileId);
                } catch (\Throwable) {
                    // Silently fail if caching fails
                }
            }
        }
        
        return $response;
    }

    /**
     * Send video with cached file_id if available, otherwise send file and cache the file_id
     */
    protected function sendCachedVideo(
        Nutgram $bot,
        string $filePath,
        ?string $caption = null,
        ?string $parseMode = null,
        ?InlineKeyboardMarkup $replyMarkup = null,
        ?int $chatId = null
    ): mixed {
        $cacheKey = $this->getCacheKey($filePath);
        $cache = $this->getCacheManager();
        
        $videoInput = null;
        $usingCachedFileId = false;
        if ($cache) {
            try {
                $cachedFileId = $cache->getItem($cacheKey);
                if ($cachedFileId && is_string($cachedFileId)) {
                    $videoInput = InputFile::make($cachedFileId);
                    $usingCachedFileId = true;
                }
            } catch (\Throwable) {
                // Cache miss or error, continue to send file
            }
        }
        
        if (!$videoInput) {
            $videoInput = InputFile::make($filePath);
        }
        
        if ($chatId !== null) {
            $response = $bot->sendVideo(
                chat_id: $chatId,
                video: $videoInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        } else {
            $response = $bot->sendVideo(
                video: $videoInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        }
        
        if (!$usingCachedFileId && $response && $response->video && $response->video->file_id) {
            $fileId = $response->video->file_id;
            if ($cache && $fileId) {
                try {
                    $cache->setItem($cacheKey, $fileId);
                } catch (\Throwable) {
                    // Silently fail if caching fails
                }
            }
        }
        
        return $response;
    }

    /**
     * Send audio with cached file_id if available, otherwise send file and cache the file_id
     */
    protected function sendCachedAudio(
        Nutgram $bot,
        string $filePath,
        ?string $caption = null,
        ?string $parseMode = null,
        ?InlineKeyboardMarkup $replyMarkup = null,
        ?int $chatId = null
    ): mixed {
        $cacheKey = $this->getCacheKey($filePath);
        $cache = $this->getCacheManager();
        
        $audioInput = null;
        $usingCachedFileId = false;
        if ($cache) {
            try {
                $cachedFileId = $cache->getItem($cacheKey);
                if ($cachedFileId && is_string($cachedFileId)) {
                    $audioInput = InputFile::make($cachedFileId);
                    $usingCachedFileId = true;
                }
            } catch (\Throwable) {
                // Cache miss or error, continue to send file
            }
        }
        
        if (!$audioInput) {
            $audioInput = InputFile::make($filePath);
        }
        
        if ($chatId !== null) {
            $response = $bot->sendAudio(
                chat_id: $chatId,
                audio: $audioInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        } else {
            $response = $bot->sendAudio(
                audio: $audioInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        }
        
        if (!$usingCachedFileId && $response && $response->audio && $response->audio->file_id) {
            $fileId = $response->audio->file_id;
            if ($cache && $fileId) {
                try {
                    $cache->setItem($cacheKey, $fileId);
                } catch (\Throwable) {
                    // Silently fail if caching fails
                }
            }
        }
        
        return $response;
    }

    /**
     * Send voice message with cached file_id if available, otherwise send file and cache the file_id
     */
    protected function sendCachedVoice(
        Nutgram $bot,
        string $filePath,
        ?string $caption = null,
        ?string $parseMode = null,
        ?InlineKeyboardMarkup $replyMarkup = null,
        ?int $chatId = null
    ): mixed {
        $cacheKey = $this->getCacheKey($filePath);
        $cache = $this->getCacheManager();
        
        $voiceInput = null;
        $usingCachedFileId = false;
        if ($cache) {
            try {
                $cachedFileId = $cache->getItem($cacheKey);
                if ($cachedFileId && is_string($cachedFileId)) {
                    $voiceInput = InputFile::make($cachedFileId);
                    $usingCachedFileId = true;
                }
            } catch (\Throwable) {
                // Cache miss or error, continue to send file
            }
        }
        
        if (!$voiceInput) {
            $voiceInput = InputFile::make($filePath);
        }
        
        if ($chatId !== null) {
            $response = $bot->sendVoice(
                chat_id: $chatId,
                voice: $voiceInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        } else {
            $response = $bot->sendVoice(
                voice: $voiceInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        }
        
        if (!$usingCachedFileId && $response && $response->voice && $response->voice->file_id) {
            $fileId = $response->voice->file_id;
            if ($cache && $fileId) {
                try {
                    $cache->setItem($cacheKey, $fileId);
                } catch (\Throwable) {
                    // Silently fail if caching fails
                }
            }
        }
        
        return $response;
    }

    /**
     * Send document with cached file_id if available, otherwise send file and cache the file_id
     */
    protected function sendCachedDocument(
        Nutgram $bot,
        string $filePath,
        ?string $caption = null,
        ?string $parseMode = null,
        ?InlineKeyboardMarkup $replyMarkup = null,
        ?int $chatId = null
    ): mixed {
        $cacheKey = $this->getCacheKey($filePath);
        $cache = $this->getCacheManager();
        
        $documentInput = null;
        $usingCachedFileId = false;
        if ($cache) {
            try {
                $cachedFileId = $cache->getItem($cacheKey);
                if ($cachedFileId && is_string($cachedFileId)) {
                    $documentInput = InputFile::make($cachedFileId);
                    $usingCachedFileId = true;
                }
            } catch (\Throwable) {
                // Cache miss or error, continue to send file
            }
        }
        
        if (!$documentInput) {
            $documentInput = InputFile::make($filePath);
        }
        
        if ($chatId !== null) {
            $response = $bot->sendDocument(
                chat_id: $chatId,
                document: $documentInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        } else {
            $response = $bot->sendDocument(
                document: $documentInput,
                caption: $caption,
                parse_mode: $parseMode,
                reply_markup: $replyMarkup
            );
        }
        
        if (!$usingCachedFileId && $response && $response->document && $response->document->file_id) {
            $fileId = $response->document->file_id;
            if ($cache && $fileId) {
                try {
                    $cache->setItem($cacheKey, $fileId);
                } catch (\Throwable) {
                    // Silently fail if caching fails
                }
            }
        }
        
        return $response;
    }

    /**
     * Send attachment based on MIME type with caching
     * Automatically determines the appropriate send method based on MIME type
     */
    protected function sendCachedAttachment(
        Nutgram $bot,
        string $filePath,
        string $mimeType,
        ?string $caption = null,
        ?string $parseMode = null,
        ?InlineKeyboardMarkup $replyMarkup = null,
        ?int $chatId = null
    ): mixed {
        if (str_starts_with($mimeType, 'image/') && !str_ends_with($mimeType, 'gif')) {
            return $this->sendCachedPhoto($bot, $filePath, $caption, $parseMode, $replyMarkup, $chatId);
        } elseif (str_starts_with($mimeType, 'video/')) {
            return $this->sendCachedVideo($bot, $filePath, $caption, $parseMode, $replyMarkup, $chatId);
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return $this->sendCachedAudio($bot, $filePath, $caption, $parseMode, $replyMarkup, $chatId);
        } else {
            return $this->sendCachedDocument($bot, $filePath, $caption, $parseMode, $replyMarkup, $chatId);
        }
    }
}

