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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
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
        $date = new \DateTime();

        $sessionParams = session_get_cookie_params();

        $allUsers = ($request->query->get('status') == 'online') ? $userRepository->findOnline($sessionParams['lifetime'], $date->getTimestamp()) : $userRepository->findBy([], ['id' => 'DESC']);

        $title = ($request->query->get('status') == 'online') ? 'users.online' : 'all.users';

        $users = $paginator->paginate(
            $allUsers,
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users, 'title' => $title]);
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

        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    public function edit(Request $request, $id, UserRepository $userRepository): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);
        //$this->denyAccessUnlessGranted('edit', $user);
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

        $userPosts = $user->getPosts();

        $posts = $paginator->paginate(
            $userPosts,
            $request->query->getInt('page', $page),
            $this->getParameter('posts_per_page')
        );
        return $this->render('post/index.html.twig', ['posts' => $posts, 'title' => 'my.posts']);
    }
}
