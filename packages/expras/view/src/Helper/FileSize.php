<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 09.10.2014
 * Time: 14:41
 */

namespace ExprAs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class FileSize extends AbstractHelper
{
    /**
     * Acceptable prefices of SI
     *
     * @var array
     */
    protected static $_prefixes = ['byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    /**
     * Tranformation to huma-readable format
     *
     * @param  int $size      Size in bytes
     * @param  int $precision Presicion of result (default 2)
     * @return string Transformed size
     */
    public function __invoke($size, $precision = 2)
    {
        $result = $size;
        $index = 0;
        while ($result > 1024 && $index < count(self::$_prefixes)) {
            $result = $result / 1024;
            $index++;
        }

        return sprintf('%1.' . $precision . 'f %s', $result, $this->getView()->translate(self::$_prefixes[$index]));
    }
}
