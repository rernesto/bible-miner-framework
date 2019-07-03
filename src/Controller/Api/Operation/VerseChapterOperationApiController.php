<?php


namespace App\Controller\Api\Operation;

use App\Entity\BibleVerse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VerseChapterOperationApiController
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
            ->select('DISTINCT bvr.chapter, bvr.chapter AS id')
            ->from(BibleVerse::class, 'bvr')
            ->innerJoin('bvr.book', 'bvb')
            ->where($this->em->getExpressionBuilder()->eq('bvb.id', ':id'))
            ->setParameter('id', $this->requestStack->getCurrentRequest()->get('book_id'))
            ->getQuery()->getScalarResult()
        ;

        return $results;
    }
}