<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 09.03.13
 * Time: 22:27
 */

namespace ExprAs\View\Helper;

use Laminas\Stdlib\Parameters;
use Laminas\View\Helper\AbstractHelper;
use Imagick;

class PdfThumb extends AbstractHelper
{
    protected $_availImageTypes = ['gif', 'jpg', 'png'];

    protected $_defaultParams
        = [
            'cacheDirectory' => 'public/cache/',
            'cacheUri' => '/cache/',
            'hashedDirLevel' => 2,
            'outputFormat'   => 'jpeg',
            'pageNumber' => 0
        ];

    protected $_params;


    public function __invoke($filename = null, $params = [])
    {

        if (!$filename) {
            return $this;
        }

        return $this->render($filename, $params);
    }

    public function render($filename, $params = [])
    {
        $this->_params = new Parameters(array_merge($this->_defaultParams, $params));
        $filename = urldecode((string) $filename);
        if (!is_file($filename) && !str_starts_with($filename, 'http') && !str_starts_with($filename, '//')) {
            return '';
        }


        $cacheParams = array_intersect_key(
            array_merge($params, ['filename' => $filename], $this->_params->toArray()),
            array_flip(['width', 'height', 'filename', 'cache_directory'])
        );


        $cachedFileName = md5(serialize($cacheParams) . @filemtime($filename)) . '.' . strtolower((string) $this->_params->get('outputFormat'));

        $cachedFileName = implode('/', str_split(substr($cachedFileName, 0, $this->_params->get('hashedDirLevel') * 2), 2)) . '/' . $cachedFileName;
        $cacheDir = dirname($this->_params->get('cacheDirectory') . $cachedFileName);

        if (!is_dir($cacheDir)) {
            $this->mkdir($cacheDir);
        }

        try {
            $cachedFilePath = $this->_params->get('cacheDirectory') . $cachedFileName;
            if (!is_file($cachedFilePath)) {

                $image = new Imagick();
                $image->readImage(sprintf('%s[%d]', $filename, $this->_params->get('pageNumber')));

                if ($this->_params->get('width') && $this->_params->get('width')) {
                    $image->adaptiveResizeImage($this->_params->get('width'), $this->_params->get('height'), $this->_params->get('bestfit', false));

                } elseif ($this->_params->get('width')) {
                    $image->adaptiveResizeImage($this->_params->get('width'), 0);
                } elseif ($this->_params->get('height')) {
                    $image->adaptiveResizeImage(0, $this->_params->get('height'));
                }

                $image->setFormat($this->_params->get('outputFormat'));
                $image->writeImage($cachedFilePath);

                chmod($cachedFilePath, 0777);
                $image->destroy();

            }

            if ($this->_params->get('srconly')) {
                return $cachedFilePath;
            } else {

                [, , , $attr] = getimagesize($cachedFilePath);

                $html = sprintf('<img src="%s" %s', $this->_params->get('cacheUri') . $cachedFileName, $attr);
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
        } catch (\Exception) {
            return '';
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
            if (!mkdir($parent_folder_path, $rights)) {
                user_error("Can't create folder \"$parent_folder_path\".");
            }
        }

        if ($parent_folder_path) {
            chmod($parent_folder_path, $rights);
        }

    }
}
