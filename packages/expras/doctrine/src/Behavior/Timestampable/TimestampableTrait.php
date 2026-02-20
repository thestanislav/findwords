<?php

namespace ExprAs\Doctrine\Behavior\Timestampable;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column(type: DoctrineTypes::DATETIME_IMMUTABLE, nullable: false)]
    #[Gedmo\Timestampable(on: 'create')]
    protected \DateTimeInterface $ctime;

    #[ORM\Column(type: DoctrineTypes::DATETIME_IMMUTABLE, nullable: false)]
    #[Gedmo\Timestampable(on: 'update')]
    protected \DateTimeInterface $mtime;

    /**
     * @return \DateTimeInterface
     */
    public function getCtime(): \DateTimeInterface
    {
        return $this->ctime;
    }

    public function setCtime(\DateTimeInterface $ctime): void
    {
        $this->ctime = $ctime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getMtime(): \DateTimeInterface
    {
        return $this->mtime;
    }

    public function setMtime(\DateTimeInterface $mtime): void
    {
        $this->mtime = $mtime;
    }


}
