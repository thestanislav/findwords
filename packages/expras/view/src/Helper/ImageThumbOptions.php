<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 09.03.13
 * Time: 22:53
 */

namespace ExprAs\View\Helper;

use Laminas\Stdlib\AbstractOptions;

class ImageThumbOptions extends AbstractOptions
{
    protected $__strictMode__ = false;
    /**
     * Relative cache directory
     *
     * @var string
     */
    protected $_cacheDirectory = null;

    protected $_cachePath = '/cache/';

    protected $_hashedDirLevel = 2;

    protected $_outputFormat = 'auto';

    protected $_throwExceptions = false;

    protected $_setDimensionsAttributes = true;

    protected $_verticalCropFactor = 4;

    public function __construct($options = [])
    {
        if (!isset($options['cacheDirectory'])) {
            $options['cacheDirectory'] = $_SERVER['DOCUMENT_ROOT'] . '/cache/';
        }
        parent::__construct($options);

    }

    /**
     * @param string $cacheDirectory
     */
    public function setCacheDirectory($cacheDirectory)
    {
        $this->_cacheDirectory = $cacheDirectory;
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->_cacheDirectory;
    }

    public function setCachePath($cachePath)
    {
        $this->_cachePath = $cachePath;
    }

    public function getCachePath()
    {
        return $this->_cachePath;
    }

    public function setHashedDirLevel($hashedDirLevel)
    {
        $this->_hashedDirLevel = $hashedDirLevel;
    }

    public function getHashedDirLevel()
    {
        return $this->_hashedDirLevel;
    }

    public function setOutputFormat($outputFormat)
    {
        $this->_outputFormat = $outputFormat;
    }

    public function getOutputFormat()
    {
        return $this->_outputFormat;
    }

    public function setThrowExceptions($throwExceptions)
    {
        $this->_throwExceptions = $throwExceptions;
    }

    public function getThrowExceptions()
    {
        return $this->_throwExceptions;
    }

    /**
     * @return int
     */
    public function getVerticalCropFactor()
    {
        return $this->_verticalCropFactor;
    }

    /**
     * @param int $verticalCropFactor
     */
    public function setVerticalCropFactor($verticalCropFactor)
    {
        $this->_verticalCropFactor = $verticalCropFactor;
    }

    /**
     * @return bool
     */
    public function isSetDimensionsAttributes(): bool
    {
        return $this->_setDimensionsAttributes;
    }

    /**
     * @param bool $setDimentionAttributes
     */
    public function setSetDimensionsAttributes(bool $setDimentionAttributes): void
    {
        $this->_setDimensionsAttributes = $setDimentionAttributes;
    }


}
