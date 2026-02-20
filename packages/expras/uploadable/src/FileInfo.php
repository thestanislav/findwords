<?php

namespace ExprAs\Uploadable;

use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Laminas\Diactoros\UploadedFile;

class FileInfo implements FileInfoInterface
{
    protected $_uploadedFile;


    public function __construct(UploadedFile $uploadedFile)
    {
        $this->_uploadedFile = $uploadedFile;
    }

    public function getTmpName()
    {

    }

    public function getName()
    {
        return $this->_uploadedFile->getClientFilename();
    }

    public function getSize()
    {
        return $this->_uploadedFile->getSize();
    }

    public function getType()
    {
        return $this->_uploadedFile->getClientMediaType();
    }

    public function getError()
    {
        return $this->_uploadedFile->getError();
    }

    /**
     * This method must return true if the file is coming from $_FILES, or false instead.
     *
     * @return bool
     */
    public function isUploadedFile()
    {
        return false;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile(): UploadedFile
    {
        return $this->_uploadedFile;
    }



}
