<?php

namespace App\Repository;

use App\Entity\BibleVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BibleVersion|null find($id, $lockMode = null, $lockVersion = null)
 * @method BibleVersion|null findOneBy(array $criteria, array $orderBy = null)
 * @method BibleVersion[]    findAll()
 * @method BibleVersion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BibleVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BibleVersion::class);
    }

    // /**
    //  * @return BibleVersion[] Returns an array of BibleVersion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BibleVersion
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
