<?php

declare(strict_types=1);

namespace App\Command;

use App\Document\BibleVerse;
use App\Document\BibleVersion;
use Doctrine\DBAL\DBALException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Phpml\Tokenization\WordTokenizer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BibleTextImportCommand extends BibleMinerCommand
{
    protected static $defaultName = 'bible:text:import';

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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $dbalConnection = $this->dbalConnectionFactory->getConnection(
            'db' . $input->getArgument('datasource')
        );

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
                $i++;
                $io->progressAdvance();
            }
            $io->progressFinish();

            $io->text(sprintf('Commiting %d documents to collection...', $i));

            try {
                $this->dm->flush();
                $io->success(sprintf('Commited %d documents to collection...', $i));
            } catch (MongoDBException $e) {
                $io->error($e->getMessage());
            }
        }

        $io->success('Datasource imported successfully');
    }
}
