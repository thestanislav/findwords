<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 27.10.2015
 * Time: 14:48
 */

namespace ExprAs\Core\Image;

class Text
{
    protected $_size = 12;

    protected $_angle = 0;

    protected $_fontFile;

    protected $_text;

    protected $_color = '#000000';

    protected $_coordinates = [0, 0];

    protected $_image;

    protected $_extraInfo = [];

    public function __construct(Resource $image)
    {
        $this->setImage($image);
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->_image;
    }

    public function setImage(mixed $image)
    {
        $this->_image = $image;
        return $this;
    }


    public function setOptions($options)
    {
        foreach ($options as $_k => $_v) {
            $this->setOption($_k, $_v);
        }
        return $this;
    }

    public function setOption($name, $value)
    {
        $prop = '_' . $name;
        if (property_exists($this, $prop)) {
            $this->{$prop} = $value;
        }
        return $this;
    }

    public static function createFromText($size, $fontFile, $text, $angle = 0, $extrainfo = [])
    {
        $coord = imageftbbox($size, $angle, $fontFile, (string) $text, $extrainfo);

        $width = abs($coord[4] - $coord[0]) + 6;
        $height = abs($coord[5] - $coord[1]) + 6;

        $image = Resource::createNew($width, $height);
        $textImage = new self($image);
        $textImage->setOptions(
            [
                'size'      => $size,
                'fontFile'  => $fontFile,
                'text'      => $text,
                'angle'     => $angle,
                'extraInfo' => $extrainfo,
                'coordinates' => [abs($coord[0]) + 3, $height - abs($coord[1]) - 3]
            ]
        );
        return $textImage;
    }

    public function write()
    {
        $color = new Resource\Color($this->getImage(), $this->_color);
        $out = imagefttext(
            $this->getImage()->getResource(),
            $this->_size,
            $this->_angle,
            $this->_coordinates['x'] ?? $this->_coordinates[0],
            $this->_coordinates['y'] ?? $this->_coordinates[1],
            $color->getId(),
            $this->_fontFile,
            (string) $this->_text,
            $this->_extraInfo
        );
        return $this;
    }

    public function save($fileName, $type = null, $quality = null)
    {
        $this->getImage()->save($fileName, $type, $quality);
    }
}
