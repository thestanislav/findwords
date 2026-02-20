<?php

namespace ExprAs\Core\ServiceManager\Factory;

use ExprAs\Core\Helper\BodyParams\MultipartFormDataParser;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;

class BodyParamsStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $bodyParams = new BodyParamsMiddleware();
        $bodyParams->addStrategy(new MultipartFormDataParser());
        return $bodyParams;
    }
}
