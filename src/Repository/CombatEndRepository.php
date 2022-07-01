<?php

namespace App\Repository;

use App\Entity\CombatEnd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CombatEnd>
 *
 * @method CombatEnd|null find($id, $lockMode = null, $lockVersion = null)
 * @method CombatEnd|null findOneBy(array $criteria, array $orderBy = null)
 * @method CombatEnd[]    findAll()
 * @method CombatEnd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CombatEndRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CombatEnd::class);
    }

    public function add(CombatEnd $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CombatEnd $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function clearTable()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'TRUNCATE TABLE combat_end';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([]);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }

//    /**
//     * @return CombatEnd[] Returns an array of CombatEnd objects
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

//    public function findOneBySomeField($value): ?CombatEnd
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
