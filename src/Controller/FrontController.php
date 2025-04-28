<?php

namespace App\Controller;

use App\Repository\CamionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front')]
class FrontController extends AbstractController
{
    #[Route('/', name: 'app_front_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('front/index.html.twig');
    }
    
    #[Route('/about', name: 'app_front_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
    
    #[Route('/services', name: 'app_front_services', methods: ['GET'])]
    public function services(): Response
    {
        return $this->render('front/services.html.twig');
    }
    
    #[Route('/contact', name: 'app_front_contact', methods: ['GET'])]
    public function contact(): Response
    {
        return $this->render('front/contact.html.twig');
    }
    
    #[Route('/map', name: 'app_front_map', methods: ['GET'])]
    public function map(CamionRepository $camionRepository): Response
    {
        $camions = $camionRepository->findAll();
        
        // Simulons des coordonnées pour les camions
        $camionsJson = [];
        foreach ($camions as $camion) {
            // Génération de coordonnées aléatoires autour de Paris pour la démo
            $lat = 48.8566 + (mt_rand(-100, 100) / 1000);
            $lng = 2.3522 + (mt_rand(-100, 100) / 1000);
            
            $camionsJson[] = [
                'id' => $camion->getId(),
                'matricule' => $camion->getMatricule(),
                'modele' => $camion->getModele(),
                'coordinates' => [$lat, $lng]
            ];
        }
        
        // Log des données pour le débogage
        error_log('Nombre de camions: ' . count($camionsJson));
        
        // S'assurer que le JSON est valide
        $encodedJson = json_encode($camionsJson);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Erreur JSON: ' . json_last_error_msg());
            // Fournir un tableau vide en cas d'erreur
            $encodedJson = '[]';
        }
        
        return $this->render('front/map.html.twig', [
            'camions' => $camions,
            'camions_json' => $encodedJson
        ]);
    }
    
    #[Route('/api/camions', name: 'app_api_camions', methods: ['GET'])]
    public function apiCamions(CamionRepository $camionRepository): Response
    {
        $camions = $camionRepository->findAll();
        $data = [];
        
        foreach ($camions as $camion) {
            // Génération de coordonnées aléatoires autour de Paris pour la démo
            $lat = 48.8566 + (mt_rand(-100, 100) / 1000);
            $lng = 2.3522 + (mt_rand(-100, 100) / 1000);
            
            $data[] = [
                'id' => $camion->getId(),
                'matricule' => $camion->getMatricule(),
                'modele' => $camion->getModele(),
                'type' => $camion->getTypeCarburant(),
                'position' => [$lat, $lng]
            ];
        }
        
        return new JsonResponse($data);
    }
}