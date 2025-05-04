<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    /**
     * Find a user by their email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Find a user by their reset token
     */
    public function findByResetToken(string $resetToken): ?User
    {
        return $this->findOneBy(['resetToken' => $resetToken]);
    }

    /**
     * Recherche des utilisateurs par nom, prénom, email ou rôle
     */
    public function searchUsers(?string $query = null, ?string $role = null, ?bool $verified = null): array
    {
        $qb = $this->createQueryBuilder('u');
        
        // Recherche par texte (nom, prénom ou email)
        if ($query !== null && $query !== '') {
            $qb->andWhere('u.firstName LIKE :query OR u.lastName LIKE :query OR u.email LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Filtrer par rôle
        if ($role !== null && $role !== '') {
            $qb->andWhere('u.roles LIKE :role')
               ->setParameter('role', '%' . $role . '%');
        }
        
        // Filtrer par statut de vérification
        if ($verified !== null) {
            $qb->andWhere('u.isVerified = :verified')
               ->setParameter('verified', $verified);
        }
        
        $qb->orderBy('u.id', 'ASC');
        
        return $qb->getQuery()->getResult();
    }

    // Add these to your UserRepository
    public function countAdmins(): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countVerifiedUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.isVerified = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAgeDistribution(): array
    {
        $results = $this->createQueryBuilder('u')
            ->select('
            CASE
                WHEN TIMESTAMPDIFF(YEAR, u.birthDate, CURRENT_DATE()) < 18 THEN \'Under 18\'
                WHEN TIMESTAMPDIFF(YEAR, u.birthDate, CURRENT_DATE()) BETWEEN 18 AND 25 THEN \'18-25\'
                WHEN TIMESTAMPDIFF(YEAR, u.birthDate, CURRENT_DATE()) BETWEEN 26 AND 35 THEN \'26-35\'
                WHEN TIMESTAMPDIFF(YEAR, u.birthDate, CURRENT_DATE()) BETWEEN 36 AND 45 THEN \'36-45\'
                WHEN TIMESTAMPDIFF(YEAR, u.birthDate, CURRENT_DATE()) BETWEEN 46 AND 55 THEN \'46-55\'
                ELSE \'Over 55\'
            END as ageGroup,
            COUNT(u.id) as count'
            )
            ->andWhere('u.birthDate IS NOT NULL')
            ->groupBy('ageGroup')
            ->getQuery()
            ->getResult();

        $distribution = [
            'Under 18' => 0,
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            'Over 55' => 0
        ];

        foreach ($results as $result) {
            $distribution[$result['ageGroup'] = $result['count']];
        }

        return $distribution;
    }

    public function getThemePreferences(): array
    {
        $results = $this->createQueryBuilder('u')
            ->select('u.theme as theme, COUNT(u.id) as count')
            ->groupBy('u.theme')
            ->getQuery()
            ->getResult();

        $themes = [
            'light' => 0,
            'dark' => 0,
            'system' => 0
        ];

        foreach ($results as $result) {
            $theme = $result['theme'] ?? 'light';
            $themes[$theme] = $result['count'];
        }

        return $themes;
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
