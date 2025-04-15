<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        // Vérifier que l'utilisateur est bien un ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer tous les utilisateurs pour le tableau
        $users = $userRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function usersList(UserRepository $userRepository): Response
    {
        // Vérifier que l'utilisateur est bien un ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[Route('/users/view/{id}', name: 'app_admin_user_view')]
    public function viewUser(User $user): Response
    {
        // Vérifier que l'utilisateur est bien un ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('admin/user_view.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/users/edit/{id}', name: 'app_admin_user_edit')]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est bien un ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $form = $this->createFormBuilder($user)
            ->add('firstName', null, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', null, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('roles', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER'
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('isVerified', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Compte vérifié',
                'required' => false,
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur modifié avec succès');
            
            return $this->redirectToRoute('app_admin_users');
        }
        
        return $this->render('admin/user_edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/users/delete/{id}', name: 'app_admin_user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Vérifier que l'utilisateur est bien un ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            // Supprimer l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        }
        
        return $this->redirectToRoute('app_admin_users');
    }
} 