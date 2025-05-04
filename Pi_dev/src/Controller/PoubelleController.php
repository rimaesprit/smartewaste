<?php

namespace App\Controller;

use App\Entity\Poubelle;
use App\Form\PoubelleType;
use App\Repository\PoubelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/poubelle')]
class PoubelleController extends AbstractController
{
    #[Route('/', name: 'app_poubelle_index', methods: ['GET'])]
    public function index(PoubelleRepository $poubelleRepository): Response
    {
        return $this->render('poubelle/index.html.twig', [
            'poubelles' => $poubelleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_poubelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $poubelle = new Poubelle();
        $form = $this->createForm(PoubelleType::class, $poubelle);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifier si le formulaire est valide
            if ($form->isValid()) {
                try {
                    // Enregistrement de la poubelle
                    $entityManager->persist($poubelle);
                    $entityManager->flush();

                    $this->addFlash('success', 'La poubelle a été ajoutée avec succès.');
                    return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
                } catch (\Exception $e) {
                    // En cas d'erreur lors de la persistance
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage());
                }
            } else {
                // Récupération des erreurs de validation pour le message flash
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                
                // Ajout des erreurs dans un message flash
                if (!empty($errors)) {
                    $this->addFlash('error', 'Le formulaire contient des erreurs : <ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
                }
            }
        }

        return $this->render('poubelle/new.html.twig', [
            'poubelle' => $poubelle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/stats/niveaux-remplissage', name: 'app_poubelle_stats_remplissage', methods: ['GET'])]
    public function statsRemplissage(PoubelleRepository $poubelleRepository): Response
    {
        $poubelles = $poubelleRepository->findAll();
        
        // Préparer les données pour les statistiques
        $labels = [];
        $data = [];
        
        foreach ($poubelles as $poubelle) {
            $labels[] = $poubelle->getLocalisation();
            $data[] = $poubelle->getNiveauRemplissage();
        }
        
        return $this->render('poubelle/stats_remplissage.html.twig', [
            'labels' => json_encode($labels),
            'data' => json_encode($data),
        ]);
    }

    #[Route('/alert/critiques', name: 'app_poubelle_alert_critiques', methods: ['GET'])]
    public function alerteCritiques(PoubelleRepository $poubelleRepository): Response
    {
        // Poubelles avec un niveau de remplissage > 80%
        $poubellesCritiques = $poubelleRepository->findByNiveauRemplissageSuperieurA(80);
        
        return $this->render('poubelle/alert_critiques.html.twig', [
            'poubelles' => $poubellesCritiques,
        ]);
    }

    #[Route('/show/{id}', name: 'app_poubelle_show', methods: ['GET'])]
    public function show(Poubelle $poubelle): Response
    {
        return $this->render('poubelle/show.html.twig', [
            'poubelle' => $poubelle,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_poubelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Poubelle $poubelle, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PoubelleType::class, $poubelle);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $entityManager->flush();
                    $this->addFlash('success', 'La poubelle a été modifiée avec succès.');
                    return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la modification : ' . $e->getMessage());
                }
            } else {
                // Récupération des erreurs de validation
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                
                if (!empty($errors)) {
                    $this->addFlash('error', 'Le formulaire contient des erreurs : <ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
                }
            }
        }

        return $this->render('poubelle/edit.html.twig', [
            'poubelle' => $poubelle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_poubelle_delete', methods: ['POST'])]
    public function delete(Request $request, Poubelle $poubelle, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$poubelle->getId(), $request->request->get('_token'))) {
            $entityManager->remove($poubelle);
            $entityManager->flush();
            $this->addFlash('success', 'La poubelle a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/notifications', name: 'app_poubelle_notifications', methods: ['GET'])]
    public function notifications(PoubelleRepository $poubelleRepository): Response
    {
        // Récupérer les poubelles avec un niveau de remplissage élevé (plus de 75%)
        $poubellesRemplies = $poubelleRepository->findByNiveauRemplissageSuperieurA(75);
        
        // Récupérer les poubelles en maintenance ou hors service
        $poubellesEnMaintenance = $poubelleRepository->findBy(['statut' => 'En maintenance']);
        $poubellesHorsService = $poubelleRepository->findBy(['statut' => 'Hors service']);
        
        // Regrouper les poubelles par type pour l'analyse
        $poubellesParType = [];
        foreach ($poubelleRepository->findAll() as $poubelle) {
            if (!isset($poubellesParType[$poubelle->getType()])) {
                $poubellesParType[$poubelle->getType()] = [];
            }
            $poubellesParType[$poubelle->getType()][] = $poubelle;
        }
        
        return $this->render('poubelle/notifications.html.twig', [
            'poubellesRemplies' => $poubellesRemplies,
            'poubellesEnMaintenance' => $poubellesEnMaintenance,
            'poubellesHorsService' => $poubellesHorsService,
            'poubellesParType' => $poubellesParType
        ]);
    }
    
    #[Route('/count-notifications', name: 'app_poubelle_count_notifications', methods: ['GET'])]
    public function countNotifications(PoubelleRepository $poubelleRepository): Response
    {
        // Récupérer le nombre de poubelles avec un niveau de remplissage élevé (plus de 75%)
        $countRemplies = count($poubelleRepository->findByNiveauRemplissageSuperieurA(75));
        
        // Récupérer le nombre de poubelles en maintenance ou hors service
        $countEnMaintenance = count($poubelleRepository->findBy(['statut' => 'En maintenance']));
        $countHorsService = count($poubelleRepository->findBy(['statut' => 'Hors service']));
        
        // Nombre total de notifications
        $totalNotifications = $countRemplies + $countEnMaintenance + $countHorsService;
        
        // Retourner le résultat au format JSON
        return $this->json([
            'total' => $totalNotifications,
            'remplies' => $countRemplies,
            'maintenance' => $countEnMaintenance,
            'horsService' => $countHorsService
        ]);
    }
    
    #[Route('/ajax-search', name: 'app_poubelle_ajax_search', methods: ['GET'])]
    public function ajaxSearch(Request $request, PoubelleRepository $poubelleRepository): Response
    {
        // Récupérer les paramètres de recherche
        $query = $request->query->get('query');
        $type = $request->query->get('type');
        $statut = $request->query->get('statut');
        $niveauMin = $request->query->has('niveau_min') ? (float)$request->query->get('niveau_min') : null;
        $niveauMax = $request->query->has('niveau_max') ? (float)$request->query->get('niveau_max') : null;
        
        // Rechercher les poubelles
        $poubelles = $poubelleRepository->searchPoubelles(
            $query,
            $type,
            $statut,
            $niveauMin,
            $niveauMax
        );
        
        // Si c'est une requête AJAX, renvoyer uniquement le tableau
        if ($request->isXmlHttpRequest()) {
            return $this->render('poubelle/_poubelles_list.html.twig', [
                'poubelles' => $poubelles,
            ]);
        }
        
        // Sinon, renvoyer la vue complète
        return $this->render('poubelle/index.html.twig', [
            'poubelles' => $poubelles,
        ]);
    }
} 