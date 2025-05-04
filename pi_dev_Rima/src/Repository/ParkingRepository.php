<?php

namespace App\Repository;

use App\Entity\Parking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parking>
 */
class ParkingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parking::class);
    }

    /**
     * Recherche des parkings selon différents critères
     * 
     * @param string|null $query Recherche textuelle (matricule camion, type de déchet, etc.)
     * @param \DateTime|null $dateDebut Date de début pour filtrer les entrées
     * @param \DateTime|null $dateFin Date de fin pour filtrer les entrées
     * @param int|null $camionId ID du camion spécifique
     * @param int|null $conteneurId ID du conteneur spécifique
     * @param string|null $typeDechet Type de déchet spécifique
     * @return Parking[] Liste des parkings filtrés
     */
    public function searchParkings(
        ?string $query = null,
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFin = null,
        ?int $camionId = null,
        ?int $conteneurId = null,
        ?string $typeDechet = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.camion', 'c')
            ->leftJoin('p.conteneur', 'co')
            ->orderBy('p.date_entree', 'DESC');
        
        // Recherche textuelle
        if ($query) {
            $qb->andWhere('c.matricule LIKE :query OR co.type_dechet LIKE :query OR co.emplacement LIKE :query OR p.description LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrage par date de début
        if ($dateDebut) {
            $qb->andWhere('p.date_entree >= :dateDebut')
               ->setParameter('dateDebut', $dateDebut);
        }
        
        // Filtrage par date de fin
        if ($dateFin) {
            $qb->andWhere('p.date_entree <= :dateFin')
               ->setParameter('dateFin', $dateFin);
        }
        
        // Filtrage par camion spécifique
        if ($camionId) {
            $qb->andWhere('c.id = :camionId')
               ->setParameter('camionId', $camionId);
        }
        
        // Filtrage par conteneur spécifique
        if ($conteneurId) {
            $qb->andWhere('co.id = :conteneurId')
               ->setParameter('conteneurId', $conteneurId);
        }
        
        // Filtrage par type de déchet
        if ($typeDechet) {
            $qb->andWhere('co.type_dechet = :typeDechet')
               ->setParameter('typeDechet', $typeDechet);
        }
        
        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Parking[] Returns an array of Parking objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Parking
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
