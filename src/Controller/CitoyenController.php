<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/citoyen')]
class CitoyenController extends AbstractController
{
    #[Route('/', name: 'app_citoyen_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        return $this->render('citoyen/index.html.twig', [
            'controller_name' => 'CitoyenController',
        ]);
    }
} 