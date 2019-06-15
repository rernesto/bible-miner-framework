<?php


namespace App\Command;


use App\Document\BibleVersion;
use App\Document\RawVocabulary;
use App\Document\StemVocabulary;
use Doctrine\ODM\MongoDB\MongoDBException;
use League\Csv\Reader;
use MongoDB\Driver\Exception\BulkWriteException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BibleMinerImportVocabulary extends MinerCommand
{
    protected static $defaultName = 'bible-miner:vocabulary:import';

    /**
     * @var BibleVersion
     */
    protected $bibleVersionDocument;

    protected function configure()
    {
        $this
            ->setDescription('Import Bible text from data source.')
            ->addArgument(
                'dictionary', InputArgument::REQUIRED,
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
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->bibleVersionDocument = $this->dm->getRepository(BibleVersion::class)
            ->findOneBy(['shortName' => $input->getArgument('dictionary')]);

        $language = $this->bibleVersionDocument->getLanguage();

        $rawDictionaryReader = Reader::createFromPath(
            $this->tmpPath . DIRECTORY_SEPARATOR . self::TMP_PREFIX .
            $input->getArgument('dictionary') . '.dct.txt'
        );

        $rawRecords = $rawDictionaryReader->getRecords();
        $io->writeln('Creating raw vocabulary...');
        $io->progressStart($rawDictionaryReader->count());
        foreach ($rawRecords as $rawRecord) {
            $rawVocabularyDocument = new RawVocabulary();
            $rawVocabularyDocument->setLanguage($language);
            $rawVocabularyDocument->setWord($rawRecord[0]);
            $this->dm->persist($rawVocabularyDocument);
            $io->progressAdvance();
        }
        $io->progressFinish();

        try {
            $this->dm->flush(['continueOnError' => true]);
            $io->success('Raw vocabulary imported successfully');
        } catch (BulkWriteException $e) {
        }

        $stemDictionaryReader = Reader::createFromPath(
            $this->tmpPath . DIRECTORY_SEPARATOR . self::TMP_PREFIX .
            $input->getArgument('dictionary') . '-stem.dct.txt'
        );

        $stemRecords = $stemDictionaryReader->getRecords();
        $io->writeln('Creating stem vocabulary...');
        $io->progressStart($stemDictionaryReader->count());
        foreach ($stemRecords as $stemRecord) {
            $stemVocabularyDocument = new StemVocabulary();
            $stemVocabularyDocument->setLanguage($language);
            $stemVocabularyDocument->setWord($stemRecord[0]);
            $this->dm->persist($stemVocabularyDocument);
            $io->progressAdvance();
        }
        $io->progressFinish();

        try {
            $this->dm->flush(['continueOnError' => true]);
            $io->success('Stem vocabulary imported successfully');
        } catch (BulkWriteException $e) {
        }
    }
}