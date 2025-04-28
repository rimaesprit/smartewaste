<?php

namespace App\Controller;

use App\Entity\Dechet;
use App\Form\DechetType;
use App\Repository\DechetRepository;
use App\Repository\CamionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/dechet')]
class DechetController extends AbstractController
{
    #[Route('/', name: 'app_dechet_index', methods: ['GET'])]
    public function index(DechetRepository $dechetRepository): Response
    {
        return $this->render('dechet/index.html.twig', [
            'dechets' => $dechetRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_dechet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dechet = new Dechet();
        $form = $this->createForm(DechetType::class, $dechet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dechet);
            $entityManager->flush();

            // Ajouter une notification pour le camion concerné
            $camion = $dechet->getCamion();
            $this->addFlash(
                'notification', 
                [
                    'title' => 'Nouveau déchet ajouté',
                    'message' => sprintf(
                        'Un déchet de type "%s" de %s kg a été ajouté au camion %s', 
                        $dechet->getTypeDechet(), 
                        $dechet->getPoids(), 
                        $camion->getMatricule()
                    ),
                    'icon' => 'info',
                    'camion_id' => $camion->getId(),
                    'dechet_id' => $dechet->getId(),
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ]
            );

            // Rediriger vers la page des notifications du camion
            return $this->redirectToRoute('app_dechet_notification', ['id' => $camion->getId()]);
        }

        return $this->render('dechet/new.html.twig', [
            'dechet' => $dechet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dechet_show', methods: ['GET'])]
    public function show(Dechet $dechet): Response
    {
        return $this->render('dechet/show.html.twig', [
            'dechet' => $dechet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_dechet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dechet $dechet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DechetType::class, $dechet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_dechet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dechet/edit.html.twig', [
            'dechet' => $dechet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_dechet_delete', methods: ['POST'])]
    public function delete(Request $request, Dechet $dechet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dechet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dechet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dechet_index', [], Response::HTTP_SEE_OTHER);
    }
    
    // Nouvelle méthode pour afficher la notification concernant un camion
    #[Route('/notification/{id}', name: 'app_dechet_notification', methods: ['GET'])]
    public function notification(int $id, DechetRepository $dechetRepository): Response
    {
        // Récupérer les derniers déchets ajoutés à ce camion (limités à 10)
        $dechets = $dechetRepository->findBy(['camion' => $id], ['date_depot' => 'DESC'], 10);
        
        return $this->render('dechet/notification.html.twig', [
            'dechets' => $dechets,
            'camion_id' => $id
        ]);
    }
    
    // MÉTIER 1: API Calendar - Liste des dépôts par date
    #[Route('/calendar/{id}', name: 'app_dechet_calendar', methods: ['GET'])]
    public function calendarData(string $id, DechetRepository $dechetRepository): Response
    {
        // Si l'ID est 'data', renvoyer les données JSON pour le calendrier
        if ($id === 'data') {
            $dechets = $dechetRepository->findAll();
            $events = [];
            
            foreach ($dechets as $dechet) {
                // Déterminer la couleur selon le type de déchet
                $color = $this->getColorForType($dechet->getTypeDechet());
                
                // Créer un événement pour chaque dépôt
                $events[] = [
                    'id' => $dechet->getId(),
                    'title' => sprintf('%s - %s kg', ucfirst($dechet->getTypeDechet()), $dechet->getPoids()),
                    'start' => $dechet->getDateDepot()->format('Y-m-d'),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'description' => $dechet->getCamion() ? 'Camion: ' . $dechet->getCamion()->getMatricule() : 'Pas de camion assigné',
                    'url' => $this->generateUrl('app_dechet_show', ['id' => $dechet->getId()])
                ];
            }
            
            return new JsonResponse($events);
        }
        
        // Sinon, afficher la vue du calendrier
        return $this->render('dechet/calendar.html.twig');
    }
    
    // Helper pour obtenir une couleur par type de déchet
    private function getColorForType(string $type): string
    {
        $colors = [
            'plastique' => '#3498db',
            'papier' => '#f1c40f',
            'verre' => '#2ecc71',
            'metal' => '#95a5a6',
            'organique' => '#27ae60',
            'electronique' => '#e74c3c',
            'dangereux' => '#c0392b'
        ];
        
        return $colors[$type] ?? '#34495e';
    }
    
    // MÉTIER 2: Algorithme d'IA pour la prédiction de collecte
    #[Route('/prediction', name: 'app_dechet_prediction', methods: ['GET'])]
    public function prediction(DechetRepository $dechetRepository): Response
    {
        // 1. Récupérer les données historiques
        $dechets = $dechetRepository->findAll();
        
        // 2. Préparer les données pour l'analyse
        $dataByType = [];
        $dataByDay = [];
        $totalWeight = 0;
        
        foreach ($dechets as $dechet) {
            $type = $dechet->getTypeDechet();
            $date = $dechet->getDateDepot()->format('Y-m-d');
            $dayOfWeek = $dechet->getDateDepot()->format('N'); // 1 (lundi) à 7 (dimanche)
            $weight = $dechet->getPoids();
            
            // Accumulation par type de déchet
            if (!isset($dataByType[$type])) {
                $dataByType[$type] = 0;
            }
            $dataByType[$type] += $weight;
            
            // Accumulation par jour de la semaine
            if (!isset($dataByDay[$dayOfWeek])) {
                $dataByDay[$dayOfWeek] = 0;
            }
            $dataByDay[$dayOfWeek] += $weight;
            
            $totalWeight += $weight;
        }
        
        // 3. Calculer les moyennes et prédictions
        $predictions = [];
        $now = new \DateTime();
        
        // Prédiction pour les 7 prochains jours
        for ($i = 1; $i <= 7; $i++) {
            $futureDate = clone $now;
            $futureDate->modify("+$i days");
            $futureDayOfWeek = $futureDate->format('N');
            
            // Facteur de prédiction basé sur les tendances historiques
            $dayFactor = isset($dataByDay[$futureDayOfWeek]) ? $dataByDay[$futureDayOfWeek] / $totalWeight : 0.1;
            
            // Prédictions par type de déchet pour ce jour
            $dayPredictions = [];
            foreach ($dataByType as $type => $totalTypeWeight) {
                $typeFactor = $totalTypeWeight / $totalWeight;
                
                // Algorithme simple: moyenne pondérée basée sur les tendances historiques
                $predictedWeight = round($dayFactor * $typeFactor * $totalWeight * 0.1, 2); // 0.1 est un facteur d'échelle
                
                $dayPredictions[$type] = $predictedWeight;
            }
            
            $predictions[$futureDate->format('Y-m-d')] = [
                'day_name' => $futureDate->format('l'),
                'total' => array_sum($dayPredictions),
                'by_type' => $dayPredictions
            ];
        }
        
        return $this->render('dechet/prediction.html.twig', [
            'history' => [
                'total_weight' => $totalWeight,
                'by_type' => $dataByType,
                'by_day' => $dataByDay
            ],
            'predictions' => $predictions
        ]);
    }
    
    // MÉTIER 3: Système de favoris
    #[Route('/{id}/toggle-favorite', name: 'app_dechet_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(Request $request, Dechet $dechet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('favorite'.$dechet->getId(), $request->request->get('_token'))) {
            // Dans un cas réel, on associerait le favori à l'utilisateur connecté
            // Ici, nous simulons simplement en stockant un attribut sur l'entité
            $dechet->setFavori(!$dechet->isFavori());
            $entityManager->flush();
            
            $status = $dechet->isFavori() ? 'ajouté aux' : 'retiré des';
            $this->addFlash('success', "Déchet $status favoris");
        }
        
        return $this->redirectToRoute('app_dechet_show', ['id' => $dechet->getId()], Response::HTTP_SEE_OTHER);
    }
    
    // MÉTIER 4: Filtrage de recherche multicritères
    #[Route('/search', name: 'app_dechet_search', methods: ['GET'])]
    public function search(Request $request, DechetRepository $dechetRepository): Response
    {
        // Récupération des filtres
        $type = $request->query->get('type');
        $poidsMin = $request->query->get('poids_min');
        $poidsMax = $request->query->get('poids_max');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        $camionId = $request->query->get('camion');
        $favori = $request->query->get('favori');
        
        // Préparation de la requête
        $queryBuilder = $dechetRepository->createQueryBuilder('d');
        
        // Filtre par type
        if ($type && $type !== 'all') {
            $queryBuilder->andWhere('d.type_dechet = :type')
                         ->setParameter('type', $type);
        }
        
        // Filtres par poids
        if ($poidsMin !== null && $poidsMin !== '') {
            $queryBuilder->andWhere('d.poids >= :poidsMin')
                         ->setParameter('poidsMin', $poidsMin);
        }
        
        if ($poidsMax !== null && $poidsMax !== '') {
            $queryBuilder->andWhere('d.poids <= :poidsMax')
                         ->setParameter('poidsMax', $poidsMax);
        }
        
        // Filtres par date
        if ($dateDebut !== null && $dateDebut !== '') {
            $queryBuilder->andWhere('d.date_depot >= :dateDebut')
                         ->setParameter('dateDebut', new \DateTime($dateDebut));
        }
        
        if ($dateFin !== null && $dateFin !== '') {
            $queryBuilder->andWhere('d.date_depot <= :dateFin')
                         ->setParameter('dateFin', new \DateTime($dateFin));
        }
        
        // Filtre par camion
        if ($camionId !== null && $camionId !== '') {
            $queryBuilder->andWhere('d.camion = :camionId')
                         ->setParameter('camionId', $camionId);
        }
        
        // Filtre par favoris
        if ($favori !== null && $favori !== '') {
            $queryBuilder->andWhere('d.favori = :favori')
                         ->setParameter('favori', $favori === '1');
        }
        
        // Exécution de la requête
        $dechets = $queryBuilder->getQuery()->getResult();
        
        return $this->render('dechet/search.html.twig', [
            'dechets' => $dechets,
            'criteria' => [
                'type' => $type,
                'poids_min' => $poidsMin,
                'poids_max' => $poidsMax,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'camion' => $camionId,
                'favori' => $favori
            ]
        ]);
    }
    
    // MÉTIER 5: Panier de commande - Regroupement pour traitement
    #[Route('/batch/process', name: 'app_dechet_batch_process', methods: ['POST'])]
    public function batchProcess(Request $request, DechetRepository $dechetRepository, EntityManagerInterface $entityManager): Response
    {
        $selectedIds = $request->request->get('selected_dechets', []);
        $action = $request->request->get('batch_action');
        
        if (empty($selectedIds) || !$action) {
            $this->addFlash('error', 'Veuillez sélectionner des déchets et une action.');
            return $this->redirectToRoute('app_dechet_index');
        }
        
        // Récupérer les déchets sélectionnés
        $dechets = [];
        foreach ($selectedIds as $id) {
            $dechet = $dechetRepository->find($id);
            if ($dechet) {
                $dechets[] = $dechet;
            }
        }
        
        // Traiter selon l'action choisie
        switch ($action) {
            case 'recycler':
                // Simuler un traitement de recyclage
                foreach ($dechets as $dechet) {
                    // Marquer comme recyclé
                    $dechet->setTraite(true);
                    $dechet->setDateTraitement(new \DateTime());
                }
                $message = count($dechets) . ' déchet(s) marqué(s) comme recyclé(s)';
                break;
                
            case 'assigner':
                // Rediriger vers un formulaire d'assignation avec les IDs sélectionnés
                return $this->redirectToRoute('app_dechet_batch_assign', [
                    'ids' => implode(',', $selectedIds)
                ]);
                
            case 'exporter':
                // Générer un export CSV (simulation)
                $response = new Response($this->generateCsvContent($dechets));
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', 'attachment; filename="dechets_export.csv"');
                return $response;
                
            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('app_dechet_index');
        }
        
        // Sauvegarder les changements
        $entityManager->flush();
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('app_dechet_index');
    }
    
    // Helper pour générer du contenu CSV
    private function generateCsvContent(array $dechets): string
    {
        $output = fopen('php://temp', 'r+');
        
        // En-têtes CSV
        fputcsv($output, ['ID', 'Type', 'Poids (kg)', 'Date de dépôt', 'Camion', 'Statut']);
        
        // Données
        foreach ($dechets as $dechet) {
            fputcsv($output, [
                $dechet->getId(),
                $dechet->getTypeDechet(),
                $dechet->getPoids(),
                $dechet->getDateDepot()->format('Y-m-d'),
                $dechet->getCamion() ? $dechet->getCamion()->getMatricule() : 'N/A',
                $dechet->isTraite() ? 'Traité' : 'En attente'
            ]);
        }
        
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        
        return $content;
    }
    
    // Page pour assigner un lot de déchets à un camion
    #[Route('/batch/assign', name: 'app_dechet_batch_assign', methods: ['GET', 'POST'])]
    public function batchAssign(Request $request, DechetRepository $dechetRepository, EntityManagerInterface $entityManager): Response
    {
        $ids = explode(',', $request->query->get('ids', ''));
        
        if (empty($ids)) {
            $this->addFlash('error', 'Aucun déchet sélectionné.');
            return $this->redirectToRoute('app_dechet_index');
        }
        
        // Récupérer les déchets sélectionnés
        $dechets = [];
        foreach ($ids as $id) {
            $dechet = $dechetRepository->find($id);
            if ($dechet) {
                $dechets[] = $dechet;
            }
        }
        
        // Traitement du formulaire d'assignation
        if ($request->isMethod('POST')) {
            $camionId = $request->request->get('camion_id');
            
            if ($camionId) {
                foreach ($dechets as $dechet) {
                    $dechet->setCamion($entityManager->getReference('App\Entity\Camion', $camionId));
                }
                
                $entityManager->flush();
                $this->addFlash('success', count($dechets) . ' déchet(s) assigné(s) au camion #' . $camionId);
                return $this->redirectToRoute('app_dechet_index');
            }
        }
        
        // Récupérer la liste des camions pour le formulaire
        $camions = $entityManager->getRepository('App\Entity\Camion')->findAll();
        
        return $this->render('dechet/batch_assign.html.twig', [
            'dechets' => $dechets,
            'camions' => $camions
        ]);
    }
    
    // MÉTIER 6: Historique et analyse des déchets
    #[Route('/historique-analyse', name: 'app_dechet_historique', methods: ['GET'])]
    public function historiqueAnalyse(DechetRepository $dechetRepository): Response
    {
        // Récupérer tous les déchets
        $dechets = $dechetRepository->findAll();
        
        // Analyse par période (mois)
        $analyseParMois = [];
        $analyseParType = [];
        $evolutionTraitement = [];
        
        // Initialiser les types de déchets pour l'analyse
        $typesDechet = ['plastique', 'papier', 'verre', 'metal', 'organique', 'electronique', 'dangereux'];
        foreach ($typesDechet as $type) {
            $analyseParType[$type] = 0;
        }
        
        // Analyse des données
        foreach ($dechets as $dechet) {
            $mois = $dechet->getDateDepot()->format('Y-m');
            $type = $dechet->getTypeDechet();
            $poids = $dechet->getPoids();
            
            // Analyse par mois
            if (!isset($analyseParMois[$mois])) {
                $analyseParMois[$mois] = [
                    'total' => 0,
                    'traite' => 0,
                    'non_traite' => 0,
                    'types' => array_fill_keys($typesDechet, 0)
                ];
            }
            
            $analyseParMois[$mois]['total'] += $poids;
            if ($dechet->isTraite()) {
                $analyseParMois[$mois]['traite'] += $poids;
            } else {
                $analyseParMois[$mois]['non_traite'] += $poids;
            }
            
            // Analyse par type
            if (isset($analyseParMois[$mois]['types'][$type])) {
                $analyseParMois[$mois]['types'][$type] += $poids;
            }
            
            // Analyse globale par type
            if (isset($analyseParType[$type])) {
                $analyseParType[$type] += $poids;
            }
            
            // Évolution du traitement
            if ($dechet->isTraite() && $dechet->getDateTraitement()) {
                $dateTraitement = $dechet->getDateTraitement()->format('Y-m');
                if (!isset($evolutionTraitement[$dateTraitement])) {
                    $evolutionTraitement[$dateTraitement] = 0;
                }
                $evolutionTraitement[$dateTraitement] += $poids;
            }
        }
        
        // Trier les analyses par ordre chronologique
        ksort($analyseParMois);
        ksort($evolutionTraitement);
        
        // Préparer les données pour les graphiques
        $chartData = [
            'mois' => array_keys($analyseParMois),
            'poids_total' => array_map(function($item) { return $item['total']; }, $analyseParMois),
            'poids_traite' => array_map(function($item) { return $item['traite']; }, $analyseParMois),
            'poids_non_traite' => array_map(function($item) { return $item['non_traite']; }, $analyseParMois),
            'types' => array_keys($analyseParType),
            'poids_par_type' => array_values($analyseParType),
            'evolution_traitement' => [
                'dates' => array_keys($evolutionTraitement),
                'valeurs' => array_values($evolutionTraitement)
            ],
            // Données détaillées par type et par mois pour les graphiques empilés
            'details_par_type' => []
        ];
        
        // Préparer les données détaillées par type pour chaque mois
        foreach ($typesDechet as $type) {
            $chartData['details_par_type'][$type] = [];
            foreach ($analyseParMois as $mois => $data) {
                $chartData['details_par_type'][$type][] = $data['types'][$type] ?? 0;
            }
        }
        
        // Calcul des métriques globales
        $metriques = [
            'total_dechets' => count($dechets),
            'poids_total' => array_sum($chartData['poids_total']),
            'poids_traite' => array_sum($chartData['poids_traite']),
            'taux_traitement' => array_sum($chartData['poids_total']) > 0 
                ? (array_sum($chartData['poids_traite']) / array_sum($chartData['poids_total']) * 100) 
                : 0,
            'repartition_types' => $analyseParType,
            'mois_max' => array_search(max($chartData['poids_total']), $chartData['poids_total']),
            'type_max' => array_search(max($analyseParType), $analyseParType)
        ];
        
        return $this->render('dechet/historique.html.twig', [
            'metriques' => $metriques,
            'chartData' => $chartData,
            'analyseParMois' => $analyseParMois
        ]);
    }
    
    // Méthode pour afficher toutes les notifications
    #[Route('/notifications', name: 'app_dechet_notifications', methods: ['GET'])]
    public function notifications(DechetRepository $dechetRepository, CamionRepository $camionRepository): Response
    {
        // Récupérer les derniers déchets ajoutés (limités à 20)
        $dechets = $dechetRepository->findBy([], ['date_depot' => 'DESC'], 20);
        
        // Regrouper les déchets par camion
        $dechetsByCamion = [];
        foreach ($dechets as $dechet) {
            $camionId = $dechet->getCamion()->getId();
            if (!isset($dechetsByCamion[$camionId])) {
                $dechetsByCamion[$camionId] = [
                    'camion' => $dechet->getCamion(),
                    'dechets' => []
                ];
            }
            $dechetsByCamion[$camionId]['dechets'][] = $dechet;
        }
        
        return $this->render('dechet/notifications.html.twig', [
            'dechetsByCamion' => $dechetsByCamion
        ]);
    }
} 