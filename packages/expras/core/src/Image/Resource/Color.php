<?php

namespace ExprAs\Core\Image\Resource;

use ExprAs\Core\Image\Resource;

class Color
{
    protected $_id = null;

    public static $stringCodes = ['Red'          => '#FF0000', 'Turquoise'    => '#00FFFF', 'Light Blue'   => '#0000FF', 'Dark Blue'    => '#0000A0', 'Light Purple' => '#FF0080', 'Dark Purple'  => '#800080', 'Yellow'       => '#FFFF00', 'Pastel Green' => '#00FF00', 'Pink'         => '#FF00FF', 'White'        => '#FFFFFF', 'Light Grey'   => '#C0C0C0', 'Dark Grey'    => '#808080', 'Black'        => '#000000', 'Orange'       => '#FF8040', 'Brown'        => '#804000', 'Gray'         => '#736F6E', 'Burgundy'     => '#800000', 'Forest Green' => '#808000', 'Grass Green'  => '#408080'];

    /**
     * Image resource Object
     *
     * @var Resource
     */
    protected $_imageResource = null;

    protected $_opacity = null;

    /**
     * Class constructor
     *
     * @param mix $colorValue
     */
    public function __construct(Resource $imgResource, $colorValue, $opacity = 0)
    {
        $this->_imageResource = $imgResource;

        if (is_scalar($colorValue) && str_contains($colorValue, '@')) {
            [$colorValue, $_opacity] = explode('@', $colorValue);
            if ($_opacity) {
                $opacity = floatval($_opacity) * 100;
            }
        }

        $this->_opacity = intval(127 / $opacity * 100);

        $match = null;
        if (is_array($colorValue) && count($colorValue) == 3) {
            $this->_parseRGBArray($colorValue);
        } elseif (is_scalar($colorValue) && preg_match('~^#?([0-9A-F]{6})$~i', $colorValue, $match)) {
            $this->_parseHex($match[1]);
        } elseif (is_scalar($colorValue) && preg_match('~^RGB\(([0-9]{1,4},[0-9]{1,4},[0-9]{1,4})\)$~i', $colorValue, $match)) {
            $this->_parseRGBString($match[1]);
        } elseif (is_scalar($colorValue) && array_key_exists(ucfirst($colorValue), self::$stringCodes)) {
            $this->_parseStringCode(self::$stringCodes[ucfirst($colorValue)]);
        } else {
            throw new Exception('Unsupported color value format');
        }
    }

    /**
     *
     *
     * @param  Resource $imgResource
     * @param  int      $opacity
     * @return Color
     */
    public static function create(Resource $imgResource, mixed $colorValue, $opacity = 0)
    {
        return new self($imgResource, $colorValue, $opacity);
    }


    protected function _parseHex($value)
    {
        $this->_parseRGBArray(array_map('hexdec', str_split((string) $value, 2)));
    }

    /**
     * Parses string color code like
     * 'Red', 'Black
     *
     * @param  string $value
     * @return void
     */
    protected function _parseStringCode($value)
    {
        $this->_parseHex(substr($value, 1));
    }

    /**
     * Define a color as transparent
     *
     * @return Color
     */
    public function transparent()
    {
        imagecolortransparent($this->_imageResource->getResource(), $this->_id);
        return $this;
    }


    protected function _parseRGBArray($value)
    {
        array_unshift($value, $this->_imageResource->getResource());
        array_push($value, $this->_opacity);
        $this->_id = call_user_func_array('imagecolorallocatealpha', $value);
    }

    protected function _parseRGBString($value)
    {
        $this->_parseRGBArray(explode(',', (string) $value, 3));
    }

    /**
     * Get allocated color id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Class destructor
     *
     * @return boolean
     */
    public function __destruct()
    {
        return imagecolordeallocate($this->_imageResource->getResource(), $this->_id);
    }
}
