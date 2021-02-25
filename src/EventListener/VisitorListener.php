<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use App\Service\ArrayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class VisitorListener
{
    private $em;

    private $userRepository;

    private $security;

    private $arrayService;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, Security $security, ArrayService $arrayService)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->arrayService = $arrayService;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $me = $this->security->getUser();
        $date = new \DateTime();

        if ($request->get('_route') == 'my_visitors') {
            // Get the User entity.

            // Update your field here.
            $me->setVisitorsLastChecked($date->getTimestamp());
            $this->em->persist($me);
            $this->em->flush();
        }

        if ($request->get('_route') == 'user_show') {
            $user = $this->userRepository->findOneby(['id' => $request->get('id')]);
            $userVisitors = $user->getVisitors();
            $this->arrayService->addElement($userVisitors, [$me->getId() => $date->getTimestamp()]);
            $user->setVisitors($userVisitors);
            $this->em->persist($me);
            $this->em->flush();
        }

    }
}