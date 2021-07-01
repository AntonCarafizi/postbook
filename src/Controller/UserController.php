<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ImageType;
use App\Form\UserType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


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

        $title = ($request->query->get('status') == 'online') ? 'users.online' : 'users.all';

        $users = $paginator->paginate(
            $allUsers,
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users, 'title' => $title]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.successfully.created'));
            return $this->redirectToRoute('user_show', ['id' => $user->getUuid()]);
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


    public function edit(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('edit', $user, $this->translator->trans('you.cant.edit.this.user'));
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

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getUuid(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('user.successfully.deleted'));
            //return $this->json($this->translator->trans('user.successfully.deleted'));
        }
        return $this->redirectToRoute('user_index');
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
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

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function showLikes(Request $request, $id, UserRepository $userRepository, PostRepository $postRepository, $page, PaginatorInterface $paginator): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);
        $likes = $postRepository->findBy(['id' => $user->getLikes()], ['id' => 'DESC']);

        $posts = $paginator->paginate(
            $likes,
            $request->query->getInt('page', $page),
            $this->getParameter('posts_per_page')
        );
        return $this->render('post/index.html.twig', ['posts' => $posts, 'title' => 'my.likes']);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function likePost($like, Request $request): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('add_like'.$like, $request->request->get('_token'))) {
            $user->addLike($like);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.liked.post'));
            //return $this->json($this->translator->trans('you.successfully.liked.post'));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unlikePost($like, Request $request): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete_like'.$like, $request->request->get('_token'))) {
            $user->removeLike($like);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.unliked.post'));
            //return $this->json($this->translator->trans('you.successfully.liked.post'));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function showFavorites(Request $request, $id, UserRepository $userRepository, PostRepository $postRepository, $page, PaginatorInterface $paginator): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);
        $favorites = $userRepository->findBy(['id' => $user->getFavorites()], ['id' => 'DESC']);

        $users = $paginator->paginate(
            $favorites,
            $request->query->getInt('page', $page),
            $this->getParameter('posts_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users, 'title' => 'my.favorites']);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function addFavorite($favorite, Request $request): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('add_favorite'.$favorite, $request->request->get('_token'))) {
            $user->addFavorite($favorite);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.added.favorite'));
            //return $this->json($this->translator->trans('you.successfully.liked.post'));
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteFavorite($favorite, Request $request): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete_favorite'.$favorite, $request->request->get('_token'))) {
            $user->removeFavorite($favorite);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.removed.favorite'));
            //return $this->json($this->translator->trans('you.successfully.liked.post'));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function showFriends(Request $request, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
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

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function addFriendRequest(Request $request, $friend, UserRepository $userRepository): Response
    {
        $me = $this->getUser();
        $user = $userRepository->findOneBy(['id' => $friend]);
        if ($this->isCsrfTokenValid('add_friend_request'.$friend, $request->request->get('_token'))) {

            // Add my ID to user friendRequests
            $user->addFriendRequest($me->getID());
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.sent.friend.request'));
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function addFriend(Request $request, $friend, UserRepository $userRepository): Response
    {
        $me = $this->getUser();
        $user = $userRepository->findOneBy(['id' => $friend]);

        if ($this->isCsrfTokenValid('add_friend'.$friend, $request->request->get('_token'))) {

            // Add friends to me
            $me->addFriend($friend);
            $me->removeFriendRequest($friend);

            // Add me to friends
            $user->addFriend($me->getId());
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.added.friend'));
            //return $this->json($this->translator->trans('you.successfully.added.favorite'));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteFriend(Request $request, $friend): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete_friend'.$friend, $request->request->get('_token'))) {
            $user->removeFriend($friend);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.removed.friend'));
            //return $this->json($this->translator->trans('you.successfully.removed.favorite'));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteFriendRequest(Request $request, $friend): Response
    {
        $me = $this->getUser();
        if ($this->isCsrfTokenValid('delete_friend_request'.$friend, $request->request->get('_token'))) {
            $me->removeFriendRequest($friend);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.removed.friend_request'));
            //return $this->json($this->translator->trans('you.successfully.removed.favorite'));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function showVisitors(Request $request, UserRepository $userRepository, $page, PaginatorInterface $paginator): Response
    {
        $getUser = $userRepository->findOneby(['id' => $this->getUser()->getId()]);

        $userVisitors = $userRepository->findByIdList(array_keys($getUser->getVisitors()));

        $users = $paginator->paginate(
            $userVisitors,
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users, 'title' => 'my.visitors']);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function addImage(Request $request, $id, UserRepository $userRepository, ImageService $imageService): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(ImageType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            $formImages = $imageService->uploadFiles($imageFiles);
            $user->addImages($formImages);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.uploaded.images'));
            return $this->redirect($request->headers->get('referer'));
            //return $this->json($this->translator->trans('you.successfully.uploaded.image'));
        }

        return $this->render('user/images/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setAvatar($image, Request $request): Response
    {
        $user = $this->getUser();
        $user->setAvatar($image);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.selected.avatar'));
        //return $this->json($this->translator->trans('you.successfully.liked.post'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function removeAvatar(Request $request): Response
    {
        $user = $this->getUser();
        $user->removeAvatar();
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.removed.avatar'));
        //return $this->json($this->translator->trans('you.successfully.liked.post'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setBackground($image, Request $request): Response
    {
        $user = $this->getUser();
        $user->setBackground($image);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.selected.background'));
        //return $this->json($this->translator->trans('you.successfully.liked.post'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function removeBackground(Request $request): Response
    {
        $user = $this->getUser();
        $user->removeBackground();
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.removed.background'));
        //return $this->json($this->translator->trans('you.successfully.liked.post'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function deleteImage(Request $request, User $user, ImageService $imageService, $image): Response
    {
        if ($this->isCsrfTokenValid('delete_image'.$image, $request->request->get('_token'))) {
            $images = $user->getImages();
            if ($images[$image] == $user->getBackground()) {
                $user->removeBackground();
            }

            if ($images[$image] == $user->getAvatar()) {
                $user->removeAvatar();
            }
            $user->removeImage($image);
            $file = $imageService->setImageExtension($images[$image]);
            $imageService->deleteFile($file);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.removed.image'));

            //return $this->json($this->translator->trans('you.successfully.removed.image'));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function moveImageUp(User $user, $image): Response
    {
        $user->moveImageUp($image);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.moved.image.up'));

        return $this->redirectToRoute('user_edit', ['id' => $user->getUuid()]);
        //return $this->json($this->translator->trans('you.successfully.moved.image.up'));
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function moveImageDown(User $user, $image): Response
    {
        $user->moveImageDown($image);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.moved.image.down'));

        return $this->redirectToRoute('user_edit', ['id' => $user->getUuid()]);
        //return $this->json($this->translator->trans('you.successfully.moved.image.up'));
    }

}
