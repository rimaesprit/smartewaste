<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifie si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            // Ajout de notification pour déboguer
            $this->addFlash('success', 'Vous êtes déjà connecté en tant que ' . $this->getUser()->getUserIdentifier());
            return $this->redirectToRoute('app_front_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Si une erreur est présente, loguer l'erreur pour débogage
        if ($error) {
            $errorMessage = $error->getMessage();
            $this->addFlash('error', 'Erreur de connexion détectée: ' . $errorMessage);
        }
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager
    ): Response
    {
        // Vérifie si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Récupérer les valeurs des mots de passe
            $password = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();
            
            // Validation manuelle simple
            $validForm = true;
            
            // Vérifier les mots de passe
            if (empty($password)) {
                $this->addFlash('error', 'Veuillez entrer un mot de passe');
                $validForm = false;
            } elseif (empty($confirmPassword)) {
                $this->addFlash('error', 'Veuillez confirmer votre mot de passe');
                $validForm = false;
            } elseif ($password !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas');
                $validForm = false;
            } elseif (strlen($password) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères');
                $validForm = false;
            }
            
            // Si le formulaire est valide
            if ($validForm && $form->isValid()) {
                try {
                    // Hasher le mot de passe
                    $user->setPassword($userPasswordHasher->hashPassword($user, $password));
                    
                    // Déterminer le rôle en fonction du domaine email
                    $email = $user->getEmail();
                    if (str_ends_with($email, '@esprit.tn')) {
                        // Attribution du rôle admin pour les emails @esprit.tn
                        $user->setRoles(['ROLE_ADMIN']);
                        $this->addFlash('info', 'Compte administrateur créé avec succès.');
                    } else {
                        // Rôle utilisateur standard pour les autres domaines
                        $user->setRoles(['ROLE_USER']);
                    }
                    
                    // L'utilisateur n'est pas encore vérifié
                    $user->setIsVerified(false);
                    
                    // Générer un token de vérification directement ici
                    $token = bin2hex(random_bytes(32));
                    $user->setVerificationToken($token);
                    
                    // Persister l'utilisateur
                    $entityManager->persist($user);
                    $entityManager->flush();

                    // Informations de débogage pour le mailer
                    $mailerDsn = $_ENV['MAILER_DSN'] ?? 'Non configuré';
                    $this->addFlash('debug', '[DEBUG] Configuration du mailer: ' . $mailerDsn);
                    
                    // Envoyer un email de vérification via le service EmailVerifier
                    try {
                        $this->emailVerifier->sendEmailConfirmation(
                            'app_verify_email', 
                            $user
                        );
                        $this->addFlash('debug', '[DEBUG] Service EmailVerifier exécuté sans erreur');
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
                        $this->addFlash('debug', '[DEBUG] Exception dans EmailVerifier: ' . $e->getMessage());
                        $this->addFlash('debug', '[DEBUG] Trace: ' . substr($e->getTraceAsString(), 0, 500) . '...');
                    }
                    
                    // Message pour l'utilisateur
                    $this->addFlash('success', 'Votre compte a été créé avec succès! Un email de vérification a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception pour activer votre compte.');
                    
                    // Pour le développement uniquement - si le mailer est en mode null, afficher aussi le lien de vérification
                    if ($_ENV['APP_ENV'] === 'dev' && (str_contains($_ENV['MAILER_DSN'] ?? '', 'null://') || str_contains($_ENV['MAILER_DSN'] ?? '', 'gmail://'))) {
                        $verificationUrl = $this->generateUrl('app_verify_email', 
                            ['token' => $user->getVerificationToken()], 
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                        $this->addFlash('info', 'Pour faciliter le test: <a href="' . $verificationUrl . '">Cliquez ici pour vérifier votre email</a>');
                    }
                    
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la création: ' . $e->getMessage());
                }
            }
            
            return $this->redirectToRoute('app_register');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $token = $request->query->get('token');
        
        if (null === $token) {
            return $this->redirectToRoute('app_login');
        }
        
        $user = $userRepository->findOneBy(['verificationToken' => $token]);
        
        if (null === $user) {
            $this->addFlash('error', 'Le lien de vérification est invalide ou a expiré.');
            return $this->redirectToRoute('app_login');
        }
        
        // Valider l'email de l'utilisateur
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $userRepository->save($user, true);
        
        $this->addFlash('success', 'Votre adresse email a été vérifiée avec succès! Vous pouvez maintenant vous connecter.');
        
        return $this->redirectToRoute('app_login');
    }
} 