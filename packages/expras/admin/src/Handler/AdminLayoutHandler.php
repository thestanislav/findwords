<?php

namespace ExprAs\Admin\Handler;

use Doctrine\ORM\EntityManager;
use ExprAs\Admin\ResourceMapping\Configuration;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Stdlib\SplPriorityQueue;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Template;

class AdminLayoutHandler implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    /**
     * @var Template\TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var EntityManager $entityManager ;
     */
    protected $entityManager;


    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->getContainer()->get(EntityManager::class);
        }

        return $this->entityManager;
    }


    /**
     * @return Template\TemplateRendererInterface
     */
    public function getRenderer()
    {
        if (!$this->renderer) {
            $this->renderer = $this->getContainer()->get(Template\TemplateRendererInterface::class);
        }
        return $this->renderer;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!($action = $request->getAttribute('action'))) {
            return $handler->process($request, $handler);
        }
        if (!method_exists($this, $actionMethod = $action . 'Action')) {
            return $handler->process($request, $handler);
        }

        try {
            return call_user_func_array([$this, $actionMethod], [$request, $handler]);
        } catch (\Throwable $ex) {
            die($ex->getMessage());
        }

    }

    public function indexAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        return new HtmlResponse(file_get_contents('data/admin.html'));
    }

    public function pingAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $config = $this->getContainer()->get('config');
        $allowedRoles = array_keys($config['exprass_admin']['permissions']);

        if (!($user = $request->getAttribute(UserInterface::class)) || (count(array_intersect($allowedRoles, $user->getRoles())) === 0)) {
            return new JsonResponse(
                [
                    'success' => false,
                ], 403
            );
        }

        return new JsonResponse(
            [
                'act' => (new \DateTime())->format('r'),
            ]
        );
    }

    public function resourcesAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {

        $config = $this->getContainer()->get('config');
        $allowedRoles = array_keys($config['exprass_admin']['permissions']);
        if (!($user = $request->getAttribute(UserInterface::class)) || (count(array_intersect($allowedRoles, $user->getRoles())) === 0)) {
            return new JsonResponse(
                [
                    'resources' => [],
                    'dashboard' => []
                ],
            );
        }

        /**
         * @var Configuration $config
         */
        $config = $this->getContainer()->get(Configuration::class);

        $specs = $config->collectSpecification();
        return new JsonResponse(
            [
                'resources' => $specs,
                'dashboard' => $config->collectDashboard()

            ], 200, [
                'x-total-count' => count($specs)
            ]
        );

    }


}
