<?php

namespace App\Repository;

use App\Entity\SignAbstract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SignAbstract>
 *
 * @method SignAbstract|null find($id, $lockMode = null, $lockVersion = null)
 * @method SignAbstract|null findOneBy(array $criteria, array $orderBy = null)
 * @method SignAbstract[]    findAll()
 * @method SignAbstract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SignAbstractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SignAbstract::class);
    }

    public function save(SignAbstract $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SignAbstract $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve les signalements par type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.typeSign = :val')
            ->setParameter('val', $type)
            ->orderBy('s.tempsSign', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les signalements par zone
     */
    public function findByZone(string $zone): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.zoneSign = :zone')
            ->setParameter('zone', $zone)
            ->orderBy('s.tempsSign', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les signalements par utilisateur
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.tempsSign', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des signalements selon différents critères
     * 
     * @param string|null $query Texte de recherche (type, adresse, description)
     * @param string|null $type Type de signalement
     * @param string|null $zone Zone du signalement
     * @param string|null $etat État du signalement
     * @param \DateTime|null $fromDate Date de début
     * @param \DateTime|null $toDate Date de fin
     * @param \App\Entity\User|null $user Utilisateur concerné (optionnel)
     * @return array Liste des signalements filtrés
     */
    public function searchSignalements(
        ?string $query = null,
        ?string $type = null,
        ?string $zone = null,
        ?string $etat = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        $user = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->orderBy('s.tempsSign', 'DESC');
        
        // Filtrer par utilisateur si spécifié
        if ($user) {
            $qb->andWhere('s.user = :user')
               ->setParameter('user', $user);
        }
        
        // Recherche par texte (adresse ou description)
        if ($query && $query !== '') {
            $qb->andWhere('s.adresse LIKE :query OR s.description LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par type
        if ($type && $type !== '') {
            $qb->andWhere('s.typeSign = :type')
               ->setParameter('type', $type);
        }
        
        // Filtrer par zone
        if ($zone && $zone !== '') {
            $qb->andWhere('s.zone = :zone')
               ->setParameter('zone', $zone);
        }
        
        // Filtrer par état
        if ($etat && $etat !== '') {
            $qb->andWhere('s.etatSign = :etat')
               ->setParameter('etat', $etat);
        }
        
        // Filtrer par date de début
        if ($fromDate && $fromDate !== '') {
            $fromDateObj = new \DateTime($fromDate);
            $qb->andWhere('s.tempsSign >= :fromDate')
               ->setParameter('fromDate', $fromDateObj);
        }
        
        // Filtrer par date de fin
        if ($toDate && $toDate !== '') {
            $toDateObj = new \DateTime($toDate);
            $toDateObj->setTime(23, 59, 59); // Fin de journée
            $qb->andWhere('s.tempsSign <= :toDate')
               ->setParameter('toDate', $toDateObj);
        }
        
        return $qb->getQuery()->getResult();
    }
} 