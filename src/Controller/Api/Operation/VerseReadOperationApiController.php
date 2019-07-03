<?php


namespace App\Controller\Api\Operation;

use App\Entity\BibleBook;
use App\Entity\BibleVerse;
use App\Entity\BibleVersion;
use App\Model\SearchModel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Phpml\FeatureExtraction\StopWords;
use Phpml\Tokenization\WordTokenizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Wamania\Snowball;

class VerseReadOperationApiController
{
    protected $searchModel;

    protected $requestStack;

    protected $em;

    protected $parameters;

    public function __construct(SearchModel $searchModel, RequestStack $requestStack,
                                EntityManagerInterface $em, ParameterBagInterface $parameters)
    {
        $this->searchModel = $searchModel;
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
        $bibleVersion = $this->em->find(BibleVersion::class,
            $this->requestStack->getCurrentRequest()->get('version')
        );

        $bibleBook = $this->em->find(BibleBook::class,
            $this->requestStack->getCurrentRequest()->get('book')
        );

        $paginator = $this->searchModel->getBibleBookVersesPaginator(
            $bibleVersion->getId(), $bibleBook->getId()
        );

        if(!is_null($page = $this->requestStack->getCurrentRequest()->get('page'))){
            $paginator->setCurrentPage($page);
        } else {
            $page = $this->searchModel->calculateVersePage(
                $bibleVersion->getId(), $bibleBook->getId(),
                $this->requestStack->getCurrentRequest()->get('chapter'),
                $this->requestStack->getCurrentRequest()->get('verse')
            );
            $paginator->setCurrentPage($page);
        }

        return [
            'info' => [
                'currentPage' => $paginator->getCurrentPage(),
                'maxPage' => $paginator->getNbPages(),
                'totalRecords' => $paginator->getNbResults(),
            ],
            'results' => $paginator->getCurrentPageResults(),
        ];
    }
}