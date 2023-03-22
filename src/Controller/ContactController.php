<?php

namespace App\Controller;

use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // récupérer les données du formulaire
            $Information = $form->getData();
            // enregister les données
            $entityManager->persist($Information);
            $entityManager->flush();
            // Envoyer l'email
            $email = (new Email())
                ->from($Information->getEmail())
                ->to('erwan.hamza56@gmail.com')
                ->subject('Nouveau message de contact')
                ->html('
                    <h3>Nouveau message de contact</h3>
                    <p><strong>Nom:</strong> ' . $Information->getName() . '</p>
                    <p><strong>Email:</strong> ' . $Information->getEmail() . '</p>
                    <p><strong>Message:</strong> ' . $Information->getMessage() . '</p>
                ');

            $mailer->send($email);

            // Rediriger l'utilisateur vers une page de confirmation
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function confirmation(): Response
    {
        return $this->render('homepage/index.html.twig');
    }
   
}
