<?php

namespace ExprAs\Admin\Hydrator\Strategy;

use ExprAs\Doctrine\Hydrator\Strategy\AllowRemoveByValue;

class CollectionUploadedExtractor extends AllowRemoveByValue
{
    use UploadedExtractorTrait;

}
