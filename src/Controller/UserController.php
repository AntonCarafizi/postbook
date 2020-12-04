<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;


class UserController extends AbstractController
{

    public function index(Request $request, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $allUsers = $userRepository->findAll();
        $users = $paginator->paginate($allUsers, $request->query->getInt('page', $page), 5);

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


    public function edit(Request $request, User $user, ImageService $imageService, TranslatorInterface $translator): Response
    {
        $currentUser = $this->getUser();
        // check for "edit" access
        if ($currentUser != $user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $itemImages = $user->getImages();

        if ($form->isSubmitted() && $form->isValid()) {
            // add images to User
            $imageFiles = $form->get('images')->getData();
            $formImages = $imageService->uploadImages($imageFiles);
            $images = array_merge($itemImages, $formImages);
            $user->setImages($images);
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
            5
        );
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    public function showFavorites(Request $request, User $user, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $favorites = ($user->getFavorites()) ? $user->getFavorites() : [];

        $users = $paginator->paginate(
            $favorites = $userRepository->findBy(['id' => $favorites]),
            $request->query->getInt('page', $page),
            5
        );
        return $this->render('user/index.html.twig', ['users' => $users]);
    }

    public function showLikes(Request $request, User $user, PostRepository $postRepository, $page, PaginatorInterface $paginator): Response
    {
        $likes = ($user->getLikes()) ? $user->getLikes() : [];

        $posts = $paginator->paginate(
            $likes = $postRepository->findBy(['id' => $likes]),
            $request->query->getInt('page', $page),
            5
        );
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }


    public function editFavorites(Request $request, User $user, TranslatorInterface $translator): Response
    {
        $favorites = ($user->getFavorites()) ? $user->getFavorites() : [];
        $message = '';
        if ($request->isMethod('post')) {
            if ($request->get('fav-add')) {
                $id = $request->get('fav-add');
                $message = 'you.successfully.added.favorite';
                if (!in_array($id, $favorites)) {
                    array_push($favorites, $id);
                }
            }

            if ($request->get('fav-remove')) {
                $id = $request->get('fav-remove');
                $message = 'you.successfully.removed.favorite';
                if (in_array($id, $favorites)) {
                    foreach (array_keys($favorites, $id) as $key) {
                        unset($favorites[$key]);
                    }
                }
            }

            $user->setFavorites($favorites);
            $this->getDoctrine()->getManager()->flush();

            return $this->json($translator->trans($message));
        }
        return $this->redirectToRoute('user_favorites', ['id' => $user->getId()]);
    }

    public function editLikes(Request $request, User $user, TranslatorInterface $translator): Response
    {
        $likes = ($user->getLikes()) ? $user->getLikes() : [];
        $message = '';
        if ($request->isMethod('post')) {
            if ($request->get('like')) {
                $id = $request->get('like');
                $message = 'you.successfully.liked.post';
                if (!in_array($id, $likes)) {
                    array_push($likes, $id);
                }
            }

            if ($request->get('unlike')) {
                $id = $request->get('unlike');
                $message = 'you.successfully.unliked.post';
                if (in_array($id, $likes)) {
                    foreach (array_keys($likes, $id) as $key) {
                        unset($likes[$key]);
                    }
                }
            }

            $user->setLikes($likes);
            $this->getDoctrine()->getManager()->flush();

            return $this->json($translator->trans($message));
        }
        return $this->redirectToRoute('user_likes', ['id' => $user->getId()]);
    }

}
