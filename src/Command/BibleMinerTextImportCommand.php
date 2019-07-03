<?php

declare(strict_types=1);

namespace App\Command;

use App\Document\BibleVerse;
use App\Document\BibleVersion;
use App\Entity\BibleBook;
use Doctrine\DBAL\DBALException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    /**
     * @var DocumentManager|EntityManagerInterface
     */
    protected $manager;

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
            ->addOption(
                'orm', null, InputOption::VALUE_OPTIONAL,
                'Use mapped ORM database', true
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

        if($input->getOption('orm') == false) {
            $this->manager = $this->dm;
            /**
             * @var $bibleVersion BibleVersion
             */
            $bibleVersion = $this->manager->getRepository(BibleVersion::class)
                ->findOneBy(['shortName' => $input->getArgument('datasource')]);
        } else {
            $this->manager = $this->em;
            $bibleVersion = $this->manager->getRepository(\App\Entity\BibleVersion::class)
                ->findOneBy(['shortName' => $input->getArgument('datasource')]);
        }

        if($input->getOption('truncate') == true) {
            if($input->getOption('orm') == true){
                $toErase = $this->em->getRepository(\App\Entity\BibleVerse::class)
                    ->createQueryBuilder('bv')
                    ->innerJoin('bv.bibleVersion', 'bvbv')
                    ->where('bvbv.shortName = :shortName')
                    ->setParameter('shortName', $bibleVersion->getShortName())
                    ->getQuery()->getResult();
                $io->warning(sprintf('Deleting %d records from Bible Verses Table', count($toErase)));

            }
            else {
                $toErase = $this->dm->createQueryBuilder(BibleVerse::class)
                    ->field('bibleVersion')
                    ->references($bibleVersion)
                    ->getQuery()->execute();
                $io->warning(sprintf('Deleting %d records from Bible Verses Collection', count($toErase->toArray())));
            }
            foreach ($toErase as $erase) {
                $this->manager->remove($erase);
            }
            $this->manager->flush();
        }


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
                $splitRef = preg_split( "/(\s|:)/ui", $bibleVerse->ref );
                if($input->getOption('orm') == false) {
                    $bibleVerseInstance = new BibleVerse();
                } else {
                    $bibleVerseInstance = new \App\Entity\BibleVerse();
                    $bibleBookQueryBuilder = $this->manager->createQueryBuilder();
                    try {
                        /**
                         * @var $bibleBook BibleBook
                         */
                        $bibleBook = $bibleBookQueryBuilder->select('bb')
                            ->from(BibleBook::class, 'bb')
                            ->innerJoin('bb.language', 'l')
                            ->where(
                                $bibleBookQueryBuilder->expr()
                                    ->eq('l.id', $bibleVersion->getLanguage()->getId())
                            )->andWhere(
                                $bibleBookQueryBuilder->expr()->eq('bb.shortName', ':shortName')
                            )->setParameter('shortName', $splitRef[0])
                            ->getQuery()->getSingleResult();
                        $bibleVerseInstance->setBook($bibleBook);
                        $bibleVerseInstance->setVerseText(
                            addslashes(preg_replace('/\n/', '', $bibleVerse->verse))
                        );
                        $bibleVerseInstance->setLocalReference(
                            $bibleBook->getShortName() . ' ' . $splitRef[1] . ':' . $splitRef[2]
                        );
                    } catch (NoResultException $e) {
                    } catch (NonUniqueResultException $e) {
                    }
                }
                $bibleVerseInstance->setChapter((int) $splitRef[1]);
                $bibleVerseInstance->setVerse((int) $splitRef[2]);
                $bibleVerseInstance->setReference($bibleVerse->ref);
                $bibleVerseInstance->setVerseTokens(
                    implode(
                        " ",
                        array_map(
                            'mb_strtolower',
                            $wordTokenizer->tokenize($bibleVerseInstance->getVerseText())
                        )
                    )
                );
                $bibleVerseInstance->setBibleVersion($bibleVersion);
                $this->manager->persist($bibleVerseInstance);

                $verseCsvWriter->insertOne([$bibleVerseInstance->getReference(), $bibleVerseInstance->getVerseText()]);
                $verseTokensCsvWriter->insertOne([$bibleVerseInstance->getReference(), $bibleVerseInstance->getVerseTokens()]);

                $stemmerLanguage = 'Wamania\\Snowball\\' . $bibleVersion->getLanguage()->getName();

                /**
                 * @var $stemmer Stemmer
                 */
                $stemmer = new $stemmerLanguage();

                $stemmedVerse = implode( ' ',
                    array_map(
                        function($word) use ($stemmer, $stemmedTokensCsvWriter, $bibleVerseInstance) {
                            return $stemmer->stem($word);
                        }, explode(' ', $bibleVerseInstance->getVerseTokens())
                    )
                );

                $bibleVerseInstance->setStemVerseTokens($stemmedVerse);
                $this->manager->persist($bibleVerseInstance);

                $stemmedTokensCsvWriter->insertOne([$bibleVerseInstance->getReference(), $stemmedVerse]);

                $i++;
                $io->progressAdvance();
            }
            $io->progressFinish();

            $io->text(sprintf('Submitting %d documents...', $i));

            try {
                $this->manager->flush();

                $csvFilePath = $this->tmpPath . DIRECTORY_SEPARATOR .
                    self::TMP_PREFIX . $input->getArgument('datasource') .'-text.csv';
                $io->comment(sprintf('Generating %s file', $csvFilePath));
                file_put_contents($csvFilePath, $verseCsvWriter->getContent());

                $csvFilePath = $this->tmpPath . DIRECTORY_SEPARATOR .
                    self::TMP_PREFIX . $input->getArgument('datasource') .'-tokens.csv';
                $io->comment(sprintf('Generating %s file', $csvFilePath));
                file_put_contents($csvFilePath, $verseTokensCsvWriter->getContent());

                $csvFilePath = $this->tmpPath . DIRECTORY_SEPARATOR .
                    self::TMP_PREFIX . $input->getArgument('datasource') .'-stem-tokens.csv';
                $io->comment(sprintf('Generating %s file', $csvFilePath));
                file_put_contents($csvFilePath, $stemmedTokensCsvWriter->getContent());

                $io->success(sprintf('CSV files created successfully.'));
            } catch (MongoDBException $e) {
                $io->error($e->getMessage());
            }
        }

        $io->success('Datasource imported successfully');
    }
}
