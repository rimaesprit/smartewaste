<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MicrosoftController extends AbstractController
{
    #[Route('/connect/microsoft', name: 'connect_microsoft_start')]
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        // Rediriger vers Microsoft pour l'authentification
        return $clientRegistry
            ->getClient('microsoft')
            ->redirect([
                'openid',
                'email',
                'profile',
                'User.Read'
            ]);
    }

    #[Route('/connect/microsoft/check', name: 'connect_microsoft_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry): Response
    {
        // Cette route est gérée par l'authenticator MicrosoftAuthenticator
        // Cette méthode ne sera jamais exécutée
        return new Response('Never called directly');
    }
} 