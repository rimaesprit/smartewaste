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
use Knp\Component\Pager\PaginatorInterface;


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

    #[Route('/stats', name: 'admin_stats')]
    public function dashboard(UserRepository $userRepository): Response
    {
        // Ensure only admins can access this
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get all user counts
        $totalUsers = $userRepository->count([]);
        $adminCount = $userRepository->count(['roles' => ['ROLE_ADMIN']]);
        $userCount = $totalUsers - $adminCount; // Regular users count
        $verifiedCount = $userRepository->count(['isVerified' => true]);
        $unverifiedCount = $totalUsers - $verifiedCount;

        return $this->render('admin/stats_dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'adminCount' => $adminCount,
            'userCount' => $userCount,
            'verifiedCount' => $verifiedCount,
            'unverifiedCount' => $unverifiedCount,
        ]);
    }


    #[Route('/users', name: 'app_admin_users')]
    public function usersList(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Vérifier que l'utilisateur est bien un ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get all users query
        $query = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        // Paginate the query
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Current page, default to 1
            2 // Items per page
        );

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

    #[Route('/users/search', name: 'app_admin_users_search', methods: ['GET'])]
    public function searchUsers(Request $request, UserRepository $userRepository): Response
    {
        // Vérifier que l'utilisateur est bien un ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer les paramètres de recherche
        $query = $request->query->get('query');
        $role = $request->query->get('role');
        $verified = $request->query->get('verified');
        
        // Convertir la chaîne 'verified' en booléen si présent
        if ($verified !== null && $verified !== '') {
            $verified = ($verified === 'true' || $verified === '1');
        } else {
            $verified = null;
        }

        // Rechercher les utilisateurs
        $users = $userRepository->searchUsers($query, $role, $verified);
        
        // Si c'est une requête AJAX, renvoyer seulement le contenu du tableau
        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/_users_list.html.twig', [
                'users' => $users,
            ]);
        }
        
        // Sinon, renvoyer la page complète
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
} 