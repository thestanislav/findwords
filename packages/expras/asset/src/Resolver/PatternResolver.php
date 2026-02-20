<?php

namespace ExprAs\Asset\Resolver;

use AssetManager\Resolver\MapResolver;
use ExprAs\Asset\Asset\HttpAsset;
use Assetic\Asset\FileAsset;
use SplFileInfo;

/**
 * This resolver allows you to resolve using pattern mapping to a file.
 */
class PatternResolver extends MapResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        $maps = $this->getMap();
        $file = false;
        foreach ($maps as $_k => $_v) {
            $pattern = sprintf('~^%s$~', $_k);
            if (preg_match($pattern, (string) $name)) {
                $_file = preg_replace_callback(
                    $pattern, function ($matches) use ($_v) {
                        foreach ($matches as $_k => $_m) {
                            $_v = str_replace('$' . $_k, basename($_m), $_v);
                        }
                        return $_v;
                    }, (string) $name
                );
                $_file = urldecode((string) $_file);
                if (filter_var($_file, FILTER_VALIDATE_URL)) {
                    $file = $_file;
                    break;
                }
                $_fileInfo = new SplFileInfo($_file);
                if (!$_fileInfo->isDir() && $_fileInfo->isReadable()) {
                    $file = urldecode($_fileInfo->getRealPath());
                    break;
                }
            }
        }

        if (!$file) {
            return null;
        }

        $mimeType = $this->getMimeResolver()->getMimeType($file);

        if (false === filter_var($file, FILTER_VALIDATE_URL)) {
            $asset = new FileAsset($file);
        } else {
            $asset = new HttpAsset($file);
        }

        $asset->mimetype = $mimeType;

        return $asset;
    }
}
