<?php

namespace ExprAs\Nutgram\Mixins;

use Closure;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Media\File;

class NutgramMixin
{
    /**
     * Download file to a specific path
     */
    public function downloadFileToPath(): Closure
    {
        return function (File $file, string $path, array $clientOpt = []): bool {
            /** @var Nutgram $this */
            try {
                $content = $this->downloadFile($file, $clientOpt);
                return file_put_contents($path, $content) !== false;
            } catch (\Exception) {
                return false;
            }
        };
    }

    /**
     * Send message with inline keyboard
     */
    public function sendMessageWithKeyboard(): Closure
    {
        return function (string $text, array $keyboard, array $options = []): mixed {
            /** @var Nutgram $this */
            $options['reply_markup'] = $keyboard;
            return $this->sendMessage($text, $options);
        };
    }

    /**
     * Edit message with inline keyboard
     */
    public function editMessageWithKeyboard(): Closure
    {
        return function (string $text, array $keyboard, array $options = []): mixed {
            /** @var Nutgram $this */
            $options['reply_markup'] = $keyboard;
            return $this->editMessageText($text, $options);
        };
    }

    /**
     * Answer callback query with notification
     */
    public function answerCallbackWithNotification(): Closure
    {
        return function (string $text, bool $showAlert = false, ?string $url = null): mixed {
            /** @var Nutgram $this */
            return $this->answerCallbackQuery($text, $showAlert, $url);
        };
    }
}