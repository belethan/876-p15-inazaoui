<?php

namespace App\Controller\Admin;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(Request $request, AuthenticationUtils $auth): Response
    {
        $error = $auth->getLastAuthenticationError();
        $lastEmail = $auth->getLastUsername();

        $form = $this->createForm(LoginType::class, [
            'email' => $lastEmail
        ]);

        return $this->render('admin/security/login.html.twig', [
            'form' => $form,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Symfony gère la déconnexion, aucun code ici.
    }
}
