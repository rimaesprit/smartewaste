<?php

namespace App\Repository;

use App\Entity\Bienetre;
use App\Entity\User;
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

    /**
     * Recherche avancée des avis bien-être en fonction de différents critères
     * 
     * @param string|null $query Texte de recherche
     * @param string|null $sentiment Type de sentiment
     * @param int|null $minRate Note minimale
     * @param int|null $maxRate Note maximale
     * @param int|null $poubelleId ID d'une poubelle spécifique
     * @param \DateTime|null $fromDate Date de début
     * @param \DateTime|null $toDate Date de fin
     * @return Bienetre[] Liste des avis filtrés
     */
    public function searchBienetre(
        ?string $query = null,
        ?string $sentiment = null,
        ?int $minRate = null,
        ?int $maxRate = null,
        ?int $poubelleId = null,
        ?\DateTime $fromDate = null,
        ?\DateTime $toDate = null
    ): array {
        $qb = $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');
        
        // Recherche par texte (commentaire)
        if ($query) {
            $qb->andWhere('b.commentaire LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par sentiment
        if ($sentiment) {
            $qb->andWhere('b.sentiment = :sentiment')
               ->setParameter('sentiment', $sentiment);
        }
        
        // Filtrer par note minimale
        if ($minRate !== null) {
            $qb->andWhere('b.rate >= :minRate')
               ->setParameter('minRate', $minRate);
        }
        
        // Filtrer par note maximale
        if ($maxRate !== null) {
            $qb->andWhere('b.rate <= :maxRate')
               ->setParameter('maxRate', $maxRate);
        }
        
        // Filtrer par poubelle
        if ($poubelleId) {
            $qb->andWhere('b.poubelle = :poubelleId')
               ->setParameter('poubelleId', $poubelleId);
        }
        
        // Filtrer par date (de)
        if ($fromDate) {
            $qb->andWhere('b.createdAt >= :fromDate')
               ->setParameter('fromDate', $fromDate);
        }
        
        // Filtrer par date (à)
        if ($toDate) {
            $qb->andWhere('b.createdAt <= :toDate')
               ->setParameter('toDate', $toDate);
        }
        
        return $qb->getQuery()->getResult();
    }
} 