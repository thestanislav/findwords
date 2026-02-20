<?php

namespace ExprAs\Uploadable\Middleware;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Uploadable\Entity\Uploaded;
use ExprAs\Uploadable\EventListener\UploadableListener;
use ExprAs\Uploadable\FileInfo;
use Laminas\Diactoros\UploadedFile;
use Laminas\Stdlib\ArrayUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UploadableEntityInjectionMiddleware implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!count($request->getUploadedFiles())) {
            return $handler->handle($request);
        }

        $body = new \ArrayObject($request->getParsedBody());

        foreach ($request->getUploadedFiles() as $_k => $_uploaded) {
            if ($body->offsetExists($_k) && $_uploaded instanceof UploadedFile) {
                continue;
            }

            if (isset($body[$_k]) && is_array($body[$_k])) {
                $_uploadedBody = $this->_createEntity($_k, $_uploaded);
                $body[$_k] = ArrayUtils::merge($body[$_k], $_uploadedBody, true);
            } else {
                $body[$_k] = $this->_createEntity($_k, $_uploaded);
            }


        }

        return $handler->handle($request->withParsedBody($body->getArrayCopy()));
    }

    /**
     * @param UploadedFile | UploadedFile[] $uploaded
     *
     * @return Uploaded | Uploaded[]
     */
    protected function _createEntity($key, $uploaded)
    {
        if (is_array($uploaded)) {
            $entities = [];
            foreach ($uploaded as $_k => $_upload) {
                $entities[$_k] = $this->_createEntity($key, $_upload);
            }
            return $entities;
        }

        /**
 * @var UploadableListener $uploadableListener 
*/
        $uploadableListener = $this->getContainer()->get(UploadableListener::class);

        $entity = new ($this->_findUploadedType($key))($uploaded);
        $uploadableListener->addFileInfoObjectInjectQueue($entity, new FileInfo($uploaded));
        return $entity;
    }

    protected function _findUploadedType($key)
    {
        $config = $this->getContainer()->get('config')['uploadable']['entity'];
        if (is_array($config) && array_key_exists($key, $config)) {
            return $config[$key];
        }

        return $config['default'];
    }
}
