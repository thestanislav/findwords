<?php

declare(strict_types=1);

namespace ExprAs\Core\Http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Holds the current PSR-7 request for the duration of request handling.
 * Used by the ServerRequest delegator so the logger can get the request
 * when calling ServerRequestInterface with 0 args under Swoole.
 */
final class CurrentRequestHolder
{
    private ?ServerRequestInterface $request = null;

    public function set(?ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function get(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
