<?php

declare(strict_types=1);

namespace App\Command;

use App\Document\BibleVerse;
use App\Document\BibleVersion;
use Doctrine\DBAL\DBALException;
use Doctrine\ODM\MongoDB\MongoDBException;
use League\Csv\CannotInsertRecord;
use League\Csv\Writer;
use Phpml\Tokenization\WordTokenizer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamania\Snowball\Stemmer;

class BibleMinerTextImportCommand extends MinerCommand
{
    protected static $defaultName = 'bible-miner:text:import';

    protected function configure()
    {
        $this
            ->setDescription('Import Bible text from data source.')
            ->addArgument(
                'datasource', InputArgument::REQUIRED,
                'Data source name SpaRVG, SpaRV1865, KJ2000, etc.'
            )
            ->addOption(
                'truncate', null,InputOption::VALUE_OPTIONAL,
                'Truncate Vocabulary Collection.', true
            )

        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws DBALException
     * @throws MongoDBException
     * @throws CannotInsertRecord
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $dbalConnection = $this->dbalConnectionFactory->getConnection(
            'db' . $input->getArgument('datasource')
        );

        /**
         * @var $bibleVersionDocument BibleVersion
         */
        $bibleVersionDocument = $this->dm->getRepository(BibleVersion::class)
            ->findOneBy(['shortName' => $input->getArgument('datasource')]);

        if($input->getOption('truncate') == true) {
            $io->warning(sprintf('Deleting records for Bible Verses Table %s', 'bible_verses'));
            $documentsToErase = $this->dm->createQueryBuilder(BibleVerse::class)
                ->field('bibleVersion')
                ->references($bibleVersionDocument)
                ->getQuery()->execute();

            foreach($documentsToErase as $documentToErase) {
                $this->dm->remove($documentToErase);
            }
        }
        $this->dm->flush();

        if (isset($dbalConnection)) {
            $bibleTextQueryBuilder = $dbalConnection->createQueryBuilder();


            $bibleTextQueryBuilder->select('b.*')
                ->from('bible', 'b');

            $bibleVerses = $bibleTextQueryBuilder->execute()
                ->fetchAll(\PDO::FETCH_OBJ);

            $io->text(sprintf('Processing %d verses...', count($bibleVerses)));
            $io->progressStart((int) count($bibleVerses));

            $i = 0;
            $wordTokenizer = new WordTokenizer();

            $verseCsvWriter = Writer::createFromString('');
            $verseCsvWriter->insertOne(['references', 'verses']);

            $verseTokensCsvWriter = Writer::createFromString('');
            $verseTokensCsvWriter->insertOne(['references', 'verse_tokens']);

            $stemmedTokensCsvWriter = Writer::createFromString('');
            $stemmedTokensCsvWriter->insertOne(['references', 'stemmed_tokens']);

            foreach($bibleVerses as $bibleVerse) {
                $bibleVerseDocument = new BibleVerse();
                $bibleVerseDocument->setReference($bibleVerse->ref);
                $bibleVerseDocument->setVerseText(
                    addslashes(preg_replace('/\n/', '', $bibleVerse->verse))
                );
                $bibleVerseDocument->setVerseTokens(
                    implode(
                        " ",
                        array_map(
                            'mb_strtolower',
                            $wordTokenizer->tokenize($bibleVerseDocument->getVerseText())
                        )
                    )
                );
                $bibleVerseDocument->setBibleVersion($bibleVersionDocument);
                $this->dm->persist($bibleVerseDocument);

                $verseCsvWriter->insertOne([$bibleVerseDocument->getReference(), $bibleVerseDocument->getVerseText()]);
                $verseTokensCsvWriter->insertOne([$bibleVerseDocument->getReference(), $bibleVerseDocument->getVerseTokens()]);

                $stemmerLanguage = 'Wamania\\Snowball\\' . $bibleVersionDocument->getLanguage()->getName();

                /**
                 * @var $stemmer Stemmer
                 */
                $stemmer = new $stemmerLanguage();

                $stemmedVerse = array_map(
                        function($word) use ($stemmer, $stemmedTokensCsvWriter, $bibleVerseDocument) {
                            return $stemmer->stem($word);
                        }, explode(' ', $bibleVerseDocument->getVerseTokens())
                );

                $stemmedTokensCsvWriter->insertOne([$bibleVerseDocument->getReference(), implode(' ', $stemmedVerse)]);

                $i++;
                $io->progressAdvance();
            }
            $io->progressFinish();

            $io->text(sprintf('Commiting %d documents to collection...', $i));

            try {
                $this->dm->flush();
                file_put_contents(
                    $this->tmpPath . DIRECTORY_SEPARATOR .
                    '__' . $input->getArgument('datasource') .'.csv',
                    $verseCsvWriter->getContent()
                );
                file_put_contents(
                    $this->tmpPath . DIRECTORY_SEPARATOR .
                    '__' . $input->getArgument('datasource') .'-tokens.csv',
                    $verseTokensCsvWriter->getContent()
                );
                file_put_contents(
                    $this->tmpPath . DIRECTORY_SEPARATOR .
                    '__' . $input->getArgument('datasource') .'-stem-tokens.csv',
                    $stemmedTokensCsvWriter->getContent()
                );
                $io->success(sprintf('Commited %d documents to collection...', $i));
            } catch (MongoDBException $e) {
                $io->error($e->getMessage());
            }
        }

        $io->success('Datasource imported successfully');
    }
}
