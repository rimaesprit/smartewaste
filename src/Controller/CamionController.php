<?php

namespace App\Controller;

use App\Entity\Camion;
use App\Form\CamionType;
use App\Repository\CamionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;

#[Route('/admin/camion')]
class CamionController extends AbstractController
{
    #[Route('/', name: 'app_camion_index', methods: ['GET'])]
    public function index(CamionRepository $camionRepository): Response
    {
        return $this->render('camion/index.html.twig', [
            'camions' => $camionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_camion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CamionRepository $camionRepository): Response
    {
        $camion = new Camion();
        $form = $this->createForm(CamionType::class, $camion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si la matricule existe déjà
            $existingCamion = $camionRepository->findOneBy(['matricule' => $camion->getMatricule()]);
            
            if ($existingCamion) {
                $this->addFlash('error', 'Cette matricule existe déjà');
                return $this->renderForm('camion/new.html.twig', [
                    'camion' => $camion,
                    'form' => $form,
                ]);
            }

            try {
                $entityManager->persist($camion);
                $entityManager->flush();
                $this->addFlash('success', 'Le camion a été ajouté avec succès');
                return $this->redirectToRoute('app_camion_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'ajout du camion');
            }
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->renderForm('camion/new.html.twig', [
            'camion' => $camion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_camion_show', methods: ['GET'])]
    public function show(Camion $camion): Response
    {
        return $this->render('camion/show.html.twig', [
            'camion' => $camion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_camion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Camion $camion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CamionType::class, $camion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_camion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('camion/edit.html.twig', [
            'camion' => $camion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_camion_delete', methods: ['POST'])]
    public function delete(Request $request, Camion $camion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$camion->getId(), $request->request->get('_token'))) {
            try {
                $matricule = $camion->getMatricule();
                $entityManager->remove($camion);
                $entityManager->flush();
                $this->addFlash('success', sprintf('Le camion %s a été supprimé avec succès', $matricule));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du camion');
            }
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide');
        }

        return $this->redirectToRoute('app_camion_index', [], Response::HTTP_SEE_OTHER);
    }
    
    // MÉTIER 1: Activer/désactiver un camion (changement d'état avec conséquence)
    #[Route('/{id}/toggle-status', name: 'app_camion_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, Camion $camion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle-status'.$camion->getId(), $request->request->get('_token'))) {
            // Si le camion est en service, passer en maintenance ou hors service, sinon remettre en service
            if ($camion->getEtat() === 'en_service') {
                $camion->setEtat('en_maintenance');
                $this->addFlash('warning', 'Le camion est maintenant en maintenance.');
            } else {
                $camion->setEtat('en_service');
                $this->addFlash('success', 'Le camion est maintenant en service.');
            }
            
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_camion_show', ['id' => $camion->getId()], Response::HTTP_SEE_OTHER);
    }
    
    // MÉTIER 2: API de géolocalisation (dynamique)
    #[Route('/{id}/position', name: 'app_camion_position', methods: ['GET'])]
    public function getPosition(Camion $camion): JsonResponse
    {
        // Simulation de données de géolocalisation (dans un cas réel, serait récupéré d'un GPS)
        $position = [
            'latitude' => 48.8534 + (mt_rand(-1000, 1000) / 10000),
            'longitude' => 2.3488 + (mt_rand(-1000, 1000) / 10000),
            'vitesse' => mt_rand(0, 50),
            'direction' => mt_rand(0, 359),
            'derniere_mise_a_jour' => (new \DateTime())->format('Y-m-d H:i:s'),
            'carburant' => mt_rand(10, 100),
            'camion_id' => $camion->getId(),
            'matricule' => $camion->getMatricule(),
            'etat' => $camion->getEtat()
        ];
        
        return new JsonResponse($position);
    }
    
    // MÉTIER 3: Statistiques (analytics avancées)
    #[Route('/statistics/dashboard', name: 'app_camion_statistics', methods: ['GET'])]
    public function statistics(CamionRepository $camionRepository, EntityManagerInterface $entityManager): Response
    {
        $camions = $camionRepository->findAll();
        
        // Analyse des états des camions
        $stats = [
            'total' => count($camions),
            'en_service' => 0,
            'en_maintenance' => 0,
            'hors_service' => 0,
            'capacity_usage' => 0, // Utilisation moyenne de la capacité
            'dechets_par_camion' => [], // Nombre de déchets par camion
            'types_dechets' => [] // Répartition des types de déchets
        ];
        
        $totalCapacity = 0;
        $usedCapacity = 0;
        
        foreach ($camions as $camion) {
            // Comptage par état
            switch ($camion->getEtat()) {
                case 'en_service':
                    $stats['en_service']++;
                    break;
                case 'en_maintenance':
                    $stats['en_maintenance']++;
                    break;
                case 'hors_service':
                    $stats['hors_service']++;
                    break;
            }
            
            // Calcul d'utilisation de la capacité
            $totalCapacity += $camion->getCapacite();
            $dechets = $camion->getDechets();
            $camionPoidsTotal = 0;
            
            // Statistiques par camion
            $stats['dechets_par_camion'][$camion->getMatricule()] = count($dechets);
            
            // Analyse des déchets
            foreach ($dechets as $dechet) {
                $camionPoidsTotal += $dechet->getPoids();
                
                // Comptage par type de déchet
                $type = $dechet->getTypeDechet();
                if (!isset($stats['types_dechets'][$type])) {
                    $stats['types_dechets'][$type] = 0;
                }
                $stats['types_dechets'][$type] += $dechet->getPoids();
            }
            
            $usedCapacity += $camionPoidsTotal;
        }
        
        // Calcul du pourcentage d'utilisation de la capacité
        $stats['capacity_usage'] = $totalCapacity > 0 ? round(($usedCapacity / $totalCapacity) * 100, 2) : 0;
        
        return $this->render('camion/statistics.html.twig', [
            'stats' => $stats,
        ]);
    }
    
    // MÉTIER 4: Notification (système d'alerte)
    #[Route('/{id}/alert', name: 'app_camion_alert', methods: ['POST'])]
    public function sendAlert(Request $request, Camion $camion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('alert'.$camion->getId(), $request->request->get('_token'))) {
            $alertType = $request->request->get('alert_type');
            $message = $request->request->get('message');
            
            // Simuler l'envoi d'une alerte
            $alert = [
                'camion_id' => $camion->getId(),
                'matricule' => $camion->getMatricule(),
                'type' => $alertType,
                'message' => $message,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ];
            
            // Dans un cas réel, on enverrait l'alerte via un service dédié
            // et on enregistrerait l'alerte en base de données
            
            // Notification succès
            $this->addFlash('success', 'Alerte envoyée au conducteur du camion ' . $camion->getMatricule());
        }
        
        return $this->redirectToRoute('app_camion_show', ['id' => $camion->getId()], Response::HTTP_SEE_OTHER);
    }
    
    // MÉTIER pour initialiser des données de test
    #[Route('/init-test-data', name: 'app_camion_init_test_data', methods: ['GET'])]
    public function initTestData(EntityManagerInterface $entityManager): Response
    {
        $typeMoteurs = ['diesel', 'electrique', 'hybride', 'gaz', 'biodiesel'];
        $etats = ['en_service', 'en_maintenance', 'hors_service'];
        
        // Supprimer les anciens camions (pour éviter les doublons)
        $camions = $entityManager->getRepository(Camion::class)->findAll();
        foreach ($camions as $camion) {
            if (count($camion->getDechets()) === 0) {
                $entityManager->remove($camion);
            }
        }
        $entityManager->flush();
        
        // Créer de nouveaux camions avec des données environnementales
        for ($i = 1; $i <= 5; $i++) {
            $camion = new Camion();
            $camion->setMatricule('CAM-' . str_pad($i, 3, '0', STR_PAD_LEFT));
            $camion->setCapacite(mt_rand(1000, 10000) / 100);
            $camion->setEtat($etats[array_rand($etats)]);
            
            // Propriétés environnementales
            $camion->setTypeMoteur($typeMoteurs[array_rand($typeMoteurs)]);
            $camion->setEmissionCo2(mt_rand(50, 300));
            $camion->setConsommation(mt_rand(10, 35));
            $camion->setAnneeFabrication(mt_rand(2010, 2023));
            $camion->setKilometrage(mt_rand(10000, 200000));
            
            $entityManager->persist($camion);
        }
        
        $entityManager->flush();
        
        $this->addFlash('success', '5 camions avec données environnementales ont été créés');
        
        return $this->redirectToRoute('app_camion_index');
    }
    
    // MÉTIER 5: Optimisation des trajets
    #[Route('/optimisation', name: 'app_camion_optimisation', methods: ['GET', 'POST'])]
    public function optimisation(Request $request, CamionRepository $camionRepository, EntityManagerInterface $entityManager): Response
    {
        // Si c'est une requête POST, traiter le formulaire d'optimisation
        if ($request->isMethod('POST')) {
            $camionId = $request->request->get('camion_id');
            
            if (!$camionId) {
                $this->addFlash('warning', 'Veuillez sélectionner un camion pour l\'optimisation');
                return $this->redirectToRoute('app_camion_optimisation');
            }
            
            $camion = $camionRepository->find($camionId);
            
            if (!$camion) {
                $this->addFlash('warning', 'Le camion sélectionné n\'existe pas');
                return $this->redirectToRoute('app_camion_optimisation');
            }
            
            // Simuler un résultat d'optimisation
            // Dans un cas réel, on ferait appel à un service d'optimisation
            $dechets = $camion->getDechets()->toArray();
            
            // Données pour la vue (adaptées au template existant)
            $depot = [
                'latitude' => 48.8566, 
                'longitude' => 2.3522
            ];
            
            $pointsCollecte = [];
            foreach ($dechets as $i => $dechet) {
                $pointsCollecte[] = [
                    'latitude' => 48.8566 + (mt_rand(-500, 500) / 10000),
                    'longitude' => 2.3522 + (mt_rand(-500, 500) / 10000),
                    'type' => $dechet['typeDechet'],
                    'poids' => $dechet['poids'],
                    'date_depot' => $dechet['dateDepot']->format('d/m/Y')
                ];
            }
            
            // Génération de l'itinéraire
            $itineraire = [];
            $distance = 0;
            
            // Point de départ au dépôt
            $currentLat = $depot['latitude'];
            $currentLng = $depot['longitude'];
            
            // Visiter tous les points de collecte
            foreach ($pointsCollecte as $i => $point) {
                $dist = $this->calculerDistance($currentLat, $currentLng, $point['latitude'], $point['longitude']);
                $itineraire[] = [
                    'point' => array_merge($point, ['id' => $i]),
                    'distance' => $dist
                ];
                $distance += $dist;
                $currentLat = $point['latitude'];
                $currentLng = $point['longitude'];
            }
            
            // Retour au dépôt
            $dist = $this->calculerDistance($currentLat, $currentLng, $depot['latitude'], $depot['longitude']);
            $itineraire[] = [
                'point' => array_merge($depot, ['id' => 'depot']),
                'distance' => $dist
            ];
            $distance += $dist;
            
            return $this->render('camion/optimisation_result.html.twig', [
                'camion' => $camion,
                'depot' => $depot,
                'pointsCollecte' => $pointsCollecte,
                'itineraire' => $itineraire,
                'distance_totale' => $distance
            ]);
        }
        
        // Afficher le formulaire de sélection de camion
        return $this->render('camion/optimisation.html.twig', [
            'camions' => $camionRepository->findBy(['etat' => 'en_service'])
        ]);
    }
    
    // Méthode utilitaire pour calculer la distance entre deux points géographiques
    private function calculerDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Rayon de la terre en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
    
    // MÉTIER 6: Comparaison écologique
    #[Route('/eco-compare', name: 'app_camion_eco_compare', methods: ['GET'])]
    public function ecoCompare(CamionRepository $camionRepository): Response
    {
        $camions = $camionRepository->findAll();
        
        // Préparer les données pour la comparaison
        $camionsData = [];
        foreach ($camions as $camion) {
            $camionsData[] = [
                'id' => $camion->getId(),
                'matricule' => $camion->getMatricule(),
                'type_moteur' => $camion->getTypeMoteur(),
                'emission_co2' => $camion->getEmissionCo2(),
                'consommation' => $camion->getConsommation(),
                'annee' => $camion->getAnneeFabrication(),
                'kilometrage' => $camion->getKilometrage(),
                'score_eco' => $this->calculateEcoScore($camion)
            ];
        }
        
        return $this->render('camion/eco_compare.html.twig', [
            'camions' => $camionsData
        ]);
    }
    
    // Méthode utilitaire pour calculer un score écologique
    private function calculateEcoScore(Camion $camion): int
    {
        $score = 100;
        
        // Pénalité pour les émissions de CO2
        if ($camion->getEmissionCo2() > 200) {
            $score -= 30;
        } elseif ($camion->getEmissionCo2() > 100) {
            $score -= 15;
        }
        
        // Bonus pour les moteurs écologiques
        if ($camion->getTypeMoteur() === 'electrique') {
            $score += 30;
        } elseif ($camion->getTypeMoteur() === 'hybride' || $camion->getTypeMoteur() === 'gaz') {
            $score += 15;
        }
        
        // Pénalité pour la consommation
        if ($camion->getConsommation() > 25) {
            $score -= 20;
        } elseif ($camion->getConsommation() > 15) {
            $score -= 10;
        }
        
        // Pénalité pour l'âge du véhicule
        $age = (new \DateTime())->format('Y') - $camion->getAnneeFabrication();
        if ($age > 10) {
            $score -= 20;
        } elseif ($age > 5) {
            $score -= 10;
        }
        
        // S'assurer que le score reste entre 0 et 100
        return max(0, min(100, $score));
    }

    #[Route('/api/search', name: 'app_camion_search', methods: ['GET'])]
    public function search(Request $request, CamionRepository $camionRepository): JsonResponse
    {
        $searchTerm = $request->query->get('term', '');
        $etatFilter = $request->query->get('etat', '');
        
        $filters = ['searchTerm' => $searchTerm];
        
        if (!empty($etatFilter) && $etatFilter !== 'all') {
            $filters['etat'] = $etatFilter;
        }
        
        $camions = $camionRepository->findByFilters($filters);
        
        $result = [];
        foreach ($camions as $camion) {
            $result[] = [
                'id' => $camion->getId(),
                'matricule' => $camion->getMatricule(),
                'modele' => $camion->getModele() ?? '',
                'capacite' => $camion->getCapacite(),
                'etat' => $camion->getEtat(),
                'type_moteur' => $camion->getTypeMoteur(),
                'score_environnemental' => $camion->getScoreEnvironnemental(),
                'show_url' => $this->generateUrl('app_camion_show', ['id' => $camion->getId()]),
                'edit_url' => $this->generateUrl('app_camion_edit', ['id' => $camion->getId()]),
                'delete_url' => $this->generateUrl('app_camion_delete', ['id' => $camion->getId()]),
                'csrf_token' => $this->container->get('security.csrf.token_manager')->getToken('delete' . $camion->getId())->getValue()
            ];
        }
        
        return new JsonResponse($result);
    }

    #[Route('/recherche', name: 'app_camion_search_page', methods: ['GET'])]
    public function searchPage(Request $request, CamionRepository $camionRepository): Response
    {
        // Récupérer les critères de recherche
        $criteria = [
            'matricule' => $request->query->get('matricule', ''),
            'etat' => $request->query->get('etat', 'all'),
            'capacite_min' => $request->query->get('capacite_min', ''),
            'capacite_max' => $request->query->get('capacite_max', ''),
            'type_moteur' => $request->query->get('type_moteur', 'all'),
            'emission_min' => $request->query->get('emission_min', ''),
            'emission_max' => $request->query->get('emission_max', ''),
            'annee_min' => $request->query->get('annee_min', ''),
            'annee_max' => $request->query->get('annee_max', ''),
        ];
        
        // Construire les filtres pour la recherche
        $filters = [];
        
        if (!empty($criteria['matricule'])) {
            $filters['matricule'] = $criteria['matricule'];
        }
        
        if ($criteria['etat'] !== 'all') {
            $filters['etat'] = $criteria['etat'];
        }
        
        if (!empty($criteria['capacite_min'])) {
            $filters['capacite_min'] = (float) $criteria['capacite_min'];
        }
        
        if (!empty($criteria['capacite_max'])) {
            $filters['capacite_max'] = (float) $criteria['capacite_max'];
        }
        
        if ($criteria['type_moteur'] !== 'all') {
            $filters['type_moteur'] = $criteria['type_moteur'];
        }
        
        // Effectuer la recherche avec les filtres
        $camions = $camionRepository->findByFilters($filters);
        
        return $this->render('camion/search.html.twig', [
            'camions' => $camions,
            'criteria' => $criteria,
        ]);
    }

    #[Route('/{id}/demarrer-tournee', name: 'app_camion_demarrer_tournee', methods: ['POST'])]
    public function demarrerTournee(Request $request, Camion $camion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('demarrer-tournee'.$camion->getId(), $request->request->get('_token'))) {
            try {
                $destination = $request->request->get('destination');
                $camion->demarrerTournee($destination);
                $entityManager->flush();
                
                $this->addFlash('success', sprintf('Le camion %s est parti en tournée.', $camion->getMatricule()));
                
                // Si la requête est en AJAX, retourner une réponse JSON
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => sprintf('Le camion %s est parti en tournée.', $camion->getMatricule()),
                        'camion' => [
                            'id' => $camion->getId(),
                            'matricule' => $camion->getMatricule(),
                            'enTournee' => $camion->isEnTournee(),
                            'debutTournee' => $camion->getDebutTournee() ? $camion->getDebutTournee()->format('d/m/Y H:i') : null,
                            'destination' => $camion->getDestination()
                        ]
                    ]);
                }
            } catch (\LogicException $e) {
                $this->addFlash('error', $e->getMessage());
                
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            }
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide');
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Le token CSRF est invalide'
                ]);
            }
        }

        return $this->redirectToRoute('app_camion_show', ['id' => $camion->getId()]);
    }
    
    #[Route('/{id}/terminer-tournee', name: 'app_camion_terminer_tournee', methods: ['POST'])]
    public function terminerTournee(Request $request, Camion $camion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('terminer-tournee'.$camion->getId(), $request->request->get('_token'))) {
            try {
                $camion->terminerTournee();
                $entityManager->flush();
                
                $this->addFlash('success', sprintf('Le camion %s est revenu de tournée.', $camion->getMatricule()));
                
                // Si la requête est en AJAX, retourner une réponse JSON
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => sprintf('Le camion %s est revenu de tournée.', $camion->getMatricule()),
                        'camion' => [
                            'id' => $camion->getId(),
                            'matricule' => $camion->getMatricule(),
                            'enTournee' => $camion->isEnTournee(),
                            'finTournee' => $camion->getFinTournee() ? $camion->getFinTournee()->format('d/m/Y H:i') : null,
                            'dureeTournee' => $camion->getDureeTournee()
                        ]
                    ]);
                }
            } catch (\LogicException $e) {
                $this->addFlash('error', $e->getMessage());
                
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            }
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide');
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Le token CSRF est invalide'
                ]);
            }
        }

        return $this->redirectToRoute('app_camion_show', ['id' => $camion->getId()]);
    }
    
    #[Route('/notifications', name: 'app_camion_notifications', methods: ['GET'])]
    public function notifications(CamionRepository $camionRepository): Response
    {
        $camionsEnTournee = $camionRepository->findBy(['en_tournee' => true]);
        
        return $this->render('camion/notifications.html.twig', [
            'camions' => $camionsEnTournee
        ]);
    }
    
    #[Route('/api/notifications', name: 'app_camion_api_notifications', methods: ['GET'])]
    public function apiNotifications(CamionRepository $camionRepository): JsonResponse
    {
        $camionsEnTournee = $camionRepository->findBy(['en_tournee' => true]);
        
        $result = [];
        foreach ($camionsEnTournee as $camion) {
            $result[] = [
                'id' => $camion->getId(),
                'matricule' => $camion->getMatricule(),
                'debutTournee' => $camion->getDebutTournee() ? $camion->getDebutTournee()->format('d/m/Y H:i') : null,
                'destination' => $camion->getDestination(),
                'dureeTournee' => $camion->getDureeTournee(),
                'showUrl' => $this->generateUrl('app_camion_show', ['id' => $camion->getId()])
            ];
        }
        
        return new JsonResponse($result);
    }
}