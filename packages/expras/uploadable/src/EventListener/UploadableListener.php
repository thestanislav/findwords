<?php

namespace ExprAs\Uploadable\EventListener;

use ExprAs\Uploadable\MimeTypeGuesser;
use Gedmo\Exception\UploadableCantWriteException;
use Gedmo\Exception\UploadableExtensionException;
use Gedmo\Exception\UploadableFileAlreadyExistsException;
use Gedmo\Exception\UploadableFormSizeException;
use Gedmo\Exception\UploadableIniSizeException;
use Gedmo\Exception\UploadableNoFileException;
use Gedmo\Exception\UploadableNoTmpDirException;
use Gedmo\Exception\UploadablePartialException;
use Gedmo\Exception\UploadableUploadException;
use Gedmo\Mapping\Event\AdapterInterface;
use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Gedmo\Uploadable\MimeType\MimeTypeGuesserInterface;
use Gedmo\Uploadable\UploadableListener as GedmoUploadableListener;
use Doctrine\Common\EventArgs;

class UploadableListener extends GedmoUploadableListener
{
    protected $_guesser;

    protected $_fileInfoObjectsInjectQueue = [];


    public function __construct(?MimeTypeGuesserInterface $mimeTypeGuesser = null)
    {
        parent::__construct($this->_guesser = new MimeTypeGuesser());
    }

    public function addFileInfoObjectInjectQueue(object $entity, FileInfoInterface $fileInfo)
    {
        $this->_fileInfoObjectsInjectQueue[] = ['entity' => $entity, 'fileInfo' => $fileInfo];
    }

    #[\Override]
    public function preFlush(EventArgs $args)
    {
       

        // check if entities is queued for inject or update then inject file info object
        foreach ($this->_fileInfoObjectsInjectQueue as $key => $fileInfoObjectInjectQueue) {
            $this->addEntityFileInfo($fileInfoObjectInjectQueue['entity'], $fileInfoObjectInjectQueue['fileInfo']);
            unset($this->_fileInfoObjectsInjectQueue[$key]);
        }
       
        parent::preFlush($args);
    }


    /**
     * If it's a Uploadable object, verify if the file was uploaded.
     * If that's the case, process it.
     *
     * @param \Gedmo\Mapping\Event\AdapterInterface $ea
     * @param object                                $object
     * @param string                                $action
     *
     * @throws \Gedmo\Exception\UploadableNoPathDefinedException
     * @throws \Gedmo\Exception\UploadableCouldntGuessMimeTypeException
     * @throws \Gedmo\Exception\UploadableMaxSizeException
     * @throws \Gedmo\Exception\UploadableInvalidMimeTypeException
     */
    #[\Override]
    public function processFile(AdapterInterface $ea, $object, $action)
    {
        $this->_guesser->setUploaded($object);
        parent::processFile($ea, $object, $action);
    }

