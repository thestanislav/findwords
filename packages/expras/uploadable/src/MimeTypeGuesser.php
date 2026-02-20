<?php

namespace ExprAs\Uploadable;

use ExprAs\Uploadable\Entity\Uploaded;
use Gedmo\Uploadable\MimeType\MimeTypeGuesserInterface;

class MimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * @var Uploaded 
     */
    protected $_uploaded;

    /**
     * @return Uploaded
     */
    public function getUploaded(): Uploaded
    {
        return $this->_uploaded;
    }

    public function setUploaded(Uploaded $uploaded): void
    {
        $this->_uploaded = $uploaded;
    }




    public function guess($filePath)
    {
        return $this->_uploaded->getMimeType();
    }

}
