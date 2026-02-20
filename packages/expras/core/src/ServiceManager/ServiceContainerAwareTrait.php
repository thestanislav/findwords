<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/20/2018
 * Time: 16:07
 */

namespace ExprAs\Core\ServiceManager;

use Psr\Container\ContainerInterface;

/**
 * Provides methods to manage a service container.
 *
 * @template T of object
 * @method T get(class-string<T> $id) Retrieve a service instance by its class name.
 * @phpstan-method T get(class-string<T> $id)
 * @psalm-method T get(class-string<T> $id)
 */
trait ServiceContainerAwareTrait
{
    /**
     * The service container instance.
     *
     * @var ContainerInterface
     * @phpstan-var ContainerInterface
     * @psalm-var ContainerInterface
     */
    protected ContainerInterface $_container;

    /**
     * Retrieves the service container instance.
     *
     * @return ContainerInterface The current container instance.
     * @phpstan-return ContainerInterface
     * @psalm-return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->_container;
    }

    /**
     * Sets the service container instance.
     *
     * @param ContainerInterface $container The container instance.
     * @return $this
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->_container = $container;
        return $this;
    }
}
