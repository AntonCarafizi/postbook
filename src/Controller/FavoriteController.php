<?php

namespace App\Controller;

use App\Service\ArrayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class FavoriteController extends AbstractController
{
    private $entityManager;

    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function index(Request $request, $id, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);
        $favorites = $user->getFavorites();

        $users = $paginator->paginate(
            $favorites = $userRepository->findBy(['id' => $favorites], ['id' => 'DESC']),
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );
        return $this->render('user/index.html.twig', ['users' => $users, 'title' => 'my.favorites']);
    }

    public function new(Request $request, $favorite, ArrayService $arrayService): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('add_favorite'.$favorite, $request->request->get('_token'))) {
            $favorites = $user->getFavorites();
            $arrayService->addElement($favorites, $favorite);
            $user->setFavorites($favorites);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('you.successfully.added.favorite'));
            //return $this->json($this->translator->trans('you.successfully.added.favorite'));
        }
        return $this->redirect($referer);
    }

    public function delete(Request $request, $favorite, ArrayService $arrayService): Response
    {
        $referer = $request->headers->get('referer');
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete_favorite'.$favorite, $request->request->get('_token'))) {
            $favorites = $user->getFavorites();
            if (!$favorites) {
                throw $this->createNotFoundException($this->translator->trans('favorites.not.found'));
            }
            $arrayService->deleteElementByValue($favorites, $favorite);
            $user->setFavorites($favorites);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.removed.favorite'));
            //return $this->json($this->translator->trans('you.successfully.removed.favorite'));
        }
        return $this->redirect($referer);
    }
}