<?php

namespace App\Repository;

use App\Entity\Bienetre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bienetre>
 *
 * @method Bienetre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bienetre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bienetre[]    findAll()
 * @method Bienetre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BienetreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bienetre::class);
    }

    public function save(Bienetre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Bienetre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupérer les avis ordonnés par note décroissante
     */
    public function findByRateDesc()
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.rate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les avis avec une note supérieure à une valeur
     */
    public function findByRateSuperieurA(int $rate)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.rate >= :rate')
            ->setParameter('rate', $rate)
            ->orderBy('b.rate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les avis par sentiment
     */
    public function findBySentiment(string $sentiment)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.sentiment = :sentiment')
            ->setParameter('sentiment', $sentiment)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les avis liés à une poubelle spécifique
     */
    public function findByPoubelle(int $poubelleId)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.poubelle = :poubelleId')
            ->setParameter('poubelleId', $poubelleId)
            ->getQuery()
            ->getResult();
    }
} 