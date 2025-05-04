<?php

namespace App\Repository;

use App\Entity\Poubelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Poubelle>
 *
 * @method Poubelle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Poubelle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Poubelle[]    findAll()
 * @method Poubelle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PoubelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poubelle::class);
    }

    public function save(Poubelle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Poubelle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupérer les poubelles ordonnées par niveau de remplissage décroissant
     */
    public function findByNiveauRemplissageDesc()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.niveauRemplissage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les poubelles avec un niveau de remplissage supérieur à une valeur
     */
    public function findByNiveauRemplissageSuperieurA(float $niveau)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.niveauRemplissage >= :niveau')
            ->setParameter('niveau', $niveau)
            ->orderBy('p.niveauRemplissage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les poubelles par statut
     */
    public function findByStatut(string $statut)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Rechercher des poubelles selon différents critères
     * 
     * @param string|null $query Texte de recherche (localisation, adresse)
     * @param string|null $type Type de poubelle
     * @param string|null $statut Statut de la poubelle
     * @param float|null $niveauMin Niveau de remplissage minimum
     * @param float|null $niveauMax Niveau de remplissage maximum
     * @return Poubelle[] Liste des poubelles filtrées
     */
    public function searchPoubelles(
        ?string $query = null,
        ?string $type = null,
        ?string $statut = null,
        ?float $niveauMin = null,
        ?float $niveauMax = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');
        
        // Recherche par texte (localisation ou adresse)
        if ($query && $query !== '') {
            $qb->andWhere('p.localisation LIKE :query OR p.adresse LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par type
        if ($type && $type !== '') {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $type);
        }
        
        // Filtrer par statut
        if ($statut && $statut !== '') {
            $qb->andWhere('p.statut = :statut')
               ->setParameter('statut', $statut);
        }
        
        // Filtrer par niveau de remplissage minimum
        if ($niveauMin !== null) {
            $qb->andWhere('p.niveauRemplissage >= :niveauMin')
               ->setParameter('niveauMin', $niveauMin);
        }
        
        // Filtrer par niveau de remplissage maximum
        if ($niveauMax !== null) {
            $qb->andWhere('p.niveauRemplissage <= :niveauMax')
               ->setParameter('niveauMax', $niveauMax);
        }
        
        return $qb->getQuery()->getResult();
    }
} 