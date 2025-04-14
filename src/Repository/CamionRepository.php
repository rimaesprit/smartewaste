<?php

namespace App\Repository;

use App\Entity\Camion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Camion>
 *
 * @method Camion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Camion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Camion[]    findAll()
 * @method Camion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CamionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Camion::class);
    }

    public function save(Camion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Camion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve tous les camions triés par état
     */
    public function findAllSortedByEtat()
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.etat', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Trouve les camions disponibles (en service)
     */
    public function findAvailable()
    {
        return $this->createQueryBuilder('c')
            ->where('c.etat = :etat')
            ->setParameter('etat', 'en_service')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les camions par type de moteur
     */
    public function findByTypeMoteur(string $typeMoteur)
    {
        return $this->createQueryBuilder('c')
            ->where('c.type_moteur = :typeMoteur')
            ->setParameter('typeMoteur', $typeMoteur)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de camions par état
     */
    public function countByEtat()
    {
        return $this->createQueryBuilder('c')
            ->select('c.etat, COUNT(c.id) as count')
            ->groupBy('c.etat')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Camion[] Returns an array of Camion objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Camion
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
