<?php

declare(strict_types=1);

namespace App\Navigation;

use Laminas\View\HelperPluginManager;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation;
use Mezzio\Router\RouteResult;
use Mimmi20\Mezzio\Navigation\ContainerInterface;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;
use Mimmi20\Mezzio\Navigation\Page\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\Navigation\Breadcrumbs;

/**
 * Resets navigation state for Swoole/coroutine environments.
 *
 * - Updates all RouteInterface pages with the current RouteResult
 * - Clears breadcrumb container
 */
final class NavigationStateResetUpdater
{
    public function __construct(
        private readonly HelperPluginManager $helperPluginManager,
    ) {
    }

    /**
     * @param ContainerInterface<PageInterface> $navigation
     */
    public function resetFromRequest(ContainerInterface $navigation, ServerRequestInterface $request): void
    {
        $this->resetBreadcrumbs();
        $this->updateRouteMatch($navigation, $request);
    }

    private function resetBreadcrumbs(): void
    {
        if ($this->helperPluginManager->has('navigation')) {
            /**
             * @var Navigation $navigation
             */
            $navigation = $this->helperPluginManager->get('navigation');
            /**
             * @var Breadcrumbs $breadcrumb
             */
            $breadcrumb = $navigation->findHelper('breadcrumbs');
            if ($breadcrumb instanceof Breadcrumbs) {
                $breadcrumb->getContainer()->removePages();
            }
        }
    }

    /**
     * @param ContainerInterface<PageInterface> $navigation
     */
    private function updateRouteMatch(ContainerInterface $navigation, ServerRequestInterface $request): void
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult) {
            return;
        }

        $this->updatePagesRecursively($navigation, $routeResult);
    }

    /**
     * @param ContainerInterface<PageInterface> $container
     */
    private function updatePagesRecursively(ContainerInterface $container, RouteResult $routeResult): void
    {
        foreach ($container as $page) {
            if ($page instanceof RouteInterface) {
                $page->setRouteMatch($routeResult);
            }

            if ($page->hasPages()) {
                $this->updatePagesRecursively($page, $routeResult);
            }
        }
    }
}
