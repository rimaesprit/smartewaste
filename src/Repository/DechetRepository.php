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

<<<<<<< HEAD
    public function save(Dechet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Dechet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve tous les déchets triés par date de dépôt décroissante
     */
    public function findAllSortedByDate()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.date_depot', 'DESC')
=======
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
>>>>>>> master
            ->getQuery()
            ->getResult();
    }

<<<<<<< HEAD
    /**
     * Trouve les déchets par type
     */
    public function findByType(string $type)
    {
        return $this->createQueryBuilder('d')
            ->where('d.type_dechet = :type')
            ->setParameter('type', $type)
            ->orderBy('d.date_depot', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les déchets collectés par un camion spécifique
     */
    public function findByCamion(int $camionId)
    {
        return $this->createQueryBuilder('d')
            ->where('d.camion = :camionId')
            ->setParameter('camionId', $camionId)
            ->orderBy('d.date_depot', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le poids total des déchets collectés
     */
    public function calculateTotalWeight()
    {
        return $this->createQueryBuilder('d')
            ->select('SUM(d.poids) as total_weight')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule le poids des déchets traités
     */
    public function calculateProcessedWeight()
    {
        return $this->createQueryBuilder('d')
            ->select('SUM(d.poids) as total_weight')
=======
    public function findStats(): array
    {
        $totalCount = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();
            
        $traiteCount = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
>>>>>>> master
            ->where('d.traite = :traite')
            ->setParameter('traite', true)
            ->getQuery()
            ->getSingleScalarResult();
<<<<<<< HEAD
    }

    //    /**
    //     * @return Dechet[] Returns an array of Dechet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Dechet
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
=======
            
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
>>>>>>> master
