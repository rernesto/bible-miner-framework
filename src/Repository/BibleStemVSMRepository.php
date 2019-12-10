<?php

namespace App\Repository;

use App\Entity\BibleStemVSM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BibleStemVSM|null find($id, $lockMode = null, $lockVersion = null)
 * @method BibleStemVSM|null findOneBy(array $criteria, array $orderBy = null)
 * @method BibleStemVSM[]    findAll()
 * @method BibleStemVSM[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BibleStemVSMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BibleStemVSM::class);
    }

    // /**
    //  * @return BibleStemVSM[] Returns an array of BibleStemVSM objects
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
    public function findOneBySomeField($value): ?BibleStemVSM
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
