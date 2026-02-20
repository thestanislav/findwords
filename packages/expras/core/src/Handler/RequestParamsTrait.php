<?php
namespace ExprAs\Core\Handler;

use Psr\Http\Message\ServerRequestInterface;

trait RequestParamsTrait
{

    public function params(ServerRequestInterface $request): AbstractActionParams
    {
        return new AbstractActionParams($request);
    }

}