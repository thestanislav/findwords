<?php

namespace ExprAs\Nutgram\Mezzio\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SergiX44\Nutgram\Nutgram;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;

class WebhookHandler implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;
    private readonly Nutgram $bot;

    public function __construct(Nutgram $bot)
    {
        $this->bot = $bot;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Handle Nutgram updates

        try {
            $this->bot->run();
        } catch (\Throwable $e) {
            $this->getContainer()->get('expras_logger')->error($e->getMessage(), ['exception' => $e]);
            //throw $e;
        }

        // Return a 200 OK response to confirm to Telegram
        return new EmptyResponse(200);
    }
}
