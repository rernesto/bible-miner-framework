<?php


namespace App\Controller\Api\Operation;

use App\Entity\BibleVerse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VerseNumberOperationApiController
{
    protected $requestStack;

    protected $em;

    protected $parameters;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, ParameterBagInterface $parameters)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->parameters = $parameters;
    }

    /**
     * @param null $data
     * @return bool|mixed[]
     * @throws \Exception
     */
    public function __invoke($data = null)
    {
        $results = $this->em->createQueryBuilder()
            ->select('DISTINCT bvr.verse, bvr.verse AS id')
            ->from(BibleVerse::class, 'bvr')
            ->innerJoin('bvr.book', 'bvb')
            ->where($this->em->getExpressionBuilder()->eq('bvb.id', ':book_id'))
            ->andWhere($this->em->getExpressionBuilder()->eq('bvr.chapter', ':chapter'))
            ->setParameter('book_id', $this->requestStack->getCurrentRequest()->get('book_id'))
            ->setParameter('chapter', $this->requestStack->getCurrentRequest()->get('chapter'))
            ->getQuery()->getScalarResult()
        ;

        return $results;
    }
}