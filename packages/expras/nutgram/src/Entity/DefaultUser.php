<?php

namespace ExprAs\Nutgram\Entity;

use Doctrine\ORM\Mapping as ORM;
use ExprAs\Nutgram\Entity\Trait\AppUserProvider;

#[ORM\Table(name: 'expras_nutgram_users')]
#[ORM\Entity]
class DefaultUser extends User
{
    use AppUserProvider;
}
