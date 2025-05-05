<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        $user = $this->getUser();
        $originalEmail = $user->getEmail(); // Store original email
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if email has been changed
            if ($originalEmail !== $user->getEmail()) {
                // Generate a verification token
                $token = $tokenGenerator->generateToken();
                $user->setEmailVerificationToken($token);
                
                // Store the new email temporarily
                $user->setTempEmail($user->getEmail());
                
                // Revert to original email until verification is complete
                $user->setEmail($originalEmail);
                
                // Create verification URL with absolute path
                $verifyUrl = $this->generateUrl(
                    'app_verify_email',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                
                // Send verification email
                $email = (new TemplatedEmail())
                    ->from(new Address('stoustou419@gmail.com', 'SmartWaste'))
                    ->to($user->getTempEmail())
                    ->subject('Vérification de votre nouvelle adresse email')
                    ->htmlTemplate('profile/verify_email.html.twig')
                    ->context([
                        'token' => $token,
                        'user' => $user,
                        'verifyUrl' => $verifyUrl,
                    ]);
                
                $mailer->send($email);
                
                $this->addFlash('info', 'Un email de vérification a été envoyé à l\'adresse ' . $user->getTempEmail() . '. Veuillez suivre le lien dans cet email pour confirmer votre nouvelle adresse. Votre email actuel restera inchangé jusqu\'à la vérification.');
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            
            return $this->redirectToRoute('app_profile_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    
    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]);
        
        if (!$user) {
            $this->addFlash('error', 'Token de vérification invalide.');
            return $this->redirectToRoute('app_profile_index');
        }
        
        // If temporary email exists, update the actual email
        if ($user->getTempEmail()) {
            $user->setEmail($user->getTempEmail());
            $user->setTempEmail(null);
        }
        
        // Clear the verification token
        $user->setEmailVerificationToken(null);
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Votre nouvelle adresse email ' . $user->getEmail() . ' a été vérifiée avec succès et est maintenant votre adresse principale.');
        return $this->redirectToRoute('app_profile_index');
    }
    
    #[Route('/change-password', name: 'app_profile_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Vérifie si l'ancien mot de passe est correct
            if (!$passwordHasher->isPasswordValid($user, $data['oldPassword'])) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('app_profile_change_password');
            }
            
            // Encode le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $data['newPassword']
            );
            
            // Met à jour le mot de passe
            $user->setPassword($hashedPassword);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
            
            return $this->redirectToRoute('app_profile_index');
        }
        
        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update-preferences', name: 'app_profile_update_preferences', methods: ['POST'])]
    public function updatePreferences(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        
        // Mise à jour du thème
        if ($request->request->has('theme')) {
            $theme = $request->request->get('theme');
            $user->setTheme($theme);
        }
        
        // Mise à jour des préférences individuelles
        if ($request->request->has('notification_enabled')) {
            $notificationEnabled = $request->request->get('notification_enabled') === '1';
            $user->updatePreference('notifications', $notificationEnabled);
        }
        
        if ($request->request->has('newsletter_enabled')) {
            $newsletterEnabled = $request->request->get('newsletter_enabled') === '1';
            $user->updatePreference('newsletter', $newsletterEnabled);
        }
        
        // Mise à jour de la langue
        if ($request->request->has('language')) {
            $language = $request->request->get('language');
            $user->setLanguage($language);
        }
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Vos préférences ont été mises à jour avec succès.');
        
        // Redirection vers la page précédente si disponible, sinon vers le profil
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_profile_index');
    }
} 