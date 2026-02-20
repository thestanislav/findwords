<?php

namespace ExprAs\Uploadable\Handler;

use Doctrine\ORM\EntityManager;
use ExprAs\Core\Image\Resource;
use ExprAs\Core\Response\HttpCachedResponse;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Uploadable\Entity\Uploaded;
use Laminas\Diactoros\Response;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FetchHandler implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->getContainer()->get(EntityManager::class);

        /**
         * @var Uploaded $uploaded
         */
        if (!($uploaded = $em->find(Uploaded::class, $request->getAttribute('uploaded_id', 0)))
            || !file_exists($uploaded->getPath())
        ) {
            return $handler->handle($request);
        }

        $queryParams = new Parameters($request->getQueryParams());

        $isImage = explode('/', (string)$uploaded->getMimeType())[0] == 'image';
        $isVideo = explode('/', (string)$uploaded->getMimeType())[0] == 'video';

        if ($isImage && $queryParams->get('size')) {

            $cacheName = md5($queryParams->get('size') . $uploaded->getPath() . filemtime($uploaded->getPath()));
            $path = 'data/cache/uploaded/' .
                implode('/', str_split(substr($cacheName, 0, 4), 2)) . '/' . $cacheName . '.' . explode(
                                                                                                    '/',
                                                                                                    (string)$uploaded->getMimeType()
                                                                                                )[1];
            if (!is_file($path)) {
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0777, true);
                }
                try {
                    $img = Resource::createFromFile($uploaded->getPath());
                    if ($img->getWidth() > intval($queryParams->get('size'))) {
                        $img->resize(intval($queryParams->get('size')));
                        $img->save($path);
                    } else {
                        $path = $uploaded->getPath();
                    }
                } catch (\Throwable) {
                    $path = $uploaded->getPath();
                }


            }

        } else {
            $path = $uploaded->getPath();
        }

        $resp = new HttpCachedResponse($path, $request);

        return
            $resp->withAddedHeader('Content-Disposition', sprintf('%s; name="%s"', ($isImage || $isVideo) ? 'inline' : 'attachment', $uploaded->getName()))
            ->withAddedHeader('Content-Type', $uploaded->getMimeType());

    }

}
