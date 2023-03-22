<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends AbstractController
{
    #[Route("/admin/images", name: 'admin_images')]
    public function images(Request $request, EntityManagerInterface $entityManager): Response
    {
        $images = $entityManager->getRepository(Image::class)->findAll();

        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('fichier')->getData() !== null) {
                // Génère un nom de fichier unique
                $nomFichier = md5(uniqid()) . '.' . $form->get('fichier')->getData()->guessExtension();

                $form->get('fichier')->getData()->move(
                    $this->getParameter('kernel.project_dir') . '/public/build/images',
                    $nomFichier
                );

                $image->setNom($nomFichier);
                $entityManager->persist($image);
                $entityManager->flush();

                $this->addFlash('success', 'L\'image a bien été ajoutée.');
            } elseif ($form->get('nom')->getData() !== null) {
                $image = $entityManager->getRepository(Image::class)->findOneBy([
                    'nom' => $form->get('nom')->getData(),
                ]);
                $image->setDescription($form->get('description')->getData());
                $entityManager->flush();

                $this->addFlash('success', 'L\'image a bien été modifiée.');
            }
        }

        return $this->render('admin/images.html.twig', [
            'form' => $form->createView(),
            'images' => $images,
        ]);
    }
}