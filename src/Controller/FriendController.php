<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 20.02.21
 * Time: 15:05
 */

namespace App\Controller;


use App\Repository\UserRepository;
use App\Service\ArrayService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class FriendController extends AbstractController
{
    private $entityManager;

    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function index(Request $request, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $getUser = $userRepository->findOneby(['id' => $this->getUser()->getId()]);

        $userFriends = $userRepository->findby(['id' => $getUser->getFriends()], ['id' => 'DESC']);

        $userFriendRequests = $userRepository->findby(['id' => $getUser->getFriendRequests()], ['id' => 'DESC']);

        $users = $paginator->paginate(
            $userFriends,
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users, 'friendRequests' => $userFriendRequests, 'title' => 'my.friends']);
    }

    public function new(Request $request, $friend, UserRepository $userRepository, ArrayService $arrayService): Response
    {
        $referer = $request->headers->get('referer');
        $me = $this->getUser();
        $user = $userRepository->findOneBy(['id' => $friend]);

        if ($this->isCsrfTokenValid('add_friend'.$friend, $request->request->get('_token'))) {

            // Add friends to me
            $myFriends = $me->getFriends();
            $myFriendsRequests = $me->getFriendRequests();
            $arrayService->addElement($myFriends, $friend);
            $me->setFriends($myFriends);
            $arrayService->deleteElementByValue($myFriendsRequests, $friend);
            $me->setFriendRequests($myFriendsRequests);

            // Add me to friends
            $userFriends = $user->getFriends();
            $arrayService->addElement($userFriends, $me->getId());
            $user->setFriends($userFriends);

            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('you.successfully.added.friend'));
            //return $this->json($this->translator->trans('you.successfully.added.favorite'));
        }
        return $this->redirect($referer);
    }

}