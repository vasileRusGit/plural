<?php

namespace Yoda\EventBundle\Controller;

use Yoda\EventBundle\Entity\Event;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public function getSecurityContext()
    {
        return $this->container->get('security.context');
    }

    public function enforceOwnerSecurity(Event $event)
    {
        $user = $this->getUser();

        if ($user != $event->getOwner()) {
            throw new AccessDeniedException("You don't own this event!");
        }
    }
}