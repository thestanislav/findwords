<?php

namespace ExprAs\Core\Serializer;

use Laminas\Serializer\Adapter\AbstractAdapter;
use Laminas\Serializer\Adapter\AdapterOptions;
use Laminas\Serializer\Adapter\Json;
use Laminas\Serializer\Exception;

class Gzip extends Json
{
    /**
     * Set options
     *
     * @param  array|\Traversable|GzipOptions $options
     * @return Json
     */
    public function setOptions($options)
    {
        if (! $options instanceof GzipOptions) {
            $options = new GzipOptions($options);
        }

        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return GzipOptions
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = new GzipOptions();
        }

        return $this->options;
    }

    /**
     * @param $value
     *
     * @return false|string
     */
    public function serialize($value)
    {
        return gzcompress((string) parent::serialize($value), $this->getOptions()->getCompressionLevel(), $this->getOptions()->getEncoding());
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function unserialize($data)
    {
        if (!($_data = @gzuncompress($data))) {
            return null;
        }
        return parent::unserialize($_data);
    }
}
