<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\JsonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;


class PostController extends AbstractController
{

    private $entityManager;
    private $translator;
    private $postRepository;
    private $paginator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator, PostRepository $postRepository, PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
    }

    public function index(Request $request, $page): Response
    {
        $search = $request->query->get('search');
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        $allPosts = $this->postRepository->findByFilter(['search' => $search, 'from' => $from, 'to' => $to], $this->getParameter('date_format'));

        $posts = $this->paginator->paginate(
        $allPosts,
        $request->query->getInt('page', $page),
        $this->getParameter('posts_per_page')
        );


        /*if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
            $response = $jsonService->convert($posts);
            return new JsonResponse($response, Response::HTTP_OK, [], true);
        } else { */
            return $this->render('post/index.html.twig', ['posts' => $posts, 'title' => 'all.posts']);
        //}
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
        return $this->render('post/show.html.twig', ['post' => $post]);
    }

    public function edit(Request $request, Post $post): Response
    {
        $this->denyAccessUnlessGranted('edit', $post, $this->translator->trans('you.cant.edit.this.post'));
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
