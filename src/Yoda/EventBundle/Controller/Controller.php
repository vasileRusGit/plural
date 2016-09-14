<?php

namespace Yoda\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Yoda\EventBundle\Entity\Event;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController 
{
    /**
     * 
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public function getSecurityContext() {
        return $this->container->get('security.context');
    }

    public function enforceOwnerSecurity(Event $event) {
        $user = $this->getuser();

        if ($user !== $event->getOwner()) {
            throw new AccessDeniedException('You do not own this event!');
        }
    }

}
