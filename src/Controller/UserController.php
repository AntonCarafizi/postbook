<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }


    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    public function show(Request $request, User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
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
            $this->addFlash('success', $translator->trans('user.successfully.updated'));

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
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
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    public function showFavorites(Request $request, User $user, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $favorites = ($user->getFavorites()) ? $user->getFavorites() : [];

        $users = $paginator->paginate(
            $favorites = $userRepository->findBy(['id' => $favorites]),
            $request->query->getInt('page', $page),
            5
        );
        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    public function editFavorites(Request $request, User $user): Response
    {
        $favorites = ($user->getFavorites()) ? $user->getFavorites() : [];

        if ($request->isMethod('post')) {
            if ($request->get('fav-add')) {
                $id = $request->get('fav-add');
                if (!in_array($id, $favorites)) {
                    array_push($favorites, $id);
                }
            }

            if ($request->get('fav-remove')) {
                $id = $request->get('fav-remove');
                if (in_array($id, $favorites)) {
                    foreach (array_keys($favorites, $id) as $key) {
                        unset($favorites[$key]);
                    }
                }
            }

            $user->setFavorites($favorites);
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->render('user/favorites/success.html.twig');
    }

}
