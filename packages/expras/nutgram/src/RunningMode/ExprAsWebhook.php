<?php

namespace ExprAs\Nutgram\RunningMode;

use Psr\Http\Message\ServerRequestInterface;
use SergiX44\Nutgram\RunningMode\Webhook;

class ExprAsWebhook extends Webhook
{
    protected ?ServerRequestInterface $request = null;

    // Setter method to inject the request
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    protected function input(): ?string
    {
        // Use getBody()->getContents() to retrieve the raw request content
        return strval($this->request->getBody());
    }
}
