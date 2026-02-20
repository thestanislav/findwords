<?php

namespace ExprAs\Core\Image;

use Laminas\Http\Client;

class Resource implements \Stringable
{
    final public const int TYPEGIF = IMAGETYPE_GIF;
    final public const int TYPEJPEG = IMAGETYPE_JPEG;
    final public const int TYPEPNG = IMAGETYPE_PNG;


    final public const int RESIZEMODE_PERCENT = 1;
    final public const int RESIZEMODE_SETWIDTH = 2;
    final public const int RESIZEMODE_DEFAULT = 2;
    final public const int RESIZEMODE_SETHEIGHT = 3;
    final public const int RESIZMODE_ADDWIDTH = 4;
    final public const int RESIZEMODE_ADDHEIGHT = 5;

    /**
     * @var resource
     **/
    protected $_res = null;

    protected static $name2type
        = ['GIF'  => IMAGETYPE_GIF, 'JPEG' => IMAGETYPE_JPEG, 'JPG'  => IMAGETYPE_JPEG, 'PNG'  => IMAGETYPE_PNG, 'BMP'  => IMAGETYPE_BMP];

    /**
     * Class constructor
     * Create new image resource object from image resource
     *
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->resetResource($resource);
    }

    /**
     * Create object from a file
     *
     * @param string $fileName
     *
     * @return \ExprAs\Core\Image\Resource
     */
    public static function createFromFile($fileName)
    {
        if (!file_exists($fileName) || !is_file($fileName)) {
            self::throwException('Specified ' . $fileName . ' image file do not exsists.');
        }

        if (false == ($imageInfo = getimagesize($fileName))) {
            self::throwException('Invalid type of image or image type is not supported.');
        }

        if (!isset($imageInfo[2]) || !in_array($imageInfo[2], self::$name2type)) {
            self::throwException('Invalid type of image or image type is not supported.');
        }

        $typeName = array_search($imageInfo[2], self::$name2type);

        return new self(call_user_func_array('imagecreatefrom' . strtolower((string) $typeName), [$fileName]));
    }

    /**
     * Enter description here...
     *
     * @param string $data
     *
     * @return \ExprAs\Core\Image\Resource
     */
    public static function createFromString($data)
    {

        $im = imagecreatefromstring($data);
        if ($im === false) {
            self::throwException('Image type is unsupported or the data is not in a recognised format');
        }
        return new self($im);
    }


    /**
     * Tries to create Image resource from url
     *
     * @param string $url
     *
     * @return \ExprAs\Core\Image\Resource
     */
    public static function createFromUrl($url)
    {
        if (false == ($content = @file_get_contents($url))) {
            $request = new Client(
                $url,
                ['maxredirects' => 2, 'sslcapath'    => '/etc/ssl/certs']
            );
            $response = $request->send();
            if ($response->getStatusCode() != 200) {
                self::throwException('Could not fetch image from url');
            }
            $content = $response->getBody();
        }
        return self::createFromString($content);
    }

    /**
     * Get image resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->_res;
    }

    /**
     * Reset gd resource
     *
     * @param resource $image Image resource
     *
     * @return Resource
     */
    public function resetResource($image)
    {
        if ((is_resource($image) && get_resource_type($image) == 'gd') || $image instanceof \GdImage) {
            if (is_resource($this->_res)) {
                imagedestroy($this->_res);
            }
            $this->_res = $image;
        } else {
            self::throwException('Resource gd type is required, resource of ' . get_resource_type($image) . ' is given');
        }
        return $this;
    }


    /**
     * Tries to autocreate image by source detecting
     *
     * @return \ExprAs\Core\Image\Resource
     */
    public static function create(mixed $source)
    {

        if (is_resource($source) && get_resource_type($source) == 'gd') {
            $out = new self($source);
        } elseif (preg_match('~^http(s)?://~i', (string) $source)) {
            $out = self::createFromUrl($source);
        } elseif (is_string($source)) {
            if (is_file($source) && file_exists($source)) {
                $out = self::createFromFile($source);
            } else {
                $out = self::createFromString($source);
            }
        }
        return $out;
    }

    /**
     * Creates new image
     *
     * @param array $dimentions
     *
     * @return Resource
     */
    public static function createNew($dimensions)
    {
        $args = func_get_args();
        if (count($args) == 2 && is_numeric($args[0]) && is_numeric($args[1])) {
            $dimensions = $args;
        }

        if (!is_array($dimensions)) {
            self::throwException('Invalid parameters specified');
        }

        return new self(call_user_func_array('imagecreatetruecolor', array_values($dimensions)));
    }

    /**
     * Get an image width
     *
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->_res);
    }

    /**
     * Gets image height
     *
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->_res);
    }

    /**
     * Get width and heigh of the image
     *
     * @return array
     */
    public function getDimensions()
    {
        $x = $this->getWidth();
        $y = $this->getHeight();
        return [$x, $y, 'width' => $x, 'height' => $y];
    }

