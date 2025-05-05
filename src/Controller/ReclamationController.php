<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\File;

#[Route('/citoyen/reclamation')]
class ReclamationController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        
        $reclamations = $reclamationRepository->findBy(['user' => $user], ['dateRec' => 'DESC']);
        
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    /**
     * Vérifie manuellement l'extension du fichier sans dépendre de fileinfo
     * @param File|null $file
     * @return bool
     */
    private function validateFileExtension(?File $file): bool
    {
        if (!$file) {
            return true;
        }
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Si c'est un fichier téléchargé
        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            // Utiliser le nom original du fichier pour extraire l'extension
            $originalFilename = $file->getClientOriginalName();
            $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
            return in_array($extension, $allowedExtensions);
        }
        
        // Sinon, c'est un fichier File standard
        $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions);
    }

    /**
     * Vérifie manuellement si la taille du fichier est correcte
     * @param File|null $file
     * @return bool
     */
    private function validateFileSize(?File $file): bool
    {
        if (!$file) {
            return true;
        }
        
        // 5MB en octets
        $maxSize = 5 * 1024 * 1024;
        
        // Si c'est un fichier téléchargé, on peut directement accéder à sa taille
        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            return $file->getSize() <= $maxSize;
        }
        
        // Sinon, on vérifie la taille du fichier
        return filesize($file->getPathname()) <= $maxSize;
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        
        $reclamation = new Reclamation();
        $reclamation->setUser($user);
        $reclamation->setDateRec(new \DateTime());
        $reclamation->setEtatRec('Pending');
        
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer les coordonnées géographiques
            if ($request->request->has('reclamation') && isset($request->request->all('reclamation')['latitude']) 
                && isset($request->request->all('reclamation')['longitude'])) {
                $latitude = $request->request->all('reclamation')['latitude'];
                $longitude = $request->request->all('reclamation')['longitude'];
                
                if (!empty($latitude) && !empty($longitude)) {
                    $reclamation->setLatitude((float) $latitude);
                    $reclamation->setLongitude((float) $longitude);
                }
            }
            
            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été ajoutée avec succès!');
            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front', name: 'app_front_reclamation_index', methods: ['GET'])]
    public function indexFront(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        
        // Récupérer les types de réclamations uniques pour les filtres
        $types = $reclamationRepository->createQueryBuilder('r')
            ->select('DISTINCT r.typeRec')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.typeRec', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Convertir le résultat en tableau simple
        $typeOptions = array_map(function($item) {
            return $item['typeRec'];
        }, $types);
        
        // Récupérer toutes les réclamations de l'utilisateur
        $reclamations = $reclamationRepository->findBy(['user' => $user], ['dateRec' => 'DESC']);
        
        return $this->render('front/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'typeOptions' => $typeOptions
        ]);
    }

    #[Route('/front/search', name: 'app_front_reclamation_search', methods: ['GET'])]
    public function searchFront(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        
        // Récupérer les paramètres de recherche
        $query = $request->query->get('query');
        $status = $request->query->get('status');
        $type = $request->query->get('type');
        
        // Récupérer les dates éventuelles
        $fromDate = $request->query->get('fromDate');
        $toDate = $request->query->get('toDate');
        
        // Convertir les chaînes de date en objets DateTime si nécessaire
        $fromDateObj = null;
        $toDateObj = null;
        
        if ($fromDate) {
            try {
                $fromDateObj = new \DateTime($fromDate);
            } catch (\Exception $e) {
                // Ignorer les dates invalides
            }
        }
        
        if ($toDate) {
            try {
                $toDateObj = new \DateTime($toDate);
                // Ajouter un jour pour inclure toute la journée
                $toDateObj->setTime(23, 59, 59);
            } catch (\Exception $e) {
                // Ignorer les dates invalides
            }
        }
        
        // Effectuer la recherche
        $reclamations = $reclamationRepository->searchReclamations(
            $user,
            $query,
            $status,
            $type,
            $fromDateObj,
            $toDateObj
        );
        
        // Si c'est une requête AJAX, renvoyer le fragment HTML
        if ($request->isXmlHttpRequest()) {
            return $this->render('front/reclamation/_reclamation_list.html.twig', [
                'reclamations' => $reclamations
            ]);
        }
        
        // Sinon, renvoyer la page complète
        return $this->render('front/reclamation/index.html.twig', [
            'reclamations' => $reclamations
        ]);
    }

    #[Route('/front/new', name: 'app_front_reclamation_new', methods: ['GET', 'POST'])]
    public function newFront(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();
        
        $reclamation = new Reclamation();
        $reclamation->setUser($user);
        $reclamation->setDateRec(new \DateTime());
        $reclamation->setEtatRec('Pending');
        
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer les coordonnées géographiques
            if ($request->request->has('reclamation')) {
                $formData = $request->request->all('reclamation');
                
                // Vérifier et récupérer les coordonnées géographiques
                if (isset($formData['latitude']) && isset($formData['longitude'])) {
                    $latitude = $formData['latitude'];
                    $longitude = $formData['longitude'];
                    
                    if (!empty($latitude) && !empty($longitude)) {
                        $reclamation->setLatitude((float) $latitude);
                        $reclamation->setLongitude((float) $longitude);
                        
                        // Log pour débogage
                        error_log("Enregistrement des coordonnées: lat={$latitude}, lng={$longitude}");
                    } else {
                        error_log("Coordonnées vides ou non valides");
                    }
                } else {
                    error_log("Coordonnées non trouvées dans la requête");
                }
            }
            
            // Gérer le téléchargement de fichier photo
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('reclamations_directory'),
                        $newFilename
                    );
                    $reclamation->setPhotoName($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo: ' . $e->getMessage());
                }
            }
            
            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été ajoutée avec succès!');
            return $this->redirectToRoute('app_front_reclamation_index');
        }

        return $this->render('front/reclamation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->checkReclamationOwner($reclamation);
        
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }
    #[Route('Front/{id}', name: 'app_front_reclamation_show', methods: ['GET'])]
    public function Frontshow(Reclamation $reclamation): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->checkReclamationOwner($reclamation);
        
        return $this->render('front/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->checkReclamationOwner($reclamation);
        
        // Vérifier si la réclamation est encore modifiable (status "Pending")
        if (!$this->isGranted('ROLE_ADMIN') && $reclamation->getEtatRec() !== 'Pending') {
            $this->addFlash('error', 'Cette réclamation ne peut plus être modifiée car elle est déjà en cours de traitement.');
            return $this->redirectToRoute('app_reclamation_show', ['id' => $reclamation->getId()]);
        }
        
        // Vérifier si l'utilisateur est administrateur
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        
        // Créer le formulaire avec les options appropriées
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'include_delete_photo' => $reclamation->getPhotoName() !== null,
            'is_admin' => $isAdmin,
        ]);
        
        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer les coordonnées géographiques
            if ($request->request->has('reclamation') && isset($request->request->all('reclamation')['latitude']) 
                && isset($request->request->all('reclamation')['longitude'])) {
                $latitude = $request->request->all('reclamation')['latitude'];
                $longitude = $request->request->all('reclamation')['longitude'];
                
                if (!empty($latitude) && !empty($longitude)) {
                    $reclamation->setLatitude((float) $latitude);
                    $reclamation->setLongitude((float) $longitude);
                }
            }
            
            try {
                // Récupérer le fichier téléchargé
                $photoFile = $form->get('photoFile')->getData();
                
                // Vérification manuelle de l'extension
                if ($photoFile && !$this->validateFileExtension($photoFile)) {
                    $this->addFlash('error', 'Le type de fichier n\'est pas autorisé. Formats acceptés: JPG, PNG, GIF');
                    return $this->render('reclamation/edit.html.twig', [
                        'reclamation' => $reclamation,
                        'form' => $form->createView(),
                    ]);
                }
                
                // Vérification manuelle de la taille
                if ($photoFile && !$this->validateFileSize($photoFile)) {
                    $this->addFlash('error', 'La taille du fichier dépasse la limite autorisée (5MB).');
                    return $this->render('reclamation/edit.html.twig', [
                        'reclamation' => $reclamation,
                        'form' => $form->createView(),
                    ]);
                }
                
                // Si l'option de suppression de photo est cochée
                if ($form->has('deletePhoto') && $form->get('deletePhoto')->getData() && $reclamation->getPhotoName()) {
                    // Supprimer le fichier physique
                    $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $reclamation->getPhotoName();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                    $reclamation->setPhotoName(null);
                }
                
                // Si un nouveau fichier a été téléchargé
                if ($photoFile) {
                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                    
                    // Supprimer l'ancienne photo si elle existe
                    if ($reclamation->getPhotoName()) {
                        $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $reclamation->getPhotoName();
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }
                    
                    // Enregistrer la nouvelle photo
                    try {
                        $photoFile->move(
                            $this->getParameter('uploads_directory'), 
                            $newFilename
                        );
                        $reclamation->setPhotoName($newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage());
                        return $this->render('reclamation/edit.html.twig', [
                            'reclamation' => $reclamation,
                            'form' => $form->createView(),
                        ]);
                    }
                }
                
                // Mettre à jour la date de modification
                $reclamation->setUpdatedAt(new \DateTime());
                
                $entityManager->flush();
                
                $this->addFlash('success', 'La réclamation a été mise à jour avec succès.');
                return $this->redirectToRoute('app_reclamation_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue: ' . $e->getMessage());
            }
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->checkReclamationOwner($reclamation);
        
        // Vérifier si la réclamation est encore supprimable (status "Pending")
        if (!$this->isGranted('ROLE_ADMIN') && $reclamation->getEtatRec() !== 'Pending') {
            $this->addFlash('error', 'Cette réclamation ne peut plus être supprimée car elle est déjà en cours de traitement.');
            return $this->redirectToRoute('app_reclamation_show', ['id' => $reclamation->getId()]);
        }
        
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            // Supprimer le fichier photo si existant
            if ($reclamation->getPhotoName()) {
                $photoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $reclamation->getPhotoName();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            $entityManager->remove($reclamation);
            $entityManager->flush();
            
            $this->addFlash('success', 'La réclamation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_reclamation_index');
    }
    
    /**
     * Vérifie si l'utilisateur actuel est bien le propriétaire de la réclamation
     */
    private function checkReclamationOwner(Reclamation $reclamation): void
    {
        $user = $this->security->getUser();
        
        if ($reclamation->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette réclamation.');
        }
    }

    #[Route('/front/{id}/edit', name: 'app_front_reclamation_edit', methods: ['GET', 'POST'])]
    public function editFront(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->checkReclamationOwner($reclamation);
        
        // Vérifier si la réclamation est encore modifiable (status "Pending")
        if (!$this->isGranted('ROLE_ADMIN') && $reclamation->getEtatRec() !== 'Pending') {
            $this->addFlash('error', 'Cette réclamation ne peut plus être modifiée car elle est déjà en cours de traitement.');
            return $this->redirectToRoute('app_front_reclamation_show', ['id' => $reclamation->getId()]);
        }
        
        // Créer le formulaire avec les options appropriées
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'include_delete_photo' => $reclamation->getPhotoName() !== null,
            'is_edit' => true
        ]);
        
        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer les coordonnées géographiques
            if ($request->request->has('reclamation')) {
                $formData = $request->request->all('reclamation');
                
                // Vérifier et récupérer les coordonnées géographiques
                if (isset($formData['latitude']) && isset($formData['longitude'])) {
                    $latitude = $formData['latitude'];
                    $longitude = $formData['longitude'];
                    
                    if (!empty($latitude) && !empty($longitude)) {
                        $reclamation->setLatitude((float) $latitude);
                        $reclamation->setLongitude((float) $longitude);
                        
                        // Log pour débogage
                        error_log("Mise à jour des coordonnées: lat={$latitude}, lng={$longitude}");
                    } else {
                        error_log("Coordonnées vides ou non valides lors de la mise à jour");
                    }
                } else {
                    error_log("Coordonnées non trouvées dans la requête de mise à jour");
                }
            }
            
            // Gérer le téléchargement de fichier photo
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                
                try {
                    // Supprimer l'ancienne photo si elle existe
                    if ($reclamation->getPhotoName()) {
                        $oldPhotoPath = $this->getParameter('reclamations_directory') . '/' . $reclamation->getPhotoName();
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }
                    
                    // Enregistrer la nouvelle photo
                    $photoFile->move(
                        $this->getParameter('reclamations_directory'),
                        $newFilename
                    );
                    $reclamation->setPhotoName($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo: ' . $e->getMessage());
                }
            }
            
            // Gérer la suppression de la photo si demandée
            if ($form->has('deletePhoto') && $form->get('deletePhoto')->getData()) {
                $photoPath = $this->getParameter('reclamations_directory') . '/' . $reclamation->getPhotoName();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
                $reclamation->setPhotoName(null);
            }
            
            // Mettre à jour la date de modification
            $reclamation->setUpdatedAt(new \DateTime());
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre réclamation a été mise à jour avec succès!');
            return $this->redirectToRoute('app_front_reclamation_show', ['id' => $reclamation->getId()]);
        }
        
        return $this->render('front/reclamation/edit.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/front/{id}', name: 'app_front_reclamation_delete', methods: ['POST'])]
    public function deleteFront(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->checkReclamationOwner($reclamation);
        
        if ($reclamation->getEtatRec() !== 'Pending') {
            $this->addFlash('error', 'Cette réclamation ne peut plus être supprimée car elle est déjà en cours de traitement.');
            return $this->redirectToRoute('app_front_reclamation_show', ['id' => $reclamation->getId()]);
        }
        
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            if ($reclamation->getPhotoName()) {
                $photoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $reclamation->getPhotoName();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            $entityManager->remove($reclamation);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre réclamation a été supprimée avec succès!');
        }

        return $this->redirectToRoute('app_front_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
} 