<?php

// src/EventListener/LoginListener.php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        // Get the User entity.
        $user = $event->getAuthenticationToken()->getUser();

        // Update your field here.
        $date = new \DateTime();
        $user->setLastLogin($date->getTimestamp());

        // Persist the data to database.
        $this->em->persist($user);
        $this->em->flush();
    }
}