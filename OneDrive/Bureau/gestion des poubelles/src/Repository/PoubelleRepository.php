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
    public function findByStatus(string $status)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }
} 