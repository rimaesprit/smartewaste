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
} 