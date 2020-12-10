<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\ImageService;
use App\Service\ArrayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;


class UserController extends AbstractController
{

    public function index(Request $request, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $allUsers = $userRepository->findBy([], ['id' => 'DESC']);
        $users = $paginator->paginate(
            $allUsers,
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users]);
    }


    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json($translator->trans('user.successfully.created'));
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    public function show(Request $request, User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }


    public function edit(Request $request, User $user, TranslatorInterface $translator): Response
    {
        $currentUser = $this->getUser();
        // check for "edit" access
        if ($currentUser != $user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->json($translator->trans('user.successfully.updated'));
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    public function delete(Request $request, User $user, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            return $this->json($translator->trans('user.successfully.deleted'));
        }
        return $this->redirectToRoute('user_index');
    }

    public function showPosts(Request $request, User $user, $page, PaginatorInterface $paginator): Response
    {
        $posts = $paginator->paginate(
            $user->getPosts(),
            $request->query->getInt('page', $page),
            $this->getParameter('posts_per_page')
        );
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    public function newPost()
    {

    }

    public function deletePost()
    {

    }


    public function editFavorites(Request $request, User $user, TranslatorInterface $translator, ArrayService $arrayService): Response
    {
        $getFavorites = ($user->getFavorites()) ? $user->getFavorites() : [];
        $setFavorites = [];
        $message = '';
        if ($request->isMethod('post')) {
            if ($request->get('fav-add')) {
                $id = $request->get('fav-add');
                $setFavorites = $arrayService->addElement($getFavorites, $id);
                $message = 'you.successfully.removed.favorite';
            }

            if ($request->get('fav-remove')) {
                $id = $request->get('fav-remove');
                $setFavorites = $arrayService->removeElement($getFavorites, $id);
                $message = 'you.successfully.removed.favorite';
            }

            $user->setFavorites($setFavorites);
            $this->getDoctrine()->getManager()->flush();

            return $this->json($translator->trans($message));
        }
        return $this->redirectToRoute('user_favorites', ['id' => $user]);
    }

    public function editLikes(Request $request, User $user, TranslatorInterface $translator, ArrayService $arrayService): Response
    {
        $getlikes = ($user->getLikes()) ? $user->getLikes() : [];
        $setLikes = [];
        $message = '';
        if ($request->isMethod('post')) {
            if ($request->get('like')) {
                $id = $request->get('like');
                $setLikes = $arrayService->addElement($getlikes, $id);
                $message = 'you.successfully.liked.post';
            }

            if ($request->get('unlike')) {
                $id = $request->get('unlike');
                $setLikes = $arrayService->removeElement($getlikes, $id);
                $message = 'you.successfully.unliked.post';
            }

            $user->setLikes($setLikes);
            $this->getDoctrine()->getManager()->flush();

            return $this->json($translator->trans($message));
        }
        return $this->redirectToRoute('user_likes', ['id' => $user]);
    }

}
