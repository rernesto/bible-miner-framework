<?php


namespace App\Controller\Api\Operation;

use App\Entity\BibleVersion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VersionLocalizedOperationApiController
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
        $result = $this->em->createQueryBuilder()
            ->select('bv.id', 'bv.name', 'l.shortName AS language')
            ->from(BibleVersion::class, 'bv')
            ->innerJoin('bv.language', 'l')
            ->getQuery()->getArrayResult()
        ;


        return $result;
    }
}