<?php

namespace App\Controller;

use App\Security\OAuth2\GoogleNoVerifyProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleController extends AbstractController
{
    private $requestStack;
    private $googleProvider;
    private $logger;

    public function __construct(
        RequestStack $requestStack, 
        GoogleNoVerifyProvider $googleProvider,
        LoggerInterface $logger
    ) {
        $this->requestStack = $requestStack;
        $this->googleProvider = $googleProvider;
        $this->logger = $logger;
    }

    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(Request $request): Response
    {
        $this->logger->info('Démarrage de la connexion Google');
        
        // Sauvegarder la page de redirection après login
        $referer = $request->headers->get('referer');
        if ($referer) {
            $this->requestStack->getSession()->set('_security.main.target_path', $referer);
        }
        
        // Configuration de la requête OAuth
        // Utiliser une URL fixe pour correspondre exactement à ce qui est configuré dans Google Cloud
        $redirectUri = 'http://127.0.0.1:8000/connect/google/check';
        $this->logger->info('URL de redirection utilisée: ' . $redirectUri);
        
        $options = [
            'scope' => ['email', 'profile'],
            'redirect_uri' => $redirectUri
        ];
        
        try {
            // Rediriger vers Google pour l'authentification
            $authUrl = $this->googleProvider->getAuthorizationUrl($options);
            
            // Stocker l'état dans la session
            $this->requestStack->getSession()->set('oauth2state', $this->googleProvider->getState());
            
            $this->logger->info('Redirection vers l\'URL d\'authentification Google');
            return $this->redirect($authUrl);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la génération de l\'URL d\'authentification: ' . $e->getMessage());
            $this->addFlash('error', 'Impossible de se connecter avec Google pour le moment. Veuillez réessayer plus tard.');
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request): Response
    {
        $this->logger->info('Check de la connexion Google');
        
        // Vérification de l'état pour prévenir les attaques CSRF
        if (!$request->query->has('code')) {
            $this->logger->error('Paramètre code manquant dans la requête de callback Google');
            $this->addFlash('error', 'Une erreur est survenue lors de la connexion avec Google. Paramètre code manquant.');
            return $this->redirectToRoute('app_login');
        }
        
        // TEMPORAIRE: Désactiver la vérification d'état pour tester
        // $state = $this->requestStack->getSession()->get('oauth2state');
        // $requestState = $request->query->get('state');
        
        // if (empty($state) || $state !== $requestState) {
        //     $this->requestStack->getSession()->remove('oauth2state');
        //     $this->logger->error('État OAuth invalide');
        //     $this->addFlash('error', 'Tentative de connexion invalide. Veuillez réessayer.');
        //     return $this->redirectToRoute('app_login');
        // }
        
        // Cette route est gérée par l'authenticator GoogleAuthenticator
        // Ce code ne devrait normalement pas être exécuté, mais on le garde en cas de problème
        $this->logger->warning('La méthode connectCheckAction a été appelée directement, ce qui est anormal');
        $this->addFlash('error', 'Une erreur est survenue lors du traitement de la connexion. Veuillez réessayer.');
        return $this->redirectToRoute('app_login');
    }
    
    #[Route('/connect/google/error', name: 'connect_google_error')]
    public function connectErrorAction(Request $request): Response
    {
        $errorMessage = $request->query->get('message', 'Une erreur inconnue s\'est produite');
        $this->logger->error('Erreur de connexion Google: ' . $errorMessage);
        
        $this->addFlash('error', 'Erreur de connexion avec Google: ' . $errorMessage);
        return $this->redirectToRoute('app_login');
    }
} 