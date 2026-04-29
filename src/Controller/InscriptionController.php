<?php
// src/Controller/InscriptionController.php
namespace App\Controller;

use App\Entity\Client;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $client = new Client();
        $form   = $this->createForm(InscriptionType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setPassword(
                $hasher->hashPassword($client, $form->get('plainPassword')->getData())
            );
            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Compte créé ! Connectez-vous.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/inscription.html.twig', [
            'inscriptionForm' => $form,
        ]);
    }
}