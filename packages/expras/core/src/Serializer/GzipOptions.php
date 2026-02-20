<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */

declare(strict_types=1);

namespace ExprAs\Core\Serializer;

use Laminas\Serializer\Adapter\JsonOptions;

class GzipOptions extends JsonOptions
{
    protected $compressionLevel = 6;

    protected $encoding = ZLIB_ENCODING_DEFLATE;

    /**
     * @return int
     */
    public function getCompressionLevel(): int
    {
        return $this->compressionLevel;
    }

    public function setCompressionLevel(int $compressionLevel): void
    {
        $this->compressionLevel = $compressionLevel;
    }

    /**
     * @return int
     */
    public function getEncoding(): int
    {
        return $this->encoding;
    }

    public function setEncoding(int $encoding): void
    {
        $this->encoding = $encoding;
    }



}
