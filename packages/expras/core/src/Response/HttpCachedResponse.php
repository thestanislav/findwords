<?php

namespace ExprAs\Core\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ServerRequestInterface;

class HttpCachedResponse extends Response
{
    public function __construct($filepath, ServerRequestInterface $request)
    {

        $isDataWrapper = str_starts_with((string) $filepath, 'data://');
        $file = new \SplFileInfo($filepath);
        if ($isDataWrapper) {
            $fileHash = md5((string) $filepath);
        } else {
            $fileHash = md5($file->getRealPath() . $file->getCTime() . $file->getMTime());
        }
        $fileIsModified = true;

        if (($_header = $request->getHeaderLine('if-modified-since')) && !$isDataWrapper
            && new \DateTime($_header) >= (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($file->getMTime())
        ) {
            $fileIsModified = false;
        } elseif (($_header = $request->getHeaderLine('If-None-Match')) && $_header === $fileHash) {
            $fileIsModified = false;
        }

        if ($fileIsModified === false) {
            if ($request->getMethod() === 'HEAD') {
                return parent::__construct('php://memory', 204);
            }

            return parent::__construct(
                'php://memory', 304, [
                'Pragma'        => 'cache',
                'Cache-Control' => 'public',
                ]
            );
        }


        $type = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME);
            $type = finfo_file($fi, $file);
        } elseif (function_exists('mime_content_type')) {
            $type = mime_content_type($file);
        }

        $fp = fopen($filepath, 'rb');
        $stat = fstat($fp);

        return parent::__construct(
            $fp, 200, [
            'Etag'           => $fileHash,
            'Pragma'         => 'cache',
            'Cache-Control'  => 'public',
            'Content-Type'   => $type,
            'Content-Length' => $stat['size'],
            ...(
                !$isDataWrapper ?
                ['Last-Modified' => (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($file->getMtime())->format('D, d M Y H:i:s \G\M\T')]
                : []
            )

            ]
        );
    }
}
