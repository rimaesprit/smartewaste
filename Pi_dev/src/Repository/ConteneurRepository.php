<?php

namespace App\Repository;

use App\Entity\Conteneur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conteneur>
 */
class ConteneurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conteneur::class);
    }

    /**
     * Recherche des conteneurs selon différents critères
     * 
     * @param string|null $query Recherche sur le type de déchet ou l'emplacement
     * @param string|null $zone Zone spécifique
     * @param float|null $capaciteMin Capacité minimale
     * @param float|null $capaciteMax Capacité maximale
     * @param float|null $remplissageMin Niveau de remplissage minimum en pourcentage
     * @param float|null $remplissageMax Niveau de remplissage maximum en pourcentage
     * @param string|null $typeDechet Type de déchet spécifique
     * @return Conteneur[] Liste des conteneurs filtrés
     */
    public function searchConteneurs(
        ?string $query = null,
        ?string $zone = null,
        ?float $capaciteMin = null,
        ?float $capaciteMax = null,
        ?float $remplissageMin = null,
        ?float $remplissageMax = null,
        ?string $typeDechet = null
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.emplacement', 'ASC');
        
        // Recherche par texte (type de déchet ou emplacement)
        if ($query) {
            $qb->andWhere('c.type_dechet LIKE :query OR c.emplacement LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par zone
        if ($zone) {
            $qb->andWhere('c.zone = :zone')
               ->setParameter('zone', $zone);
        }
        
        // Filtrer par capacité minimale
        if ($capaciteMin !== null) {
            $qb->andWhere('c.capacite >= :capaciteMin')
               ->setParameter('capaciteMin', $capaciteMin);
        }
        
        // Filtrer par capacité maximale
        if ($capaciteMax !== null) {
            $qb->andWhere('c.capacite <= :capaciteMax')
               ->setParameter('capaciteMax', $capaciteMax);
        }
        
        // Filtrer par niveau de remplissage minimum (en pourcentage)
        if ($remplissageMin !== null) {
            $qb->andWhere('(c.poids_actuel / c.capacite) * 100 >= :remplissageMin')
               ->setParameter('remplissageMin', $remplissageMin);
        }
        
        // Filtrer par niveau de remplissage maximum (en pourcentage)
        if ($remplissageMax !== null) {
            $qb->andWhere('(c.poids_actuel / c.capacite) * 100 <= :remplissageMax')
               ->setParameter('remplissageMax', $remplissageMax);
        }
        
        // Filtrer par type de déchet
        if ($typeDechet) {
            $qb->andWhere('c.type_dechet = :typeDechet')
               ->setParameter('typeDechet', $typeDechet);
        }
        
        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Conteneur[] Returns an array of Conteneur objects
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

    //    public function findOneBySomeField($value): ?Conteneur
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
