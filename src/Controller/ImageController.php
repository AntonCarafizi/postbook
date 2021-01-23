<?php

namespace App\Controller;

use App\Form\ImageType;
use App\Service\ArrayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ImageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class ImageController extends AbstractController
{
    private $entityManager;

    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function new(Request $request, $id, UserRepository $userRepository, ImageService $imageService): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }
        $itemImages = $user->getImages();
        if (!$itemImages) {
            throw $this->createNotFoundException($this->translator->trans('images.not.found'));
        }
        $form = $this->createForm(ImageType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            $formImages = $imageService->uploadFiles($imageFiles);
            $itemImages['all'] = array_merge($itemImages['all'], $formImages);
            $user->setImages($itemImages);
            $this->entityManager->flush();
            return $this->json($this->translator->trans('you.successfully.uploaded.image'));
        }

        return $this->render('user/images/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    public function up(User $user, ArrayService $arrayService, $image): Response
    {
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }
        $images = $user->getImages();
        if (!$images) {
            throw $this->createNotFoundException($this->translator->trans('images.not.found'));
        }
        $arrayService->moveElement($images['all'], $image, $image - 1);
        $user->setImages($images);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.moved.image.up'));

        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        //return $this->json($this->translator->trans('you.successfully.moved.image.up'));
    }

    public function down(User $user, ArrayService $arrayService, $image): Response
    {
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }
        $images = $user->getImages();
        if (!$images) {
            throw $this->createNotFoundException($this->translator->trans('images.not.found'));
        }
        $arrayService->moveElement($images['all'], $image, $image + 1);
        $user->setImages($images);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.moved.image.down'));

        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        //return $this->json($this->translator->trans('you.successfully.moved.image.down'));
    }

    public function main(User $user, $image): Response
    {
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }
        $images = $user->getImages();
        if (!$images) {
            throw $this->createNotFoundException($this->translator->trans('images.not.found'));
        }
        $images['avatar'] = $images['all'][$image];
        $user->setImages($images);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.selected.avatar'));

        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        //return $this->json($this->translator->trans('you.successfully.selected.avatar'));
    }

    public function background(User $user, $image): Response
    {
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }
        $images = $user->getImages();
        if (!$images) {
            throw $this->createNotFoundException($this->translator->trans('images.not.found'));
        }
        $images['background'] = $images['all'][$image];
        $user->setImages($images);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('you.successfully.selected.background'));

        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        //return $this->json($this->translator->trans('you.successfully.selected.background'));
    }

    public function delete(Request $request, User $user, ArrayService $arrayService, ImageService $imageService, $image): Response
    {
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user.not.found'));
        }
        if ($this->isCsrfTokenValid('delete_image'.$image, $request->request->get('_token'))) {
            $images = $user->getImages();
            if (!$images) {
                throw $this->createNotFoundException($this->translator->trans('images.not.found'));
            }
            if ($images['all'][$image] == $images['background']) {
                $images['background'] = null;
            }

            if ($images['all'][$image] == $images['avatar']) {
                $images['avatar'] = null;
            }
            $file = $imageService->setImageExtension($images['all'][$image]);
            $imageService->deleteFile($file);
            $arrayService->deleteElementByKey($images['all'], $image, 1);
            $user->setImages($images);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('you.successfully.removed.image'));

            //return $this->json($this->translator->trans('you.successfully.removed.image'));
        }
        return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
    }
}