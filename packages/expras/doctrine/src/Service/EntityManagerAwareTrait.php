<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Service;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerAwareTrait
{
    protected ?EntityManagerInterface $entityManager = null;

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function setEntityManager(EntityManagerInterface $entityManager): static
    {
        $this->entityManager = $entityManager;
        return $this;
    }
}
