<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Rediriger vers la page de login
        return $this->redirectToRoute('app_login');
    }
    
    #[Route('/home', name: 'app_home_page')]
    public function home(): Response
    {
        // Rediriger vers la page de login si l'utilisateur tente d'accéder à /home
        return $this->redirectToRoute('app_login');
    }
} 