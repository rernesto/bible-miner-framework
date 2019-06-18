<?php

namespace App\Repository;

use App\Entity\BibleRawVSM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BibleRawVSM|null find($id, $lockMode = null, $lockVersion = null)
 * @method BibleRawVSM|null findOneBy(array $criteria, array $orderBy = null)
 * @method BibleRawVSM[]    findAll()
 * @method BibleRawVSM[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BibleRawVSMRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BibleRawVSM::class);
    }

    // /**
    //  * @return BibleRawVSM[] Returns an array of BibleRawVSM objects
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
    public function findOneBySomeField($value): ?BibleRawVSM
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
