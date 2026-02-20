<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 09.03.13
 * Time: 22:27
 */

namespace ExprAs\View\Helper;

use ExprAs\Core\Image\Resource as ImageResource;
use Laminas\Stdlib\Exception;
use Laminas\View\Helper\AbstractHelper;

class ImageThumb extends AbstractHelper
{
    protected $_availImageTypes = ['gif', 'jpg', 'png'];

    protected $_options = null;

    /**
     * @return ImageThumbOptions
     */
    public function getOptions()
    {
        if (!($this->_options instanceof ImageThumbOptions)) {
            $this->_options = new ImageThumbOptions();
        }
        return $this->_options;
    }


    public function __invoke($filename = null, $params = [])
    {

        if (!$filename) {
            return $this;
        }

        if (is_array($filename)) {
            $this->getOptions()->setFromArray($filename);
            return $this;
        }

        return $this->render($filename, $params);
    }

    public function render($filename, $params = [])
    {

        $filename = urldecode((string) $filename);
        if (!is_file($filename) && !str_starts_with($filename, 'http') && !str_starts_with($filename, '//')) {
            if (!$this->getOptions()->getThrowExceptions()) {
                return '';
            }
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Provided image %s could not be found',
                    $filename
                )
            );
        }

        $this->getOptions()->setFromArray($params);

        $cacheParams = array_intersect_key(
            array_merge($this->getOptions()->toArray(), ['filename' => $filename], $params),
            array_flip(['width', 'height', 'filename', 'cache_directory'])
        );

        $outputFormat = $this->getOptions()->getOutputFormat();
        if ($outputFormat == 'auto') {
            $outputFormat = pathinfo($filename, PATHINFO_EXTENSION);
            if (!$outputFormat) {
                if ($info = getimagesize($filename)) {
                    $info = explode('/', $info['mime']);
                    $outputFormat = $info[1];
                }
            }

            if (!in_array($outputFormat, $this->_availImageTypes)) {
                $outputFormat = 'jpg';
            }
        }

        $cachedFileName = md5(serialize($cacheParams) . @filemtime($filename)) . '.' . strtolower((string) $outputFormat);

        $cachedFileName = implode('/', str_split(substr($cachedFileName, 0, $this->getOptions()->getHashedDirLevel() * 2), 2)) . '/' . $cachedFileName;
        $cacheDir = dirname($this->getOptions()->getCacheDirectory() . $cachedFileName);
        if (!is_dir($cacheDir)) {
            $this->mkdir($cacheDir);
        }

        try {
            if (!is_file($this->getOptions()->getCacheDirectory() . $cachedFileName)) {

                if (isset($params['width']) || isset($params['height'])) {
                    if (str_starts_with($filename, 'http') || str_starts_with($filename, '//')) {
                        $image = ImageResource::createFromUrl($filename);
                    } else {
                        $image = ImageResource::createFromFile($filename);
                    }

                    if (isset($params['width']) && $params['width'] && isset($params['height']) && $params['height']) {
                        $h = $image->getHeight();
                        $w = $image->getWidth();
                        if ($w / $h > $params['width'] / $params['height']) {
                            $image->resize($params['height'], ImageResource::RESIZEMODE_SETHEIGHT);
                            $image = $image->copy(
                                intval(($image->getWidth() - $params['width']) / 2),
                                0,
                                $params['width'],
                                $params['height']
                            );
                        } else {
                            $image->resize($params['width'], ImageResource::RESIZEMODE_SETWIDTH);
                            $image = $image->copy(
                                0,
                                intval(($image->getHeight() - $params['height']) / $this->getOptions()->getVerticalCropFactor()),
                                $params['width'],
                                $params['height']
                            );
                        }

                    } elseif (isset($params['width']) && $params['width']) {
                        $image->resize($params['width']);
                    } elseif (isset($params['height'])) {
                        $image->resize($params['height'], ImageResource::RESIZEMODE_SETHEIGHT);
                    }

                    $image->save($this->getOptions()->getCacheDirectory() . $cachedFileName);

                    chmod($this->getOptions()->getCacheDirectory() . $cachedFileName, 0777);
                    imagedestroy($image->getResource());
                } else {
                    copy($filename, $this->getOptions()->getCacheDirectory() . $cachedFileName);
                }

            }

            if (isset($params['srconly']) && $params['srconly'] == 1) {
                return $this->getOptions()->getCachePath() . $cachedFileName;
            } else {

                $attr = '';
                if ($this->getOptions()->isSetDimensionsAttributes()) {
                    [, , , $attr] = getimagesize($this->getOptions()->getCacheDirectory() . $cachedFileName);
                }


                $html = sprintf('<img src="%s" %s', $this->getOptions()->getCachePath() . $cachedFileName, $attr);
                $imageAttributes = ['hspace', 'vspace', 'align', 'alt', 'title', 'style', 'border', 'id', 'class'];

                foreach ($imageAttributes as $attr) {
                    if (array_key_exists($attr, $params)) {
                        $html .= sprintf(' %s="%s"', $attr, $params[$attr]);
                    }
                }

                if (isset($params['attributes']) && is_array($params['attributes'])) {
                    foreach ($params['attributes'] as $_attrName => $_attr) {
                        $html .= sprintf(' %s="%s"', $_attrName, $_attr);
                    }
                }
                $html .= '>';


                return $html;
            }
        } catch (\Exception $ex) {
            if (!$this->getOptions()->getThrowExceptions()) {
                return '';
            }
            throw new Exception\RuntimeException($ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
        }


    }

    protected function mkdir($path, $rights = 0777)
    {
        $folder_path = [strstr((string) $path, '.') ? dirname((string) $path) : $path];

        while (!is_dir(dirname((string) end($folder_path)))
            && dirname((string) end($folder_path)) != '/'
            && dirname((string) end($folder_path)) != '.'
            && dirname((string) end($folder_path)) != '') {
            array_push($folder_path, dirname((string) end($folder_path)));
        }

        while (false != ($parent_folder_path = array_pop($folder_path))) {
            if (!is_dir($parent_folder_path) && !mkdir($parent_folder_path, $rights)) {
                user_error("Can't create folder \"$parent_folder_path\".");
            }
        }

        if ($parent_folder_path) {
            chmod($parent_folder_path, $rights);
        }

    }
}
