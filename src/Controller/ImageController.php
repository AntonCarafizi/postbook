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

class ImageController extends AbstractController
{
    public function new(Request $request, $id, UserRepository $userRepository, ImageService $imageService, TranslatorInterface $translator): Response
    {
        $user = (is_null($id)) ? $this->getUser() : $userRepository->findOneBy(['id' => $id]);

        $itemImages = $user->getImages();
        $form = $this->createForm(ImageType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            $formImages = $imageService->uploadFiles($imageFiles);
            $images = array_merge($itemImages, $formImages);
            $user->setImages($images);
            $this->getDoctrine()->getManager()->flush();
            return $this->json($translator->trans('you.successfully.uploaded.image'));
        }

        return $this->render('user/images/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    public function up(User $user, ArrayService $arrayService, $image, TranslatorInterface $translator): Response
    {
        $images = $user->getImages();
        $arrayService->moveElement($images, $image, $image - 1);
        $user->setImages($images);
        $this->getDoctrine()->getManager()->flush();
        return $this->json($translator->trans('you.successfully.moved.image.up'));

    }

    public function down(User $user, ArrayService $arrayService, $image, TranslatorInterface $translator): Response
    {
        $images = $user->getImages();
        $arrayService->moveElement($images, $image, $image + 1);
        $user->setImages($images);
        $this->getDoctrine()->getManager()->flush();
        return $this->json($translator->trans('you.successfully.moved.image.down'));
    }

    public function main(User $user, ArrayService $arrayService, $image, TranslatorInterface $translator): Response
    {
        $images = $user->getImages();
        $arrayService->moveElement($images, $image, 0);
        $user->setImages($images);
        $this->getDoctrine()->getManager()->flush();
        return $this->json($translator->trans('you.successfully.selected.avatar'));
    }

    public function background(User $user, ArrayService $arrayService, $image, TranslatorInterface $translator): Response
    {
        $images = $user->getImages();
        $arrayService->moveElement($images, $image, 1);
        $user->setImages($images);
        $this->getDoctrine()->getManager()->flush();
        return $this->json($translator->trans('you.successfully.selected.background'));
    }

    public function delete(Request $request, User $user, ArrayService $arrayService, ImageService $imageService, $image, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete_image'.$image, $request->request->get('_token'))) {
            $images = $user->getImages();
            $file = $imageService->setImageExtension($images[$image]);
            $imageService->deleteFile($file);
            $arrayService->deleteElementByKey($images, $image, 1);
            $user->setImages($images);
            $this->getDoctrine()->getManager()->flush();
            return $this->json($translator->trans('you.successfully.removed.image'));
        }
        return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
    }
}