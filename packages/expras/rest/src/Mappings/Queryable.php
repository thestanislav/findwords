<?php

namespace ExprAs\Rest\Mappings;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Queryable
{
    public function __construct(
        /**
         * @var      array<string>|null
         * @readonly
         * /
         */
        protected array $fields = []
    )
    {
    }

    public function getFields(): array
    {
        return $this->fields;
    }



}
