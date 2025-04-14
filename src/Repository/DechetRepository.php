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
            ->getQuery()
            ->getResult();
    }

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
            ->where('d.traite = :traite')
            ->setParameter('traite', true)
            ->getQuery()
            ->getSingleScalarResult();
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
