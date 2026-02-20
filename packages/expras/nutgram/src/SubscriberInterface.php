<?php

namespace ExprAs\Nutgram;

use SergiX44\Nutgram\Nutgram;

/**
 * Interface for handlers that want to subscribe to Nutgram events
 */
interface SubscriberInterface
{
    /**
     * Subscribe to Nutgram events
     *
     * @param Nutgram $bot The Nutgram bot instance
     */
    public function subscribeToEvents(Nutgram $bot): void;
}
