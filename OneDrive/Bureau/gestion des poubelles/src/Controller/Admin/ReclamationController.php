<?php

namespace App\Controller\Admin;

use App\Entity\Reclamation;
use App\Form\ReclamationProcessType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_admin_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('admin/reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('admin/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/process', name: 'app_admin_reclamation_process', methods: ['GET', 'POST'])]
    public function process(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationProcessType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the updated date
            $reclamation->setUpdatedAt(new \DateTimeImmutable());
            
            $entityManager->flush();

            $this->addFlash('success', 'La réclamation a été traitée avec succès !');
            return $this->redirectToRoute('app_admin_reclamation_index');
        }

        return $this->render('admin/reclamation/process.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'La réclamation a été supprimée avec succès !');
        }

        return $this->redirectToRoute('app_admin_reclamation_index');
    }
} 