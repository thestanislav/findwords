<?php

namespace ExprAs\Core\Response;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ServerRequestInterface;

class RangedResponse extends Response
{

    public function __construct($filepath, $rangeHeader)
    {
        $file = new \SplFileInfo($filepath);

        preg_match('~bytes=(\d+)-(\d+)?~', (string) $rangeHeader, $matches);
        // Validate the range header
        if (empty($matches) || !isset($matches[1])) {
            // Handle invalid range, e.g. return a 416 Range Not Satisfiable
            return parent::__construct(
                'php://memory', 416, [
                    'Content-Range' => 'bytes */' . $file->getSize(),
                    'Content-Length' => 0,
                ]
            );
        }

        $fileSize = filesize($filepath);
        [, $range] = explode('=', (string) $rangeHeader, 2);
        [$start, $end] = explode('-', $range);
        $start = (int)$start;
        $end = $end === '' ? $fileSize - 1 : (int)$end;

        if ($start > $end || $end >= $fileSize) {
            return new Response(416);
        }
        // Open file and seek to position
        $fp = fopen($filepath, 'rb');
        if ($fp === false) {
            return parent::__construct(
                'php://memory', 500, [
                    'Content-Length' => 0,
                ]
            );
        }

        // Calculate length and read data
        $length = $end - $start + 1;


        return parent::__construct(
            $fp, 206, [
                'Accept-Ranges'  => 'bytes',
                'Content-Length' => $length,
                'Content-Range'  => sprintf('bytes %d-%d/%d', $start, $start + $length - 1, $fileSize)
            ]
        );
    }
}