<?php

namespace App\Controller;

use App\Service\ArrayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LikeController extends AbstractController
{
    public function show(Request $request, $id, UserRepository $userRepository, PostRepository $postRepository, $page, PaginatorInterface $paginator): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);

        $likes = ($user->getLikes()) ? $user->getLikes() : [];

        $posts = $paginator->paginate(
            $likes = $postRepository->findBy(['id' => $likes], ['id' => 'DESC']),
            $request->query->getInt('page', $page),
            $this->getParameter('posts_per_page')
        );
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }


    public function new($like, Request $request, ArrayService $arrayService, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('add_like'.$like, $request->request->get('_token'))) {
            $likes = $user->getLikes();
            $arrayService->addElement($likes, $like);
            $user->setLikes($likes);
            $this->getDoctrine()->getManager()->flush();
            return $this->json($translator->trans('you.successfully.liked.post'));
        }
        return $this->redirectToRoute('user_likes', ['id' => $user->getId()]);
    }

    public function delete($like, Request $request, ArrayService $arrayService, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete_like'.$like, $request->request->get('_token'))) {
            $likes = $user->getLikes();
            $arrayService->deleteElementByValue($likes, $like);
            $user->setLikes($likes);
            $this->getDoctrine()->getManager()->flush();
            return $this->json($translator->trans('you.successfully.unliked.post'));
        }
        return $this->redirectToRoute('user_likes', ['id' => $user->getId()]);
    }
}