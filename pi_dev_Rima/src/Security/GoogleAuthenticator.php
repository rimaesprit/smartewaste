<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Security\OAuth2\GoogleNoVerifyProvider;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private GoogleNoVerifyProvider $googleProvider;
    private ClientRegistry $clientRegistry;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        GoogleNoVerifyProvider $googleProvider,
        ClientRegistry $clientRegistry,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->googleProvider = $googleProvider;
        $this->clientRegistry = $clientRegistry;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        // Vérifie si la requête est une callback d'authentification Google
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $this->logger->info('Début du processus d\'authentification Google');
        
        try {
            // Désactiver temporairement la vérification SSL pour faciliter le développement local
            $this->disableSslVerification();
            
            // Obtenir le client OAuth
            $client = $this->clientRegistry->getClient('google');
            $this->logger->info('Client OAuth récupéré');
            
            // Récupérer l'AccessToken
            try {
                // Ignorer les erreurs d'état en essayant d'appeler directement getAccessToken sur le provider
                if ($request->query->has('code') && !$request->query->has('state')) {
                    $this->logger->info('Contournement de la vérification d\'état activé');
                    $code = $request->query->get('code');
                    $accessToken = $this->googleProvider->getAccessToken('authorization_code', [
                        'code' => $code
                    ]);
                } else {
                    $accessToken = $client->getAccessToken();
                }
                
                $this->logger->info('AccessToken récupéré avec succès');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la récupération du token: ' . $e->getMessage());
                throw new AuthenticationException('Impossible d\'obtenir le token d\'accès: ' . $e->getMessage());
            }
            
            // Réactiver la vérification SSL
            $this->enableSslVerification();
            
            // Récupérer les informations utilisateur
            try {
                $googleUser = $client->fetchUserFromToken($accessToken);
                $this->logger->info('Informations utilisateur récupérées pour: ' . $googleUser->getEmail());
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la récupération des infos utilisateur: ' . $e->getMessage());
                throw new AuthenticationException('Impossible de récupérer les informations utilisateur: ' . $e->getMessage());
            }
            
            // Traiter l'utilisateur
            $email = $googleUser->getEmail();
            
            if (!$email) {
                $this->logger->error('Email manquant dans les données Google');
                throw new AuthenticationException('L\'email est nécessaire pour l\'authentification.');
            }
            
            // Rechercher l'utilisateur dans la base de données
            $existingUser = $this->userRepository->findOneBy(['email' => $email]);
            
            if ($existingUser) {
                $this->logger->info('Utilisateur existant trouvé: ' . $email);
                $user = $existingUser;
                
                // Mettre à jour les informations utilisateur si nécessaire
                $updateUser = false;
                
                if ($googleUser->getFirstName() && $user->getFirstName() !== $googleUser->getFirstName()) {
                    $user->setFirstName($googleUser->getFirstName());
                    $updateUser = true;
                }
                
                if ($googleUser->getLastName() && $user->getLastName() !== $googleUser->getLastName()) {
                    $user->setLastName($googleUser->getLastName());
                    $updateUser = true;
                }
                
                if (!$user->isVerified()) {
                    $user->setIsVerified(true);
                    $updateUser = true;
                }
                
                if ($updateUser) {
                    $this->logger->info('Mise à jour des informations utilisateur');
                    $this->entityManager->flush();
                }
            } else {
                // Créer un nouvel utilisateur
                $this->logger->info('Création d\'un nouvel utilisateur pour: ' . $email);
                $user = new User();
                $user->setEmail($email);
                $user->setFirstName($googleUser->getFirstName() ?? '');
                $user->setLastName($googleUser->getLastName() ?? '');
                $user->setIsVerified(true);
                
                // Générer un mot de passe aléatoire
                $randomPassword = bin2hex(random_bytes(16));
                $hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
                $user->setPassword($hashedPassword);
                
                // Définir les rôles
                $user->setRoles(['ROLE_USER']);
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->logger->info('Nouvel utilisateur créé avec succès');
            }
            
            // Créer le passport d'authentification
            return new SelfValidatingPassport(
                new UserBadge($email, function() use ($user) {
                    return $user;
                })
            );
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur non gérée: ' . $e->getMessage());
            throw new AuthenticationException('Erreur d\'authentification OAuth: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->info('Authentification Google réussie pour: ' . $token->getUserIdentifier());
        
        // Redirection basée sur le rôle de l'utilisateur
        $user = $token->getUser();
        $roles = $user->getRoles();
        
        if (in_array('ROLE_ADMIN', $roles)) {
            $this->logger->info('Redirection vers le dashboard admin');
            return new RedirectResponse($this->router->generate('app_admin_dashboard'));
        } else {
            $this->logger->info('Redirection vers la page front');
            return new RedirectResponse($this->router->generate('app_front_home'));
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->error('Échec de l\'authentification Google: ' . $exception->getMessage());
        
        $message = $exception->getMessage();
        $request->getSession()->set('google_oauth_error', $message);
        
        return new RedirectResponse($this->router->generate('app_login', [
            'error' => 'google_auth_error'
        ]));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
    
    /**
     * Désactive temporairement la vérification SSL pour le développement local
     */
    private function disableSslVerification(): void
    {
        // Configurer le contexte SSL par défaut
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        
        // Pour cURL
        if (function_exists('curl_setopt_array')) {
            // Stocker la configuration actuelle
            $this->originalCurlCaInfo = ini_get('curl.cainfo');
            
            // Désactiver la vérification SSL de cURL
            ini_set('curl.cainfo', '');
        }
        
        // Pour les requêtes qui utilisent directement les ressources cURL
        if (!defined('CURLOPT_SSL_VERIFYHOST')) {
            define('CURLOPT_SSL_VERIFYHOST', 2);
        }
        if (!defined('CURLOPT_SSL_VERIFYPEER')) {
            define('CURLOPT_SSL_VERIFYPEER', 64);
        }
        
        // Configuration des variables d'environnement
        putenv('CURLOPT_SSL_VERIFYHOST=0');
        putenv('CURLOPT_SSL_VERIFYPEER=0');
    }
    
    /**
     * Réactive la vérification SSL
     */
    private function enableSslVerification(): void
    {
        // Reconfigurer le contexte SSL par défaut
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        
        // Pour cURL
        if (function_exists('curl_setopt_array') && isset($this->originalCurlCaInfo) && $this->originalCurlCaInfo !== false) {
            ini_set('curl.cainfo', $this->originalCurlCaInfo);
        }
    }
    
    /**
     * @var string|false|null
     */
    private $originalCurlCaInfo;
} 