    /**
     * Moves the file to the specified path
     *
     * @param FileInfoInterface $fileInfo
     * @param string            $path
     * @param bool              $filenameGeneratorClass
     * @param bool              $overwrite
     * @param bool              $appendNumber
     * @param object            $object
     *
     * @return array
     *
     * @throws \Gedmo\Exception\UploadableUploadException
     * @throws \Gedmo\Exception\UploadableNoFileException
     * @throws \Gedmo\Exception\UploadableExtensionException
     * @throws \Gedmo\Exception\UploadableIniSizeException
     * @throws \Gedmo\Exception\UploadableFormSizeException
     * @throws \Gedmo\Exception\UploadableFileAlreadyExistsException
     * @throws \Gedmo\Exception\UploadablePartialException
     * @throws \Gedmo\Exception\UploadableNoTmpDirException
     * @throws \Gedmo\Exception\UploadableCantWriteException
     */
    #[\Override]
    public function moveFile(FileInfoInterface $fileInfo, $path, $filenameGeneratorClass = false, $overwrite = false, $appendNumber = false, $object = null)
    {
        if ($fileInfo->getError() > 0) {
            switch ($fileInfo->getError()) {
            case 1:
                $msg = 'Size of uploaded file "%s" exceeds limit imposed by directive "upload_max_filesize" in php.ini';

                throw new UploadableIniSizeException(sprintf($msg, $fileInfo->getName()));
            case 2:
                $msg = 'Size of uploaded file "%s" exceeds limit imposed by option MAX_FILE_SIZE in your form.';

                throw new UploadableFormSizeException(sprintf($msg, $fileInfo->getName()));
            case 3:
                $msg = 'File "%s" was partially uploaded.';

                throw new UploadablePartialException(sprintf($msg, $fileInfo->getName()));
            case 4:
                $msg = 'No file was uploaded!';

                throw new UploadableNoFileException(sprintf($msg, $fileInfo->getName()));
            case 6:
                $msg = 'Upload failed. Temp dir is missing.';

                throw new UploadableNoTmpDirException($msg);
            case 7:
                $msg = 'File "%s" couldn\'t be uploaded because directory is not writable.';

                throw new UploadableCantWriteException(sprintf($msg, $fileInfo->getName()));
            case 8:
                $msg = 'A PHP Extension stopped the uploaded for some reason.';

                throw new UploadableExtensionException(sprintf($msg, $fileInfo->getName()));
            default:
                throw new UploadableUploadException(
                    sprintf(
                        'There was an unknown problem while uploading file "%s"',
                        $fileInfo->getName()
                    )
                );
            }
        }

        $info = ['fileName' => '', 'fileExtension' => '', 'fileWithoutExt' => '', 'origFileName' => '', 'filePath' => '', 'fileMimeType' => $fileInfo->getType(), 'fileSize' => $fileInfo->getSize()];

        $info['fileName'] = basename((string) $fileInfo->getName());
        $info['filePath'] = $path . '/' . $info['fileName'];

        $hasExtension = strrpos($info['fileName'], '.');

        if ($hasExtension) {
            $info['fileExtension'] = substr($info['filePath'], strrpos($info['filePath'], '.'));
            $info['fileWithoutExt'] = substr($info['filePath'], 0, strrpos($info['filePath'], '.'));
        } else {
            $info['fileWithoutExt'] = $info['fileName'];
        }

        // Save the original filename for later use
        $info['origFileName'] = $info['fileName'];

        // Now we generate the filename using the configured class
        if ($filenameGeneratorClass) {
            $filename = $filenameGeneratorClass::generate(
                str_replace($path . '/', '', $info['fileWithoutExt']),
                $info['fileExtension'],
                $object
            );
            $info['filePath'] = str_replace(
                '/' . $info['fileName'],
                '/' . implode('/', str_split(substr((string) $filename, 0, 4), 2)) . '/' . $filename,
                $info['filePath']
            );
            if (!is_dir(dirname($info['filePath']))) {
                mkdir(dirname($info['filePath']), 0777, true);
            }
            //$info['fileName'] = $filename;

            if ($pos = strrpos($info['filePath'], '.')) {
                // ignores positions like "./file" at 0 see #915
                $info['fileWithoutExt'] = substr($info['filePath'], 0, $pos);
            } else {
                $info['fileWithoutExt'] = $info['filePath'];
            }
        }

        if (is_file($info['filePath'])) {
            if ($overwrite) {
                $this->cancelFileRemoval($info['filePath']);
                $this->removeFile($info['filePath']);
            } elseif ($appendNumber) {
                $counter = 1;
                $info['filePath'] = $info['fileWithoutExt'] . '-' . $counter . $info['fileExtension'];

                do {
                    $info['filePath'] = $info['fileWithoutExt'] . '-' . (++$counter) . $info['fileExtension'];
                } while (is_file($info['filePath']));
            } else {
                throw new UploadableFileAlreadyExistsException(
                    sprintf(
                        'File "%s" already exists!',
                        $info['filePath']
                    )
                );
            }
        }

        try {

            $fileInfo->getUploadedFile()->moveTo($info['filePath']);
            return $info;

        } catch (\Throwable) {
            throw new UploadableUploadException(
                sprintf(
                    'File "%s" was not uploaded, or there was a problem moving it to the location "%s".',
                    $fileInfo->getName(),
                    $path
                )
            );
        }
    }
}
