<?php

namespace ExprAs\Nutgram\Mezzio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ExprAs\Nutgram\Entity\DefaultUser;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use ExprAs\Core\Handler\RequestParamsTrait;
class AuthorizeWebAppUser implements MiddlewareInterface
{
    use RequestParamsTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(DefaultUser::class) ?? null;
        if (!$user){
            $acceptHeader = $this->params($request)->fromHeader('Accept');
            if (is_array($acceptHeader) && in_array('application/json', $acceptHeader)){
                return new JsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }
            return new HtmlResponse('<html><body><h1>Forbidden</h1></body></html>', 403);
        }
        return $handler->handle($request);
    }   
}
