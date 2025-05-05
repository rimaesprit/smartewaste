<?php

namespace App\Controller;

use App\Entity\SignAbstract;
use App\Form\SignalementType;
use App\Repository\SignAbstractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/citoyen/signalement')]
class SignalementController extends AbstractController
{
    #[Route('/', name: 'app_signalement_index', methods: ['GET'])]
    public function index(SignAbstractRepository $signAbstractRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Admin peut voir tous les signalements, utilisateur normal voit seulement les siens
        if ($this->isGranted('ROLE_ADMIN')) {
            $signalements = $signAbstractRepository->findAll();
        } else {
            $signalements = $signAbstractRepository->findByUser($this->getUser());
        }
        
        return $this->render('signalement/index.html.twig', [
            'signalements' => $signalements,
        ]);
    }

    #[Route('/new', name: 'app_signalement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $signalement = new SignAbstract();
        $signalement->setUser($this->getUser());
        // Initialiser la date à maintenant
        $signalement->setTempsSign(new \DateTime());
        
        $form = $this->createForm(SignalementType::class, $signalement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($signalement);
            $entityManager->flush();

            $this->addFlash('success', 'Votre signalement a été enregistré avec succès.');
            return $this->redirectToRoute('app_signalement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signalement/new.html.twig', [
            'signalement' => $signalement,
            'form' => $form,
        ]);
    }
    #[Route('/search', name: 'app_signalement_search', methods: ['GET'])]
    public function search(Request $request, SignAbstractRepository $signalementRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Get and sanitize parameters
        $query = $request->query->get('query', '');
        $type = $request->query->get('type', '');
        $zone = $request->query->get('zone', '');
        $etat = $request->query->get('etat', '');
        $fromDate = $request->query->get('fromDate', '');
        $toDate = $request->query->get('toDate', '');
        
        // Determine user filter
        $user = $this->isGranted('ROLE_ADMIN') ? null : $this->getUser();
        
        // Execute search
        $signalements = $signalementRepository->searchSignalements(
            trim($query),
            trim($type),
            trim($zone),
            trim($etat),
            trim($fromDate),
            trim($toDate),
            $user
        );
    
        // AJAX response
        if ($request->isXmlHttpRequest()) {
            return $this->render('signalement/_signalements_list.html.twig', [
                'signalements' => $signalements,
            ]);
        }
    
        // Full page response
        return $this->render('signalement/index.html.twig', [
            'signalements' => $signalements,
            'search_params' => [
                'query' => $query,
                'type' => $type,
                'zone' => $zone,
                'etat' => $etat,
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_signalement_show', methods: ['GET'])]
    public function show(
        int $id, 
        SignAbstractRepository $repository
    ): Response {
        $signalement = $repository->find($id);
        
        if (!$signalement) {
            throw $this->createNotFoundException('Signalement non trouvé');
        }
    
        // Add security check if needed
        if (!$this->isGranted('ROLE_ADMIN') && $signalement->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
    
        return $this->render('signalement/show.html.twig', [
            'signalement' => $signalement,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_signalement_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        SignAbstract $signalement,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Verify ownership or admin role
        if (!$this->isGranted('ROLE_ADMIN') && $signalement->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce signalement.');
        }

        $form = $this->createForm(SignalementType::class, $signalement, [
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Signalement mis à jour avec succès.');
            return $this->redirectToRoute('app_signalement_index');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        return $this->render('signalement/edit.html.twig', [
            'signalement' => $signalement,
            'form' => $form->createView(),
        ]);
    }
    
    
    #[Route('/{id}', name: 'app_signalement_delete', methods: ['POST'])]
    public function delete(Request $request, SignAbstract $signalement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Vérification que l'utilisateur est admin ou le propriétaire du signalement
        if (!$this->isGranted('ROLE_ADMIN') && $signalement->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce signalement.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$signalement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($signalement);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le signalement a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_signalement_index', [], Response::HTTP_SEE_OTHER);
    }
    
  
} 