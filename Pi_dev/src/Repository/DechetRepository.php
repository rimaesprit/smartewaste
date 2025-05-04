<?php

namespace App\Repository;

use App\Entity\Dechet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dechet>
 *
 * @method Dechet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dechet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dechet[]    findAll()
 * @method Dechet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DechetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dechet::class);
    }

    /**
     * Recherche des déchets selon les critères spécifiés
     *
     * @param string|null $query Terme de recherche général
     * @param string|null $type Type de déchet spécifique
     * @param string|null $weight Poids du déchet
     * @param string|null $date Date de dépôt
     * @return Dechet[] Liste des déchets correspondant aux critères
     */
    public function searchDechets(?string $query = null, ?string $type = null, ?string $weight = null, ?string $date = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.date_depot', 'DESC');
        
        // Recherche par texte général
        if ($query) {
            $qb->andWhere('d.type_dechet LIKE :query OR CAST(d.poids AS STRING) LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtre par type de déchet
        if ($type && $type !== 'all') {
            $qb->andWhere('d.type_dechet = :type')
               ->setParameter('type', $type);
        }
        
        // Filtre par poids
        if ($weight) {
            $qb->andWhere('d.poids = :weight')
               ->setParameter('weight', (float)$weight);
        }
        
        // Filtre par date de dépôt
        if ($date) {
            $dateObj = new \DateTime($date);
            $nextDay = clone $dateObj;
            $nextDay->modify('+1 day');
            
            $qb->andWhere('d.date_depot >= :date_start AND d.date_depot < :date_end')
               ->setParameter('date_start', $dateObj->format('Y-m-d'))
               ->setParameter('date_end', $nextDay->format('Y-m-d'));
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * @return Dechet[] Returns an array of Dechet objects
     */
    public function findByFilters($filters = []): array
    {
        $qb = $this->createQueryBuilder('d');

        if (isset($filters['traite']) && $filters['traite'] !== null) {
            $qb->andWhere('d.traite = :traite')
               ->setParameter('traite', $filters['traite']);
        }

        if (isset($filters['favori']) && $filters['favori'] !== null) {
            $qb->andWhere('d.favori = :favori')
               ->setParameter('favori', $filters['favori']);
        }

        if (isset($filters['type']) && !empty($filters['type'])) {
            $qb->andWhere('d.type_dechet = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['camion']) && !empty($filters['camion'])) {
            $qb->andWhere('d.camion = :camion')
               ->setParameter('camion', $filters['camion']);
        }

        if (isset($filters['searchTerm']) && !empty($filters['searchTerm'])) {
            $qb->andWhere('d.type_dechet LIKE :searchTerm OR CAST(d.poids AS VARCHAR) LIKE :searchTerm')
               ->setParameter('searchTerm', '%' . $filters['searchTerm'] . '%');
        }

        // Définir le tri par défaut sur la date de dépôt (du plus récent au plus ancien)
        $orderBy = isset($filters['orderBy']) ? $filters['orderBy'] : 'd.date_depot';
        $order = isset($filters['order']) ? $filters['order'] : 'DESC';
        
        $qb->orderBy($orderBy, $order);

        return $qb->getQuery()->getResult();
    }

    public function findAllGroupedByType(): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.type_dechet, SUM(d.poids) as total_poids, COUNT(d.id) as count')
            ->groupBy('d.type_dechet')
            ->getQuery()
            ->getResult();
    }

    public function findStats(): array
    {
        $totalCount = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();
            
        $traiteCount = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.traite = :traite')
            ->setParameter('traite', true)
            ->getQuery()
            ->getSingleScalarResult();
            
        $nonTraiteCount = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.traite = :traite')
            ->setParameter('traite', false)
            ->getQuery()
            ->getSingleScalarResult();
            
        $favoriCount = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.favori = :favori')
            ->setParameter('favori', true)
            ->getQuery()
            ->getSingleScalarResult();
            
        return [
            'total' => $totalCount,
            'traite' => $traiteCount,
            'non_traite' => $nonTraiteCount,
            'favori' => $favoriCount
        ];
    }

    /**
     * Recherche avancée de déchets avec filtres multiples
     *
     * @param array $filters Tableau des filtres
     * @return Dechet[] Liste des déchets correspondant aux critères
     */
    public function searchWithAdvancedFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.date_depot', 'DESC');
        
        // Recherche par texte général
        if (!empty($filters['query'])) {
            $qb->andWhere('d.type_dechet LIKE :query OR CAST(d.poids AS STRING) LIKE :query')
               ->setParameter('query', '%' . $filters['query'] . '%');
        }
        
        // Filtre par type de déchet
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $qb->andWhere('d.type_dechet = :type')
               ->setParameter('type', $filters['type']);
        }
        
        // Filtre par poids minimum
        if (!empty($filters['poids_min'])) {
            $qb->andWhere('d.poids >= :poids_min')
               ->setParameter('poids_min', $filters['poids_min']);
        }
        
        // Filtre par poids maximum
        if (!empty($filters['poids_max'])) {
            $qb->andWhere('d.poids <= :poids_max')
               ->setParameter('poids_max', $filters['poids_max']);
        }
        
        // Filtre par date de début
        if (!empty($filters['date_debut'])) {
            $qb->andWhere('d.date_depot >= :date_debut')
               ->setParameter('date_debut', $filters['date_debut']);
        }
        
        // Filtre par date de fin
        if (!empty($filters['date_fin'])) {
            // Ajouter un jour à la date de fin pour inclure tout le jour
            $dateFin = clone $filters['date_fin'];
            $dateFin->modify('+1 day');
            
            $qb->andWhere('d.date_depot < :date_fin')
               ->setParameter('date_fin', $dateFin);
        }
        
        return $qb->getQuery()->getResult();
    }
} 