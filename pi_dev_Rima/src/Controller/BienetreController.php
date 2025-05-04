<?php

namespace App\Controller;

use App\Entity\Bienetre;
use App\Form\BienetreType;
use App\Repository\BienetreRepository;
use App\Repository\PoubelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bienetre')]
class BienetreController extends AbstractController
{
    #[Route('/', name: 'app_bienetre_index', methods: ['GET'])]
    public function index(BienetreRepository $bienetreRepository): Response
    {
        return $this->render('bienetre/index.html.twig', [
            'bienetres' => $bienetreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bienetre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bienetre = new Bienetre();
        $form = $this->createForm(BienetreType::class, $bienetre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ici on pourrait analyser le texte pour déterminer le sentiment
            // Pour l'exemple, on définit simplement en fonction de la note
            if ($bienetre->getRate() >= 4) {
                $bienetre->setSentiment('Positif');
            } elseif ($bienetre->getRate() >= 2) {
                $bienetre->setSentiment('Neutre');
            } else {
                $bienetre->setSentiment('Négatif');
            }
            
            $entityManager->persist($bienetre);
            $entityManager->flush();

            $this->addFlash('success', 'L\'avis a été ajouté avec succès.');
            return $this->redirectToRoute('app_bienetre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bienetre/new.html.twig', [
            'bienetre' => $bienetre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bienetre_show', methods: ['GET'])]
    public function show(Bienetre $bienetre): Response
    {
        return $this->render('bienetre/show.html.twig', [
            'bienetre' => $bienetre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bienetre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bienetre $bienetre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BienetreType::class, $bienetre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour du sentiment en fonction de la note
            if ($bienetre->getRate() >= 4) {
                $bienetre->setSentiment('Positif');
            } elseif ($bienetre->getRate() >= 2) {
                $bienetre->setSentiment('Neutre');
            } else {
                $bienetre->setSentiment('Négatif');
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'L\'avis a été modifié avec succès.');
            return $this->redirectToRoute('app_bienetre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bienetre/edit.html.twig', [
            'bienetre' => $bienetre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bienetre_delete', methods: ['POST'])]
    public function delete(Request $request, Bienetre $bienetre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bienetre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($bienetre);
            $entityManager->flush();
            $this->addFlash('success', 'L\'avis a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_bienetre_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/stats/avis', name: 'app_bienetre_stats_avis', methods: ['GET'])]
    public function statsAvis(BienetreRepository $bienetreRepository): Response
    {
        $avis = $bienetreRepository->findAll();
        
        // Préparer les données pour les statistiques
        $sentiments = [
            'Positif' => 0,
            'Neutre' => 0,
            'Négatif' => 0,
            'Indéterminé' => 0
        ];
        
        foreach ($avis as $avisItem) {
            $sentiment = $avisItem->getSentiment();
            if (isset($sentiments[$sentiment])) {
                $sentiments[$sentiment]++;
            } else {
                $sentiments['Indéterminé']++;
            }
        }
        
        return $this->render('bienetre/stats_avis.html.twig', [
            'sentiments' => $sentiments,
            'total' => count($avis),
        ]);
    }

    #[Route('/poubelle/{id}/avis', name: 'app_bienetre_by_poubelle', methods: ['GET'])]
    public function avisParPoubelle(int $id, BienetreRepository $bienetreRepository, PoubelleRepository $poubelleRepository): Response
    {
        $poubelle = $poubelleRepository->find($id);
        
        if (!$poubelle) {
            $this->addFlash('error', 'Poubelle non trouvée.');
            return $this->redirectToRoute('app_poubelle_index');
        }
        
        $avis = $bienetreRepository->findByPoubelle($id);
        
        return $this->render('bienetre/avis_par_poubelle.html.twig', [
            'poubelle' => $poubelle,
            'avis' => $avis,
        ]);
    }
} 