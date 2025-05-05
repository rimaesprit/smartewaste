<?php

namespace App\Repository;

use App\Entity\SignAbstract;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

class SignAbstractRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private ?LoggerInterface $logger = null
    ) {
        parent::__construct($registry, SignAbstract::class);
    }

    public function save(SignAbstract $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) $this->flush();
    }

    public function remove(SignAbstract $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) $this->flush();
    }

    /**
     * Finds reports by type with pagination
     */
    public function findByType(string $type, int $page = 1, int $limit = 10): array
    {
        return $this->createSearchQueryBuilder()
            ->andWhere('s.typeSign = :type')
            ->setParameter('type', $type)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds reports by zone with pagination
     */
    public function findByZone(string $zone, int $page = 1, int $limit = 10): array
    {
        return $this->createSearchQueryBuilder()
            ->andWhere('s.zone = :zone')
            ->setParameter('zone', $zone)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds reports by user with pagination
     */
    public function findByUser(User $user, int $page = 1, int $limit = 10): array
    {
        return $this->createSearchQueryBuilder()
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search reports with advanced filtering
     */
    public function searchSignalements(
        ?string $query = null,
        ?string $type = null,
        ?string $zone = null,
        ?string $etat = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        ?User $user = null
    ): array {
        $qb = $this->createSearchQueryBuilder($user);
        
        $this->applyTextFilter($qb, $query);
        $this->applyFilters($qb, [
            'typeSign' => $type,
            'zone' => $zone,
            'etatSign' => $etat
        ]);
        $this->applyDateFilters($qb, $fromDate, $toDate);

        return $qb->getQuery()->getResult();
    }

    /**
     * Creates a base query builder with common settings
     */
    private function createSearchQueryBuilder(?User $user = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->orderBy('s.tempsSign', 'DESC');

        if ($user && !$user->hasRole('ROLE_ADMIN')) {
            $qb->andWhere('s.user = :user')
               ->setParameter('user', $user);
        }

        return $qb;
    }

    /**
     * Applies full-text search filters
     */
    private function applyTextFilter(QueryBuilder $qb, ?string $query): void
    {
        $cleanQuery = trim((string)$query);
        if (!empty($cleanQuery)) {
            $qb->andWhere('LOWER(s.adresse) LIKE LOWER(:query) OR 
                LOWER(s.description) LIKE LOWER(:query)')
               ->setParameter('query', '%'.$cleanQuery.'%');
        }
    }

    /**
     * Applies multiple equality filters
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        foreach ($filters as $field => $value) {
            $cleanValue = trim((string)$value);
            if (!empty($cleanValue)) {
                $qb->andWhere("s.$field = :$field")
                   ->setParameter($field, $cleanValue);
            }
        }
    }

    /**
     * Applies date range filters with validation
     */
    private function applyDateFilters(QueryBuilder $qb, ?string $fromDate, ?string $toDate): void
    {
        try {
            $this->applyStartDateFilter($qb, $fromDate);
            $this->applyEndDateFilter($qb, $toDate);
        } catch (\Exception $e) {
            $this->logger?->error('Invalid date format in search', [
                'exception' => $e,
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ]);
        }
    }

    private function applyStartDateFilter(QueryBuilder $qb, ?string $fromDate): void
    {
        $cleanDate = trim((string)$fromDate);
        if (!empty($cleanDate)) {
            $qb->andWhere('s.tempsSign >= :fromDate')
               ->setParameter('fromDate', (new \DateTime($cleanDate))->setTime(0, 0));
        }
    }

    private function applyEndDateFilter(QueryBuilder $qb, ?string $toDate): void
    {
        $cleanDate = trim((string)$toDate);
        if (!empty($cleanDate)) {
            $qb->andWhere('s.tempsSign <= :toDate')
               ->setParameter('toDate', (new \DateTime($cleanDate))->setTime(23, 59, 59));
        }
    }

    /**
     * Flushes changes to the database
     */
    private function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}