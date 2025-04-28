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
        // Rediriger vers la liste des camions
        return $this->redirectToRoute('app_camion_index');
    }
    
    #[Route('/home', name: 'app_home_page')]
    public function home(): Response
    {
        // Rediriger vers la liste des camions également
        return $this->redirectToRoute('app_camion_index');
    }
} 