<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;


class UserController extends AbstractController
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
        $allUsers = $userRepository->findBy([], ['id' => 'DESC']);

        if (!$allUsers) {
            throw $this->createNotFoundException($this->translator->trans('users.not.found'));
        }

        $users = $paginator->paginate(
            $allUsers,
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users]);
    }


    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.successfully.created'));
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
            //return $this->json($this->translator->trans('user.successfully.created'));
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    public function show($id, UserRepository $userRepository): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }

        return $this->render('user/show.html.twig', ['user' => $user]);
    }


    public function edit(Request $request, $id, UserRepository $userRepository): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);
        // check for "edit" access
        /*if ($currentUser != $user) {
            throw $this->createAccessDeniedException();
        }*/

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.successfully.updated'));
            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
            //return $this->json($this->translator->trans('user.successfully.updated'));
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('user.successfully.deleted'));
            //return $this->json($this->translator->trans('user.successfully.deleted'));
        }
        return $this->redirectToRoute('user_index');
    }

    public function showPosts(Request $request, $id, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }

        $userPosts = $user->getPosts();

        if (!$userPosts) {
            throw $this->createNotFoundException($this->translator->trans('posts.not.found'));
        }

        $posts = $paginator->paginate(
            $userPosts,
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

}
