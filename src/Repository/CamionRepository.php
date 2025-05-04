<?php

namespace App\Repository;

use App\Entity\Camion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * @return Camion[] Returns an array of Camion objects
     */
    public function findByFilters($filters = []): array
    {
        $qb = $this->createQueryBuilder('c');

        if (isset($filters['etat']) && !empty($filters['etat'])) {
            $qb->andWhere('c.etat = :etat')
               ->setParameter('etat', $filters['etat']);
        }

        if (isset($filters['type_moteur']) && !empty($filters['type_moteur'])) {
            $qb->andWhere('c.type_moteur = :type_moteur')
               ->setParameter('type_moteur', $filters['type_moteur']);
        }

        if (isset($filters['capacite_min']) && !empty($filters['capacite_min'])) {
            $qb->andWhere('c.capacite >= :capacite_min')
               ->setParameter('capacite_min', $filters['capacite_min']);
        }

        if (isset($filters['capacite_max']) && !empty($filters['capacite_max'])) {
            $qb->andWhere('c.capacite <= :capacite_max')
               ->setParameter('capacite_max', $filters['capacite_max']);
        }

        if (isset($filters['searchTerm']) && !empty($filters['searchTerm'])) {
            $qb->andWhere('c.matricule LIKE :searchTerm OR c.modele LIKE :searchTerm OR c.type_moteur LIKE :searchTerm OR c.etat LIKE :searchTerm')
               ->setParameter('searchTerm', '%' . $filters['searchTerm'] . '%');
        }

        // Définir le tri par défaut sur la matricule
        $orderBy = isset($filters['orderBy']) ? $filters['orderBy'] : 'c.matricule';
        $order = isset($filters['order']) ? $filters['order'] : 'ASC';
        
        $qb->orderBy($orderBy, $order);

        return $qb->getQuery()->getResult();
    }

    public function findStats(): array
    {
        $totalCount = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
            
        $enServiceCount = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.etat = :etat')
            ->setParameter('etat', 'en_service')
            ->getQuery()
            ->getSingleScalarResult();
            
        $enMaintenanceCount = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.etat = :etat')
            ->setParameter('etat', 'en_maintenance')
            ->getQuery()
            ->getSingleScalarResult();
            
        $horsServiceCount = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.etat = :etat')
            ->setParameter('etat', 'hors_service')
            ->getQuery()
            ->getSingleScalarResult();
            
        return [
            'total' => $totalCount,
            'en_service' => $enServiceCount,
            'en_maintenance' => $enMaintenanceCount,
            'hors_service' => $horsServiceCount
        ];
    }

    public function findByPerformanceEnvironnementale(int $scoreMin = 0, int $scoreMax = 100): array
    {
        $camions = $this->findAll();
        
        return array_filter($camions, function(Camion $camion) use ($scoreMin, $scoreMax) {
            $score = $camion->getScoreEnvironnemental();
            return $score >= $scoreMin && $score <= $scoreMax;
        });
    }

    /**
     * Recherche des camions en fonction de différents critères
     * 
     * @param string|null $query Texte de recherche (matricule ou modèle)
     * @param string|null $etat État du camion (en_service, en_maintenance, hors_service)
     * @param string|null $typeMoteur Type de moteur (diesel, electrique, etc.)
     * @param float|null $capaciteMin Capacité minimale
     * @param float|null $capaciteMax Capacité maximale
     * @param int|null $anneeMin Année de fabrication minimale
     * @param int|null $anneeMax Année de fabrication maximale
     * @param bool|null $enTournee Filtre sur les camions en tournée
     * @param \DateTime|null $dateDebut Date de début pour filtrer
     * @param \DateTime|null $dateFin Date de fin pour filtrer
     * @return Camion[] Liste des camions filtrés
     */
    public function searchCamions(
        ?string $query = null,
        ?string $etat = null,
        ?string $typeMoteur = null,
        ?float $capaciteMin = null,
        ?float $capaciteMax = null,
        ?int $anneeMin = null,
        ?int $anneeMax = null,
        ?bool $enTournee = null,
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFin = null
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.matricule', 'ASC');
        
        // Recherche par texte (matricule ou modèle)
        if ($query) {
            $qb->andWhere('c.matricule LIKE :query OR c.modele LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par état
        if ($etat) {
            $qb->andWhere('c.etat = :etat')
               ->setParameter('etat', $etat);
        }
        
        // Filtrer par type de moteur
        if ($typeMoteur) {
            $qb->andWhere('c.type_moteur = :typeMoteur')
               ->setParameter('typeMoteur', $typeMoteur);
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
        
        // Filtrer par année de fabrication minimale
        if ($anneeMin !== null) {
            $qb->andWhere('c.annee_fabrication >= :anneeMin')
               ->setParameter('anneeMin', $anneeMin);
        }
        
        // Filtrer par année de fabrication maximale
        if ($anneeMax !== null) {
            $qb->andWhere('c.annee_fabrication <= :anneeMax')
               ->setParameter('anneeMax', $anneeMax);
        }
        
        // Filtrer par état de tournée
        if ($enTournee !== null) {
            $qb->andWhere('c.en_tournee = :enTournee')
               ->setParameter('enTournee', $enTournee);
        }
        
        // Filtrer par date de début de tournée
        if ($dateDebut) {
            $qb->andWhere('c.debut_tournee >= :dateDebut')
               ->setParameter('dateDebut', $dateDebut);
        }
        
        // Filtrer par date de fin de tournée
        if ($dateFin) {
            $qb->andWhere('c.fin_tournee <= :dateFin')
               ->setParameter('dateFin', $dateFin);
        }
        
        return $qb->getQuery()->getResult();
    }
} 