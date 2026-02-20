<?php

declare(strict_types=1);

namespace ExprAs\Core\Middleware;

use Laminas\Diactoros\UploadedFile;
use Laminas\Stdlib\ArrayUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;

class JsonAttachmentDetectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
 * @var UploadedFile[] $uploadedFiles 
*/
        $uploadedFiles = $request->getUploadedFiles();
        if (!count($uploadedFiles)) {
            return $handler->handle($request);
        }

        $bodyParams = $request->getParsedBody();
        $newUploadedFiles = $this->_parseInjectJSON($uploadedFiles, $bodyParams);

        return $handler->handle($request->withParsedBody($bodyParams)->withUploadedFiles($newUploadedFiles));
    }

    protected function _parseInjectJSON($uploadedFiles, &$bodyParams)
    {
        $newUploadedFiles = [];
        foreach ($uploadedFiles as $_k => $_uploaded) {

            if (is_array($_uploaded)) {

                $newUploadedFiles[$_k] = $this->_parseInjectJSON($_uploaded, $bodyParams);

            } else {
                if ($_uploaded->getClientMediaType() === 'expras-body/inject-json') {
                    $bodyParams = array_merge_recursive($bodyParams, json_decode((string) $_uploaded->getStream()->getContents(), true, 512, JSON_THROW_ON_ERROR));
                } else {
                    $newUploadedFiles[$_k] = $_uploaded;
                }
            }


        }

        return $newUploadedFiles;
    }
}
