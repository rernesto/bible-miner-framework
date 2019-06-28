<?php


namespace App\Controller\Api\Operation;

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

class VerseSearchOperationApiController
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
        $tokenizer = new WordTokenizer();
        $bibleVersion = $this->em->find(BibleVersion::class,
            $this->requestStack->getCurrentRequest()->get('bible_version')
        );

        $stemmer = 'Wamania\\Snowball\\' . $bibleVersion->getLanguage()->getName();
        /**
         * @var $stemmer Snowball\Stemmer
         */
        $stemmer = new $stemmer();

        $requestQuery = $this->requestStack->getCurrentRequest()->get('search_query');

        $stopWords = new StopWords(
            explode(
                "\n", file_get_contents(
                    $this->parameters->get('kernel.project_dir') . DIRECTORY_SEPARATOR .
                    'data' . DIRECTORY_SEPARATOR . 'stopwords' . DIRECTORY_SEPARATOR .
                    mb_strtolower($bibleVersion->getLanguage()->getName()) . '.txt'
                )
            )
        );

        $requestQuery = $tokenizer->tokenize($requestQuery);
        $stemmedTerms = [];
        foreach ($requestQuery as $searchTerm) {
            if(!$stopWords->isStopWord($searchTerm)) {
                $stemmedTerms[$stemmer->stem($searchTerm)] = $searchTerm;
            }
        }

        $paginator = $this->searchModel->getSearchQueryPaginator(
            array_keys($stemmedTerms), $bibleVersion->getId()
        );

        if(!is_null($page = $this->requestStack->getCurrentRequest()->get('page'))){
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