    /**
     * Set the blending mode for an image
     *
     * @param boolean $mode
     *
     * @return Resource
     */
    public function alphablending($mode)
    {
        imagealphablending($this->_res, $mode);
        return $this;
    }

    /**
     * Set antialias mode
     *
     * @param boolean $on
     *
     * @return Resource
     */
    public function antialias($on)
    {
        imageantialias($this->_res, $on);
        return $this;
    }

    /**
     *
     * @param boolean $on
     *
     * @return Resource
     */
    public function savealpha($on)
    {
        imagesavealpha($this->_res, $on);
        return $this;
    }

    /**
     * Rotate an image with a given angle
     *
     * @param float $angle              Rotation angle, in degrees.
     * @param int   $bgd_color          Specifies the color of the uncovered zone after the rotation
     * @param int   $ignore_transparent If set and non-zero, transparent colors are ignored (otherwise kept).
     *
     * @return Resource
     */
    public function rotate($angle, $bgd_color, $ignore_transparent = null)
    {
        $color = new Resource\Color($this, $bgd_color);
        $rotated = imagerotate($this->_res, $angle, $color->getId());

        $this->resetResource($rotated);
        return $this;
    }

    // {{{ resize()
    /**
     * Tries to resize image
     *
     * @param resize    value $value
     * @param int             $mode
     * @param boolean         $preserve Wheather preserve image proportions
     *
     * @return Resource
     */
    public function resize($value, $mode = self::RESIZEMODE_DEFAULT, $preserve = true)
    {
        [$origW, $origH] = $this->getDimensions();

        switch ($mode) {
        case self::RESIZEMODE_PERCENT:
            $newW = intval($origW * $value / 100);
            break;

        case self::RESIZEMODE_SETHEIGHT:
            $newH = $value;
            break;

        case self::RESIZEMODE_SETWIDTH:
            $newW = $value;
            break;

        case self::RESIZEMODE_ADDHEIGHT:
            $newH = $origH + $value;
            break;

        case self::RESIZMODE_ADDWIDTH:
            $newW = $origW + $value;
            break;

        default:
            self::throwException('Unsupported resize mode');
            break;
        }

        if (!isset($newW) && $preserve === true) {
            $newW = intval($newH / $origH * $origW);
        } elseif (!isset($newH) && $preserve === true) {
            $newH = intval($newW / $origW * $origH);
        } elseif (!isset($newW)) {
            $newW = $origW;
        } elseif (!isset($newH)) {
            $newH = $origH;
        }

        $newImRs = Resource::createNew($newW, $newH);
        $newImRs->alphablending(true)
            ->savealpha(true)
            ->fillColor('#ffffff', 100);
        imagecopyresampled($newImRs->getResource(), $this->_res, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $this->resetResource($newImRs->getResource());
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param  int $opacity
     * @return Resource
     */
    public function fillColor(mixed $color, $opacity = 0)
    {
        if (!$color instanceof Resource\Color && is_string($color)) {
            $color = new Resource\Color($this, $color, $opacity);
        }
        imagefill($this->_res, 0, 0, $color->getId());
        return $this;
    }

    /**
     * Creates new image from a part of the image
     *
     * @param int $coordX X coordinate
     * @param int $coordY Y coordinate
     * @param int $width  copy width
     * @param int $height copy heihgt
     *
     * @return Resource
     */
    public function copy($coordX = 0, $coordY = 0, $width = null, $height = null)
    {
        $width = is_null($width) ? $this->getWidth() - $coordX : $width;
        $height = is_null($height) ? $this->getHeight() - $coordY : $height;
        $destRes = self::createNew([$width, $height]);
        imagecopy($destRes->getResource(), $this->_res, 0, 0, $coordX, $coordY, $width, $height);
        return $destRes;
    }


    protected function _place(Resource $source, $cx, $cy, $pct = null, $gray = false)
    {

        if ($cx == 'left') {
            $cx = 0;
        } elseif ($cx == 'right') {
            $cx = $this->getWidth() - $source->getWidth();
        } elseif ($cx == 'middle') {
            $cx = $this->getWidth() / 2 - $source->getWidth() / 2;
        } elseif ($cx < 0) {
            $cx = $this->getWidth() - $source->getWidth() + $cx;
        } elseif (!is_numeric($cx)) {
            self::throwException('Invalid paramter is passed');
        }


        if ($cy == 'top') {
            $cy = 0;
        } elseif ($cy == 'bottom') {
            $cy = $this->getHeight() - $source->getHeight();
        } elseif ($cy == 'middle') {
            $cy = $this->getHeight() / 2 - $source->getHeight() / 2;
        } elseif ($cy < 0) {
            $cy = $this->getHeight() - $source->getHeight() + $cy;
        } elseif (!is_numeric($cy)) {
            self::throwException('Invalid paramter is passed');
        }

        $args = [$this->_res, $source->getResource(), $cx, $cy, 0, 0, $source->getWidth(), $source->getHeight()];

        $fnc = 'imagecopy';
        if ($gray == true) {
            $fnc = 'imagecopymergegray';
            $args[] = (int)$pct;
        } elseif ($pct !== null) {
            $fnc = 'imagecopymerge';
            $args[] = (int)$pct;
        }

        if (!call_user_func_array($fnc, $args)) {
            self::throwException('Error occured while placing the image');
        }

        return $this;
    }


    /**
     * Places the image on the current image by the given coordinates
     *
     * @param  Resource $source
     * @param  int      $cx
     * @param  int      $cy
     * @params int pct implements alpha transparency for true colour images
     *
     * @return Resource
     */
    public function place(Resource $source, $cx, $cy)
    {
        return $this->_place($source, $cx, $cy);
    }

    /**
     * Places the image on the current image by the given coordinates  and merge
     *
     * @param  Resource $source
     * @param  int      $cx
     * @param  int      $cy
     * @params int pct implements alpha transparency for true colour images
     *
     * @return Resource
     */
    public function placeMerge(Resource $source, $cx, $cy, $pct = 100)
    {
        return $this->_place($source, $cx, $cy, $pct);
    }

    /**
     * Places the image on the current image by the given coordinates with gray scale
     *
     * @param  Resource $source
     * @param  int      $cx
     * @param  int      $cy
     * @params int pct implements alpha transparency for true colour images
     *
     * @return Resource
     */
    public function placeMergeGray(Resource $source, $cx, $cy, $pct)
    {
        return $this->_place($source, $cx, $cy, $pct, true);
    }

    /**
     * Enter description here...
     *
     * @param int    $type
     * @param string $fileName
     *
     * @return unknown
     */
    public function _savedisplay($type = self::TYPEJPEG, $fileName = null, $quality = null)
    {
        if (!($typeExt = array_search($type, self::$name2type, false))) {
            self::throwException('Unsupported image type');
        }

        if (is_null($quality)) {
            $quality = 90;
        }

        $args = array_fill(0, 2, null);
        $args[0] = $this->_res;
        if ($fileName) {
            $args[1] = $fileName;
        }

        if ($type == self::TYPEJPEG && $quality) {
            $args[2] = $quality;
        }

        return call_user_func_array('image' . $typeExt, $args);
    }

    /**
     * Saves the image resurce to the file
     *
     * @param string $fileName
     * @param int    $type
     *
     * @return boolean
     */
    public function save($fileName, $type = null, $quality = null)
    {
        if (!$type) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if (!isset(self::$name2type[strtoupper($ext)])) {
                self::throwException('Output image type is not specified');
            }
            $type = self::$name2type[strtoupper($ext)];
        }

        if (!is_dir($dn = dirname($fileName))) {
            if (!mkdir($dn, 0777, true)) {
                self::throwException('Could not create image file path');
            }
        }
        return $this->_savedisplay($type, $fileName, $quality);
    }

    // {{{ display()
    /**
     * Displays image
     *
     * @param string $fileName Saves to file if set
     */
    public function display($type = self::TYPEJPEG, $quality = null)
    {
        if (is_null($type)) {
            $type = self::TYPEJPEG;
        } elseif (!is_numeric($type)) {
            if (isset(self::$name2type[strtoupper((string) $type)])) {
                $type = self::$name2type[strtoupper((string) $type)];
            } else {
                $type = self::TYPEJPEG;
            }
        }
        $this->_savedisplay($type, null, $quality);
    }

    // }}}
    /**
     * Tries to fetch image content
     *
     * @param int $type
     *
     * @return string
     */
    public function toString($type = self::TYPEJPEG, $quality = null)
    {
        ob_start();
        $this->display($type, $quality);
        return ob_get_clean();
    }


    /**
     * Throw an exception
     **/
    protected static function throwException($message, $code = null): never
    {
        throw new Exception($message, $code);
    }

    /**
     * Get image string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    public function __clone()
    {
        return $this->copy();
    }

    public function __call($method, $args)
    {

        if (!isset(self::$name2type[strtoupper((string) $method)])) {
            self::throwException('Called to unknown method ' . $method);
        }

        return $this->_savedisplay(self::$name2type[strtoupper((string) $method)], (is_countable($args) ? count($args) : 0) != 0);
    }

    /**
     * Class destructor
     */
    /*public function __destruct() {
        if (is_resource($this->_res)){
            //imagedestroy($this->_res);
        }
    }*/

}
