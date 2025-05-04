<?php

namespace App\Repository;

use App\Entity\Reclamation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 *
 * @method Reclamation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reclamation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reclamation[]    findAll()
 * @method Reclamation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function save(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve les réclamations par type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.typeRec = :val')
            ->setParameter('val', $type)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réclamations par utilisateur
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des réclamations en fonction de différents critères
     * 
     * @param User $user Utilisateur actuel
     * @param string|null $query Texte de recherche
     * @param string|null $status Statut de la réclamation
     * @param string|null $type Type de réclamation
     * @param \DateTime|null $fromDate Date de début
     * @param \DateTime|null $toDate Date de fin
     * @return Reclamation[] Liste des réclamations filtrées
     */
    public function searchReclamations(
        User $user, 
        ?string $query = null, 
        ?string $status = null, 
        ?string $type = null,
        ?\DateTime $fromDate = null,
        ?\DateTime $toDate = null
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.dateRec', 'DESC');
        
        // Recherche par texte (description ou adresse)
        if ($query) {
            $qb->andWhere('r.reclamation LIKE :query OR r.address LIKE :query OR r.typeRec LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par statut
        if ($status) {
            $qb->andWhere('r.etatRec = :status')
               ->setParameter('status', $status);
        }
        
        // Filtrer par type
        if ($type) {
            $qb->andWhere('r.typeRec = :type')
               ->setParameter('type', $type);
        }
        
        // Filtrer par date (de)
        if ($fromDate) {
            $qb->andWhere('r.dateRec >= :fromDate')
               ->setParameter('fromDate', $fromDate);
        }
        
        // Filtrer par date (à)
        if ($toDate) {
            $qb->andWhere('r.dateRec <= :toDate')
               ->setParameter('toDate', $toDate);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche des réclamations pour l'administration
     * 
     * @param string|null $query Texte de recherche (description, adresse, etc.)
     * @param string|null $status Statut de la réclamation (Pending, In Progress, Resolved, Rejected)
     * @param string|null $type Type de réclamation
     * @param ?\DateTime $fromDate Date de début
     * @param ?\DateTime $toDate Date de fin
     * @return Reclamation[] Liste des réclamations filtrées
     */
    public function searchReclamationsAdmin(
        ?string $query = null, 
        ?string $status = null, 
        ?string $type = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->orderBy('r.dateRec', 'DESC');
        
        // Recherche par texte (description, adresse, email utilisateur)
        if ($query && $query !== '') {
            $qb->andWhere('r.reclamation LIKE :query OR r.address LIKE :query OR r.typeRec LIKE :query OR u.email LIKE :query OR u.firstName LIKE :query OR u.lastName LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par statut
        if ($status && $status !== '') {
            $qb->andWhere('r.etatRec = :status')
               ->setParameter('status', $status);
        }
        
        // Filtrer par type
        if ($type && $type !== '') {
            $qb->andWhere('r.typeRec = :type')
               ->setParameter('type', $type);
        }
        
        // Filtrer par date (de)
        if ($fromDate && $fromDate !== '') {
            $fromDateObj = new \DateTime($fromDate);
            $qb->andWhere('r.dateRec >= :fromDate')
               ->setParameter('fromDate', $fromDateObj);
        }
        
        // Filtrer par date (à)
        if ($toDate && $toDate !== '') {
            $toDateObj = new \DateTime($toDate);
            $toDateObj->setTime(23, 59, 59); // Fin de journée
            $qb->andWhere('r.dateRec <= :toDate')
               ->setParameter('toDate', $toDateObj);
        }
        
        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Reclamation[] Returns an array of Reclamation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reclamation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
