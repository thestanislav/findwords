<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 14.04.2014
 * Time: 11:36
 */

namespace ExprAs\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;

class RepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * The list of EntityRepository instances.
     *
     * @var \Doctrine\Common\Persistence\ObjectRepository[]
     */
    private array $repositoryList = [];

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        return $this->repositoryList[$repositoryHash] ?? ($this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName));
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager The EntityManager instance.
     * @param string                               $entityName    The name of the entity.
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadata */
        $metadata            = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName
            ?: \ExprAs\Doctrine\Repository\DefaultRepository::class;

        return new $repositoryClassName($entityManager, $metadata);
    }
}
