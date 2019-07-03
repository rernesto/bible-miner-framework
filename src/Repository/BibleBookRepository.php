<?php

namespace App\Repository;

use App\Entity\BibleBook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BibleBook|null find($id, $lockMode = null, $lockVersion = null)
 * @method BibleBook|null findOneBy(array $criteria, array $orderBy = null)
 * @method BibleBook[]    findAll()
 * @method BibleBook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BibleBookRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BibleBook::class);
    }

    // /**
    //  * @return BibleBook[] Returns an array of BibleBook objects
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
    public function findOneBySomeField($value): ?BibleBook
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
