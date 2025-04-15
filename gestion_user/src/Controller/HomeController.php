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
        // Si l'utilisateur est déjà connecté, le garder sur la page d'accueil
        if ($this->getUser()) {
            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
        }
        
        // Sinon, rediriger vers la page de connexion
        return $this->redirectToRoute('app_login');
    }
    
    #[Route('/home', name: 'app_home_page')]
    public function home(): Response
    {
        // Si l'utilisateur est déjà connecté, le garder sur la page d'accueil
        if ($this->getUser()) {
            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
        }
        
        // Sinon, rediriger vers la page de connexion
        return $this->redirectToRoute('app_login');
    }
}
