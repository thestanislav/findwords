<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 01.05.2019
 * Time: 12:23
 */

namespace ExprAs\View\Helper;

use Mezzio\Flash\FlashMessages;
use Laminas\View\Helper\AbstractHelper;
use Mezzio\Session\Session;

class Flash extends AbstractHelper
{
    private ?FlashMessages $flashMessages = null;

    public function setFlashMessages(FlashMessages $flashMessages): void
    {
        $this->flashMessages = $flashMessages;
    }

    public function __invoke(): array
    {
        if ($this->flashMessages !== null) {
            return $this->flashMessages->getFlashes();
        }
        $flashMessages = FlashMessages::createFromSession(
            new Session($_SESSION ?? [])
        );

        return $flashMessages->getFlashes();
    }
}
