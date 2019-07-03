<?php


namespace App\Model;


use App\Database\DBAL\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Pagerfanta\Pagerfanta;
use PDO;

class SearchModel
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * SearchModel constructor.
     * @param ConnectionFactory $connectionFactory
     * @throws DBALException
     */
    public function __construct(ConnectionFactory $connectionFactory)
    {
        $this->connection = $connectionFactory->getConnection('dbDefault');
    }

    /**
     * @param array|null $query
     * @param null $bibleVersion
     * @return Pagerfanta
     */
    public function getSearchQueryPaginator(array $query = null, $bibleVersion = null)
    {
        $queryBuilder = $this->getStemIdfRankedQueryBuilder();

        $paginatorQueryBuilder = $this->connection->createQueryBuilder()
            ->select(
                "bv2.id AS verse_id, bv2.reference AS reference",
                "bv2.verse_text AS verse_text, bsv3.words AS words",
                "bsv3.bible_version_id AS bible_version_id,
                bsv3.bible_version_name AS bible_version_name,
                bsv3.matches AS matches, bsv3.score AS score"
            )
            ->from('bible_verse', 'bv2')
            ->innerJoin(
                'bv2', sprintf('(%s)', $queryBuilder->getSQL()), 'bsv3',
                'bsv3.verse_id = bv2.id'
            )
            ->setParameter('words', $query, Connection::PARAM_STR_ARRAY)
            ->setParameter('bibleVersion', $bibleVersion)
            ->orderBy('bsv3.matches', 'DESC')
            ->addOrderBy('bsv3.score', 'DESC');
        ;

        $paginationAdapter = new DoctrineDbalAdapter($paginatorQueryBuilder,
            function(QueryBuilder $queryBuilder) {
                return $queryBuilder->select('COUNT(*)');
            }
        );

        $paginator = new Pagerfanta($paginationAdapter);

        return $paginator;
    }

    public function getBibleBookVersesPaginator($versionId, $bookId) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('bvr.id, bvr.reference, bvr.chapter, bvr.verse, bvr.verse_text')
            ->from('bible_verse', 'bvr')
            ->innerJoin(
                'bvr', 'bible_book', 'bb',
                'bb.id = bvr.book_id'
            )
            ->innerJoin(
                'bvr', 'bible_version', 'bv',
                'bv.id = bvr.bible_version_id'
            )
            ->where($queryBuilder->expr()->eq('bb.id', ':bookId'))
            ->andWhere($queryBuilder->expr()->eq('bv.id', ':versionId'))
            ->setParameter('bookId', $bookId)
            ->setParameter('versionId', $versionId)
        ;

        $paginationAdapter = new DoctrineDbalAdapter($queryBuilder,
            function(QueryBuilder $queryBuilder) {
                return $queryBuilder->select('COUNT(*)');
            }
        );

        $paginator = new Pagerfanta($paginationAdapter);

        return $paginator;
    }

    public function findByTfIdfSearchQuery(array $query = null, $bibleVersion = null, $stem = true)
    {
        if ($stem == true) {
            $queryBuilder = $this->getStemIdfRankedQueryBuilder()
                ->setParameter('words', $query, Connection::PARAM_STR_ARRAY)
                ->setParameter('bibleVersion', $bibleVersion)
                ->having('v2.CountOf >=' . count($query))
                ->orHaving('v2.SumOf > AVG(v2.SumOf)')
            ;
            return $queryBuilder->execute()->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function getStemIdfRankedQueryBuilder()
    {
        $subQueryBuilder = $this->connection->createQueryBuilder();
        $mainQueryBuilder = $this->connection->createQueryBuilder();

        $subQueryBuilder->select(
            "bsv2.verse_id,
                 GROUP_CONCAT(DISTINCT sv.word SEPARATOR ',') AS words,
                 COUNT(*) AS CountOf, SUM(bsv2.tf_idf_value) AS SumOf"
        )
            ->from("bible_stem_vsm", 'bsv2')
            ->innerJoin(
                'bsv2', "stem_vocabulary", 'sv',
                'sv.id = bsv2.vocabulary_id'
            )
            ->where(
                $subQueryBuilder->expr()->in(
                    'sv.word', ':words'
                )
            )
            ->groupBy('bsv2.verse_id')
            ->having('COUNT(*) > 1');

        $mainQueryBuilder->select(
            "brc.id as verse_id, brc.reference AS reference",
            "brc.verse_text AS verse_text, v2.words AS words",
            "brc.bible_version_id AS bible_version_id,
            bv.name AS bible_version_name,
            v2.CountOf AS matches, v2.SumOf AS score"
        )
            ->from("bible_stem_vsm", 'bsv')
            ->innerJoin(
                'bsv', "bible_verse",
                'brc', 'brc.id = bsv.verse_id'
            )
            ->innerJoin('brc', 'bible_version', 'bv', 'bv.id = brc.bible_version_id')
            ->innerJoin(
                'bsv', sprintf('(%s)', $subQueryBuilder->getSQL()), 'v2',
                'v2.verse_id = bsv.verse_id'
            )
            ->where('brc.bible_version_id = :bibleVersion')
            ->groupBy('brc.id')
        ;

        return $mainQueryBuilder;
    }

    public function calculateVersePage($versionId, $bookId, $chapter, $verse)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('CONCAT(bvr.chapter, ":", bvr.verse)')
            ->from("bible_verse", 'bvr')
            ->where($queryBuilder->expr()->eq('bvr.book_id', $bookId))
            ->andWhere($queryBuilder->expr()->eq('bvr.bible_version_id', $versionId));
        $result = $queryBuilder->execute()->fetchAll(\PDO::FETCH_COLUMN);
        $page = (int) ceil((array_search("{$chapter}:{$verse}", $result) + 1)/10);

        return $page;
    }
}