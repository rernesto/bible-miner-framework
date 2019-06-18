<?php

namespace App\Repository;

use App\Entity\BibleVerse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BibleVerse|null find($id, $lockMode = null, $lockVersion = null)
 * @method BibleVerse|null findOneBy(array $criteria, array $orderBy = null)
 * @method BibleVerse[]    findAll()
 * @method BibleVerse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BibleVerseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BibleVerse::class);
    }

    // /**
    //  * @return BibleVerse[] Returns an array of BibleVerse objects
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
    public function findOneBySomeField($value): ?BibleVerse
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
