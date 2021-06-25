<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;


class VisitorListener
{
    private $em;
    private $userRepository;
    private $security;
    private $urlGenerator;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, UrlGeneratorInterface $urlGenerator, Security $security)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $me = $this->security->getUser();
        $date = new \DateTime();

        if (!$me) {
            return new RedirectResponse($this->urlGenerator->generate('login'));
        }

        if ($request->get('_route') == 'my_visitors') {
            // Get the User entity.

            // Update your field here.
            $me->setVisitorsLastChecked($date->getTimestamp());
            $this->em->persist($me);
            $this->em->flush();
        }

        if ($request->get('_route') == 'user_show') {
            $user = $this->userRepository->findOneby(['id' => $request->get('id')]);
            if ($user !== $me) {
                $user->addVisitor([$me->getId() => $date->getTimestamp()]);
            }
            $this->em->persist($me);
            $this->em->flush();
        }

    }
}