<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    #[Route('', name: 'app_forgot_password_request')]
    public function request(
        Request $request, 
        MailerInterface $mailer, 
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findByEmail($email);

            // Do not reveal whether a user account was found or not
            if (!$user) {
                $this->addFlash('success', 'Un email a été envoyé avec les instructions pour réinitialiser votre mot de passe.');
                return $this->redirectToRoute('app_login');
            }

            // Generate reset token
            $resetToken = $tokenGenerator->generateToken();
            $user->setResetToken($resetToken);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('noreply@smartwaste.com', 'SmartWaste'))
                ->to($user->getEmail())
                ->subject('Votre demande de réinitialisation de mot de passe')
                ->htmlTemplate('security/reset_password_email.html.twig')
                ->context([
                    'resetToken' => $resetToken,
                    'user' => $user,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Un email a été envoyé avec les instructions pour réinitialiser votre mot de passe.');
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire est soumis mais invalide, il faut quand même rediriger
        if ($form->isSubmitted() && !$form->isValid()) {
            // Stocker les erreurs en session flash
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            
            // Rediriger vers la même page pour recharger avec Turbo
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ?string $token = null
    ): Response
    {
        if ($token) {
            $user = $userRepository->findByResetToken($token);

            if (!$user) {
                $this->addFlash('danger', 'Le lien de réinitialisation est invalide ou expiré.');
                return $this->redirectToRoute('app_forgot_password_request');
            }
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once
            $user->setResetToken(null);

            // Encode the plain password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé, vous pouvez maintenant vous connecter.');
            
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire est soumis mais invalide, il faut quand même rediriger
        if ($form->isSubmitted() && !$form->isValid()) {
            // Stocker les erreurs en session flash
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            
            // Rediriger vers la même page pour recharger avec Turbo
            return $this->redirectToRoute('app_reset_password', ['token' => $token]);
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
} 