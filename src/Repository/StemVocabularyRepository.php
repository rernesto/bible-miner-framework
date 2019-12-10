<?php

namespace App\Repository;

use App\Entity\StemVocabulary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method StemVocabulary|null find($id, $lockMode = null, $lockVersion = null)
 * @method StemVocabulary|null findOneBy(array $criteria, array $orderBy = null)
 * @method StemVocabulary[]    findAll()
 * @method StemVocabulary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StemVocabularyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StemVocabulary::class);
    }

    // /**
    //  * @return StemVocabulary[] Returns an array of StemVocabulary objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StemVocabulary
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
