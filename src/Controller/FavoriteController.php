<?php

namespace App\Controller;

use App\Service\ArrayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FavoriteController extends AbstractController
{
    public function show(Request $request, $id, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);

        $favorites = ($user->getFavorites()) ? $user->getFavorites() : [];

        $users = $paginator->paginate(
            $favorites = $userRepository->findBy(['id' => $favorites], ['id' => 'DESC']),
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );
        return $this->render('user/index.html.twig', ['users' => $users]);
    }

    public function new(Request $request, $favorite, ArrayService $arrayService, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('add_favorite'.$favorite, $request->request->get('_token'))) {
            $favorites = $user->getFavorites();
            $arrayService->addElement($favorites, $favorite);
            $user->setFavorites($favorites);
            $this->getDoctrine()->getManager()->flush();
            return $this->json($translator->trans('you.successfully.added.favorite'));
        }
        return $this->redirectToRoute('user_favorites', ['id' => $user->getId()]);
    }

    public function delete(Request $request, $favorite, ArrayService $arrayService, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete_favorite'.$favorite, $request->request->get('_token'))) {
            $favorites = $user->getFavorites();
            $arrayService->deleteElementByValue($favorites, $favorite);
            $user->setFavorites($favorites);
            $this->getDoctrine()->getManager()->flush();
            return $this->json($translator->trans('you.successfully.removed.favorite'));
        }
        return $this->redirectToRoute('user_favorites', ['id' => $user->getId()]);
    }
}