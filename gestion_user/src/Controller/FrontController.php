<?php

namespace App\Controller;

use App\Entity\Bienetre;
use App\Entity\Poubelle;
use App\Entity\Camion;
use App\Form\BienetreType;
use App\Repository\BienetreRepository;
use App\Repository\PoubelleRepository;
use App\Repository\CamionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/poubelles', name: 'app_front_poubelles', methods: ['GET'])]
    public function index(PoubelleRepository $poubelleRepository): Response
    {
        $poubelles = $poubelleRepository->findAll();
        
        // Simulons des coordonnées pour les poubelles
        // Dans un cas réel, ces coordonnées seraient stockées dans l'entité Poubelle
        $poubellesJson = [];
        foreach ($poubelles as $poubelle) {
            // Génération de coordonnées aléatoires autour de Tunis pour la démo
            $lat = 36.8065 + (mt_rand(-100, 100) / 1000);
            $lng = 10.1815 + (mt_rand(-100, 100) / 1000);
            
            $poubellesJson[] = [
                'id' => $poubelle->getId(),
                'localisation' => $poubelle->getLocalisation(),
                'niveauRemplissage' => $poubelle->getNiveauRemplissage(),
                'status' => $poubelle->getStatus(),
                'coordinates' => [$lat, $lng]
            ];
        }
        
        return $this->render('poubelle/front_map.html.twig', [
            'poubelles' => $poubelles,
            'poubelles_json' => json_encode($poubellesJson)
        ]);
    }
    
    #[Route('/bienetre/new/{poubelle_id}', name: 'app_front_bienetre_new', methods: ['GET', 'POST'])]
    public function newAvis(Request $request, EntityManagerInterface $entityManager, PoubelleRepository $poubelleRepository, ?int $poubelle_id = null): Response
    {
        $bienetre = new Bienetre();
        
        // Si un ID de poubelle est fourni, précharger cette poubelle
        if ($poubelle_id) {
            $poubelle = $poubelleRepository->find($poubelle_id);
            if ($poubelle) {
                $bienetre->setPoubelle($poubelle);
            }
        }
        
        $form = $this->createForm(BienetreType::class, $bienetre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Déterminer le sentiment en fonction de la note
            if ($bienetre->getRate() >= 4) {
                $bienetre->setSentiment('Positif');
            } elseif ($bienetre->getRate() >= 2) {
                $bienetre->setSentiment('Neutre');
            } else {
                $bienetre->setSentiment('Négatif');
            }
            
            $entityManager->persist($bienetre);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis ! Il a été enregistré avec succès.');
            
            // Rediriger vers la page d'avis de la poubelle concernée si disponible
            if ($bienetre->getPoubelle()) {
                return $this->redirectToRoute('app_front_bienetre_by_poubelle', ['id' => $bienetre->getPoubelle()->getId()]);
            }
            
            return $this->redirectToRoute('app_front_poubelles');
        }

        return $this->renderForm('bienetre/front_new.html.twig', [
            'bienetre' => $bienetre,
            'form' => $form,
        ]);
    }
    
    #[Route('/bienetre/poubelle/{id}', name: 'app_front_bienetre_by_poubelle', methods: ['GET'])]
    public function avisParPoubelle(int $id, BienetreRepository $bienetreRepository, PoubelleRepository $poubelleRepository): Response
    {
        $poubelle = $poubelleRepository->find($id);
        
        if (!$poubelle) {
            $this->addFlash('error', 'Poubelle non trouvée.');
            return $this->redirectToRoute('app_front_poubelles');
        }
        
        $avis = $bienetreRepository->findByPoubelle($id);
        
        return $this->render('bienetre/front_avis_par_poubelle.html.twig', [
            'poubelle' => $poubelle,
            'avis' => $avis,
        ]);
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
    public function map(PoubelleRepository $poubelleRepository, CamionRepository $camionRepository): Response
    {
        $poubelles = $poubelleRepository->findAll();
        $camions = $camionRepository->findAll();
        
        // Simulons des coordonnées pour les poubelles
        $poubellesJson = [];
        foreach ($poubelles as $poubelle) {
            // Génération de coordonnées aléatoires autour de Paris pour la démo
            $lat = 48.8566 + (mt_rand(-100, 100) / 1000);
            $lng = 2.3522 + (mt_rand(-100, 100) / 1000);
            
            $poubellesJson[] = [
                'id' => $poubelle->getId(),
                'localisation' => $poubelle->getLocalisation(),
                'niveauRemplissage' => $poubelle->getNiveauRemplissage(),
                'status' => $poubelle->getStatus(),
                'coordinates' => [$lat, $lng]
            ];
        }
        
        return $this->render('front/map.html.twig', [
            'poubelles' => $poubelles,
            'camions' => $camions,
            'poubelles_json' => json_encode($poubellesJson)
        ]);
    }
    
    #[Route('/poubelles-list', name: 'app_front_poubelles_list', methods: ['GET'])]
    public function poubellesList(PoubelleRepository $poubelleRepository): Response
    {
        $poubelles = $poubelleRepository->findAll();
        
        // Catégoriser les poubelles selon leur niveau de remplissage
        $poubellesCritiques = [];
        $poubellesAttention = [];
        $poubellesNormales = [];
        
        foreach ($poubelles as $poubelle) {
            $niveau = $poubelle->getNiveauRemplissage();
            
            if ($niveau >= 80) {
                $poubellesCritiques[] = $poubelle;
            } elseif ($niveau >= 50) {
                $poubellesAttention[] = $poubelle;
            } else {
                $poubellesNormales[] = $poubelle;
            }
        }
        
        return $this->render('front/poubelles_list.html.twig', [
            'poubelles' => $poubelles,
            'poubelles_critiques' => $poubellesCritiques,
            'poubelles_attention' => $poubellesAttention,
            'poubelles_normales' => $poubellesNormales,
        ]);
    }
    
    #[Route('/poubelle/{id}/avis/new', name: 'app_front_poubelle_avis_new', methods: ['GET', 'POST'])]
    public function submitAvis(Request $request, Poubelle $poubelle = null, EntityManagerInterface $entityManager): Response
    {
        $bienetre = new Bienetre();
        
        // Si une poubelle spécifique est fournie, on l'utilise
        if ($poubelle) {
            $bienetre->setPoubelle($poubelle);
        }
        
        $form = $this->createForm(BienetreType::class, $bienetre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Déterminer le sentiment en fonction de la note
            if ($bienetre->getRate() >= 4) {
                $bienetre->setSentiment('Positif');
            } elseif ($bienetre->getRate() >= 2) {
                $bienetre->setSentiment('Neutre');
            } else {
                $bienetre->setSentiment('Négatif');
            }
            
            $entityManager->persist($bienetre);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis ! Il a été enregistré avec succès.');
            
            // Rediriger vers la page d'avis de la poubelle concernée
            if ($bienetre->getPoubelle()) {
                return $this->redirectToRoute('app_front_bienetre_by_poubelle', ['id' => $bienetre->getPoubelle()->getId()]);
            }
            
            return $this->redirectToRoute('app_front_poubelles_list');
        }

        return $this->renderForm('bienetre/front_new.html.twig', [
            'bienetre' => $bienetre,
            'form' => $form,
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
                'capacite' => $camion->getCapacite(),
                'etat' => $camion->getEtat(),
                'nombreDechets' => count($camion->getDechets()),
                'lat' => $lat,
                'lng' => $lng
            ];
        }
        
        return new JsonResponse($data);
    }
}