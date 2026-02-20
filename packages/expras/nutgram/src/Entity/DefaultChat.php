<?php

namespace ExprAs\Nutgram\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'expras_nutgram_chats')]
#[ORM\Entity]
class DefaultChat extends Chat
{
}
