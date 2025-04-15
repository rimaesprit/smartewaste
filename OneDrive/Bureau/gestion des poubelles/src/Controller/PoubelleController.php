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

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($poubelle);
            $entityManager->flush();

            $this->addFlash('success', 'La poubelle a été ajoutée avec succès.');
            return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez vérifier tous les champs obligatoires.');
        }

        return $this->renderForm('poubelle/new.html.twig', [
            'poubelle' => $poubelle,
            'form' => $form,
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

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La poubelle a été modifiée avec succès.');
            return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez vérifier tous les champs obligatoires.');
        }

        return $this->renderForm('poubelle/edit.html.twig', [
            'poubelle' => $poubelle,
            'form' => $form,
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
} 