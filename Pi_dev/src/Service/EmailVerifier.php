<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailVerifier
{
    private $requestStack;

    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private ?LoggerInterface $logger = null,
        private ?ParameterBagInterface $params = null,
        ?RequestStack $requestStack = null
    ) {
        $this->requestStack = $requestStack;
    }

    private function addFlash(string $type, string $message): void
    {
        if ($this->requestStack && $this->requestStack->getSession()) {
            $this->requestStack->getSession()->getFlashBag()->add($type, $message);
        }
    }

    public function sendEmailConfirmation(
        string $verifyEmailRouteName,
        User $user
    ): void {
        try {
            // Ajouter un message de débogage
            $debug = "[DEBUG] Début du processus d'envoi d'email de vérification à " . $user->getEmail();
            
            if ($this->logger) {
                $this->logger->info($debug);
            }
            
            $this->addFlash('debug', $debug);
            
            // Générer un token aléatoire
            $token = bin2hex(random_bytes(32));
            $user->setVerificationToken($token);
            
            // Sauvegarder le token dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $debug .= "\n[DEBUG] Token généré et sauvegardé: " . substr($token, 0, 10) . "...";
            
            if ($this->logger) {
                $this->logger->info($debug);
            }
            
            $this->addFlash('debug', $debug);
            
            // Générer l'URL de vérification
            $verifyUrl = $this->urlGenerator->generate(
                $verifyEmailRouteName,
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            
            $debug .= "\n[DEBUG] URL de vérification générée: " . $verifyUrl;
            
            if ($this->logger) {
                $this->logger->info($debug);
            }
            
            $this->addFlash('debug', $debug);

            // Créer l'email avec les détails MAIL_DSN
            $mailerDsn = $_ENV['MAILER_DSN'] ?? 'Aucun MAILER_DSN configuré';
            $fromEmail = "stoustou419@gmail.com";
            $fromName = "SmartWaste";
            
            $debug .= "\n[DEBUG] Configuration du mailer: " . $mailerDsn;
            $debug .= "\n[DEBUG] From email: " . $fromEmail;
            
            if ($this->logger) {
                $this->logger->info($debug);
            }
            
            $this->addFlash('debug', $debug);
            
            // Créer l'email
            $email = (new TemplatedEmail())
                ->from(new Address($fromEmail, $fromName))
                ->to($user->getEmail())
                ->subject('Confirmation de votre adresse email')
                ->htmlTemplate('email/verification_email.html.twig')
                ->context([
                    'verifyUrl' => $verifyUrl,
                    'user' => $user
                ]);
            
            $debug .= "\n[DEBUG] Email préparé pour " . $user->getEmail();
            
            if ($this->logger) {
                $this->logger->info($debug);
            }
            
            $this->addFlash('debug', $debug);
            
            // Envoyer l'email
            $this->mailer->send($email);
            
            $debug .= "\n[DEBUG] Email envoyé avec succès!";
            
            if ($this->logger) {
                $this->logger->info($debug);
            }
            
            $this->addFlash('debug', $debug);
            $this->addFlash('success', 'Email de vérification envoyé à ' . $user->getEmail());
        } catch (\Exception $e) {
            $error = "Erreur lors de l'envoi de l'email: " . $e->getMessage();
            
            if ($this->logger) {
                $this->logger->error($error);
                $this->logger->error($e->getTraceAsString());
            }
            
            $this->addFlash('error', $error);
            $this->addFlash('debug', "[DEBUG] Trace: " . substr($e->getTraceAsString(), 0, 500) . "...");
            
            // Ne pas propager l'exception pour ne pas bloquer l'inscription
            // mais on enregistre l'erreur dans les logs
        }
    }

    public function verifyEmail(Request $request, User $user): bool
    {
        $token = $request->query->get('token');

        if ($token !== $user->getVerificationToken()) {
            if ($this->logger) {
                $this->logger->warning('Tentative de vérification avec un token invalide');
            }
            return false;
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        if ($this->logger) {
            $this->logger->info('Email vérifié avec succès pour l\'utilisateur ' . $user->getEmail());
        }

        return true;
    }
} 