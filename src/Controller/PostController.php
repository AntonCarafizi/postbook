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
use Doctrine\ORM\EntityManagerInterface;


class PostController extends AbstractController
{

    private $entityManager;

    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function index(Request $request, PostRepository $postRepository, $page, PaginatorInterface $paginator): Response
    {
        $search = ($request->query->get('search')) ? $request->query->get('search') : '';
        $from = ($request->query->get('from')) ? $request->query->get('from') : '';
        $to = ($request->query->get('to')) ? $request->query->get('to') : new \DateTime();

        $allPosts = ($search or $from or $to) ? $postRepository
            ->findByFilter(['search' => $search, 'from' => $from, 'to' => $to]) : $postRepository->findby([], ['id' => 'DESC']);

        $posts = $paginator->paginate(
            $allPosts,
            $request->query->getInt('page', $page),
            $this->getParameter('posts_per_page')
        );

        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    public function new(Request $request): Response
    {
        $post = new Post();
        $user = $this->getUser();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->addPost($post);
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            //return $this->json($this->translator->trans('post.successfully.created'));
            $this->addFlash('success', $this->translator->trans('post.successfully.created'));
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function show(Post $post): Response
    {
        if (!$post) {
            throw $this->createNotFoundException($this->translator->trans('post.not.found'));
        }

        return $this->render('post/show.html.twig', ['post' => $post]);
    }

    public function edit(Request $request, Post $post): Response
    {
        $this->denyAccessUnlessGranted('edit', $post);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $this->translator->trans('post.successfully.updated'));
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
            //return $this->json($this->translator->trans('post.successfully.updated'));
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    public function delete(Request $request, Post $post): Response
    {
        $this->denyAccessUnlessGranted('edit', $post);
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('post.successfully.deleted'));

            //return $this->json($this->translator->trans('post.successfully.deleted'));
        }

        return $this->redirectToRoute('post_index');
    }
}
