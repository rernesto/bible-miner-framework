<?php

namespace App\Repository;

use App\Entity\RawVocabulary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RawVocabulary|null find($id, $lockMode = null, $lockVersion = null)
 * @method RawVocabulary|null findOneBy(array $criteria, array $orderBy = null)
 * @method RawVocabulary[]    findAll()
 * @method RawVocabulary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RawVocabularyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RawVocabulary::class);
    }

    // /**
    //  * @return RawVocabulary[] Returns an array of RawVocabulary objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RawVocabulary
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
