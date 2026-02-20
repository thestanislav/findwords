<?php

namespace ExprAs\Admin\Hydrator\Strategy;

use ExprAs\Uploadable\Entity\Uploaded;

trait UploadedExtractorTrait
{
    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param  mixed       $value  The original value.
     * @param  null|object $object (optional) The original object for context.
     * @return mixed       Returns the value that should be extracted.
     */
    public function extract(mixed $value, ?object $object = null)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($value instanceof \Traversable) {
            $out = [];
            foreach ($value as $_entity) {
                $out[] = $this->_extract($_entity);
            }
            return $out;
        } else {
            return $this->_extract($value);
        }
    }

    public function _extract(Uploaded $uploaded)
    {
        return [
            'name' => $uploaded->getName(),
            'id' => $uploaded->getId(),
            'type' => $uploaded->getMimeType(),
            'src' => '/.admin/uploaded/' . $uploaded->getId()
        ];
    }
}
