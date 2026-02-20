<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\RunningMode;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\RunningMode;

/**
 * No-op running mode for CLI. Used when the bot is created in CLI (e.g. hook:set, hook:remove).
 * processUpdates() is never called in CLI; the bot is only used for API calls (setWebhook, deleteWebhook, etc.).
 */
class CliRunningMode implements RunningMode
{
    public function processUpdates(Nutgram $bot): void
    {
        // No-op: CLI does not process webhook updates
    }
}
