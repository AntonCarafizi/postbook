<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;

class PostController extends AbstractController
{

    public function index(Request $request, PostRepository $postRepository, $page, PaginatorInterface $paginator, TranslatorInterface $translator): Response
    {
        $search = $request->query->get('search');

        $allPosts = ($search) ? $postRepository->findByTitle(['title' => $search]) : $postRepository->findby([], ['id' => 'DESC']);

        if (!$allPosts) {
            throw $this->createNotFoundException($translator->trans('posts.not.found'));
        }

        $posts = $paginator->paginate(
            $allPosts,
            $request->query->getInt('page', $page),
            $this->getParameter('posts_per_page')
        );

        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $post = new Post();
        $user = $this->getUser();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->addPost($post);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->json($translator->trans('post.successfully.created'));
            //return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function show(Post $post, TranslatorInterface $translator): Response
    {
        if (!$post) {
            throw $this->createNotFoundException($translator->trans('post.not.found'));
        }

        return $this->render('post/show.html.twig', ['post' => $post]);
    }

    public function edit(Request $request, TranslatorInterface $translator, Post $post): Response
    {
        $this->denyAccessUnlessGranted('edit', $post);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->json($translator->trans('post.successfully.updated'));
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function delete(Request $request, Post $post, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('edit', $post);
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
            return $this->json($translator->trans('post.successfully.deleted'));
        }

        return $this->redirectToRoute('post_index');
    }
}
