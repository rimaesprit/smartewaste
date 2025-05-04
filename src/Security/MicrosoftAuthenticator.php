<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
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

class MicrosoftAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_microsoft_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('microsoft');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var ResourceOwnerInterface $microsoftUser */
                $microsoftUser = $client->fetchUserFromToken($accessToken);
                $userData = $microsoftUser->toArray();

                $email = $userData['mail'] ?? $userData['userPrincipalName'] ?? null;
                
                if (!$email) {
                    throw new AuthenticationException('Impossible de récupérer l\'email de l\'utilisateur Microsoft.');
                }

                // Chercher si l'utilisateur existe déjà
                $existingUser = $this->userRepository->findOneBy(['email' => $email]);

                // Si l'utilisateur existe, retournez-le
                if ($existingUser) {
                    return $existingUser;
                }

                // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
                $user = new User();
                $user->setEmail($email);
                $user->setFirstName($userData['givenName'] ?? '');
                $user->setLastName($userData['surname'] ?? '');
                $user->setIsVerified(true); // L'email est déjà vérifié par Microsoft
                
                // Génération d'un mot de passe aléatoire
                $randomPassword = bin2hex(random_bytes(16));
                $hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
                $user->setPassword($hashedPassword);
                
                // Définir les rôles
                $user->setRoles(['ROLE_USER']);
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirection basée sur le rôle de l'utilisateur
        $user = $token->getUser();
        $roles = $user->getRoles();
        
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->router->generate('app_admin_dashboard'));
        } else {
            return new RedirectResponse($this->router->generate('app_front_home'));
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        
        return new RedirectResponse($this->router->generate('app_login', [
            'error' => $message
        ]));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
} 