<?php


namespace App\Controller\Api\Operation;

use App\Entity\BibleBook;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class BookLocalizedOperationApiController
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
        $bibleBooks = $this->em->createQueryBuilder()
            ->select('bb')
            ->from(BibleBook::class, 'bb')
            ->innerJoin('bb.language', 'l')
            ->where($this->em->getExpressionBuilder()->eq('l.shortName', ':shortName'))
            ->setParameter('shortName', $this->requestStack->getCurrentRequest()->get('language'))
            ->getQuery()->getArrayResult()
        ;


        return $bibleBooks;
    }
}