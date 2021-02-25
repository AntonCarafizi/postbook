<?php

namespace App\Controller;


use App\Repository\UserRepository;
use App\Service\ArrayService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class VisitorController extends AbstractController
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
        $getUser = $userRepository->findOneby(['id' => $this->getUser()->getId()]);

        $userVisitors = $userRepository->findby(['id' => array_keys($getUser->getVisitors())], ['id' => 'DESC']);

        $users = $paginator->paginate(
            $userVisitors,
            $request->query->getInt('page', $page),
            $this->getParameter('users_per_page')
        );

        return $this->render('user/index.html.twig', ['users' => $users, 'title' => 'my.visitors']);
    }
}