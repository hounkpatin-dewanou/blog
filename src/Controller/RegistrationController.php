<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    // On garde le constructeur pour ne pas casser tes futurs travaux sur l'email, 
    // mais on met l'EmailVerifier en commentaire si tu ne l'utilises pas tout de suite.
    // public function __construct (private readonly EmailVerifier $emailVerifier) {}

    #[Route('/register', name: 'app_register')]
    public function register (
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            
            $em->persist($user);
            $em->flush();

            // --- LE MESSAGE VERT ---
            $this->addFlash('success', 'Votre compte a été créé avec succès ! Connectez-vous avec vos identifiants.');

            // --- REDIRECTION VERS LOGIN ---
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    /* PARTIES INUTILES POUR L'INSTANT MISES EN COMMENTAIRE 
    #[Route('/validate', name: 'app_validate_account')]
    public function validate (): Response
    {
        return $this->render('registration/validate.html.twig');
    }
    */
}