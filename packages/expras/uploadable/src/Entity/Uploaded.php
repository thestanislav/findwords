<?php

namespace ExprAs\Uploadable\Entity;

use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Diactoros\UploadedFile;

/**
 * Class Uploaded
 */
#[ORM\Table(name: 'expras_uploaded_files')]
#[Gedmo\Uploadable(allowOverwrite: false, appendNumber: true, path: 'data/uploaded', filenameGenerator: 'SHA1')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class Uploaded extends AbstractEntity
{
    use TimestampableTrait;

    protected bool $_removeFileOnDestruct = false;


    public function __construct(protected UploadedFile $_uploadedFile)
    {
        $this->name = $this->_uploadedFile->getClientFilename();
        $this->mimeType = $this->_uploadedFile->getClientMediaType();
        $this->size = $this->_uploadedFile->getSize();
    }

    #[ORM\Column]
    #[Gedmo\UploadableFilePath]
    protected ?string $path = null;

    #[ORM\Column]
    #[Gedmo\UploadableFileName]
    protected ?string $name = null;

    #[ORM\Column(name: 'mime_type')]
    #[Gedmo\UploadableFileMimeType]
    protected ?string $mimeType = null;

    #[ORM\Column(type: 'decimal')]
    #[Gedmo\UploadableFileSize]
    protected ?string $size = null;

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    public function setPath(mixed $path): void
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName(mixed $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->mimeType ?? $this->_uploadedFile->getClientMediaType();
    }

    public function setMimeType(mixed $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    public function setSize(mixed $size): void
    {
        $this->size = $size;
    }

    #[ORM\PreRemove]
    public function _unlinkFile()
    {
        $this->_removeFileOnDestruct = true;
    }

    public function __destruct()
    {
        $this->_removeFileOnDestruct && file_exists($this->path) && @unlink($this->path);
    }

    public function getMd5Hash(): ?string
    {
        if ($this->_uploadedFile && $this->_uploadedFile->getError() === UPLOAD_ERR_OK) {
            return $this->_calculateUploadedFileMd5();
        } elseif ($this->path && is_file($this->path)) {
            return md5_file($this->path);
        } else {
            return null;
        }
    }

    protected function _calculateUploadedFileMd5(): ?string
    {
        if ($this->_uploadedFile && $this->_uploadedFile->getError() === UPLOAD_ERR_OK) {
            return md5($this->_uploadedFile->getStream()->getContents());
        }

        return null;
    }
}
