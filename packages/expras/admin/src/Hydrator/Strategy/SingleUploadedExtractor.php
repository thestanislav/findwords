<?php

namespace ExprAs\Admin\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

class SingleUploadedExtractor implements StrategyInterface
{
    use UploadedExtractorTrait;


    public function hydrate($value, ?array $data)
    {
        return $value ?: null;
    }
}
