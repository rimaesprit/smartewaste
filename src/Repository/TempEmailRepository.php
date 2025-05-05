<?php

namespace App\Repository;

use App\Entity\TempEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TempEmail>
 *
 * @method TempEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method TempEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method TempEmail[]    findAll()
 * @method TempEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TempEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TempEmail::class);
    }

    public function findByToken(string $token): ?TempEmail
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function findByUser(int $userId): ?TempEmail
    {
        return $this->findOneBy(['user' => $userId]);
    }

    public function removeExpired(): int
    {
        $now = new \DateTimeImmutable();
        
        $qb = $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < :now')
            ->setParameter('now', $now);
            
        return $qb->getQuery()->execute();
    }
} 