<?php


namespace App\Command;


use App\Document\BibleRawVSM;
use App\Document\BibleStemVSM;
use App\Document\BibleVerse;
use App\Document\BibleVersion;
use App\Document\RawVocabulary;
use App\Document\StemVocabulary;
use Doctrine\DBAL\DBALException;
use Doctrine\ODM\MongoDB\MongoDBException;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDuplicateKeyException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class BibleMinerIndexCommand extends WekaCommand
{
    protected static $defaultName = 'bible-miner:index';

    protected $arffFile;

    protected $jsonFilePath;

    /**
     * @var BibleVersion
     */
    protected $bibleVersionDocument;

    protected function configure()
    {
        $this
            ->setDescription('Import Bible text from data source.')
            ->addArgument(
                'bible-version', InputArgument::REQUIRED,
                'Data source name SpaRVG, SpaRV1865, KJ2000, etc.'
            )
            ->addOption(
                'stem', null,InputOption::VALUE_OPTIONAL,
                'Create stemmed vocabulary and index', true
            )
            ->addOption(
                'stopwords', null,InputOption::VALUE_OPTIONAL,
                'Use stopwords', true
            )
            ->addOption(
                'normalize', null,InputOption::VALUE_OPTIONAL,
                'Normalize values', true
            )
            ->addOption(
                'threshold', null,InputOption::VALUE_OPTIONAL,
                'Minimum frequency threshold', 1
            )
            ->addOption(
                'vocabulary-size', null,InputOption::VALUE_OPTIONAL,
                'Create stemmed vocabulary and index', 20000
            )
            ->addOption(
                'use-database', null,InputOption::VALUE_OPTIONAL,
                'Use database instead CSV (Not recommended)', false
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->arffFile = $this->tmpPath . DIRECTORY_SEPARATOR .
            self::TMP_PREFIX . $input->getArgument('bible-version') .
            (($input->getOption('stem') == true)?'-stem':'') . '.arff';

        $this->jsonFilePath = $this->tmpPath . DIRECTORY_SEPARATOR . self::TMP_PREFIX .
            $input->getArgument('bible-version') .
            (($input->getOption('stem') == true)?'-stem':'') . '.json';



        $this->bibleVersionDocument = $this->dm->getRepository(BibleVersion::class)
            ->findOneBy(['shortName' => $input->getArgument('bible-version')]);


        if ($input->getOption('use-database') == true) {
            try {
                $io->warning('This option its\'n not recommended and discontinued.' );
                $this->_createArffFromDatabase($input, $io);
            } catch (DBALException $e) {
                $io->error($e->getMessage());
            }

        } else {
            $this->_createArffFromCSV($input, $io);
        }

        $this->_applyNominalToString($io);
        $this->_applyStringToWordVector($input, $io);

        if ($input->getOption('normalize') == true) {
            $this->_normalize($io);
        }

        $this->_convertToJSON($io, $input);
    }

    private function _convertToJSON(SymfonyStyle $io, InputInterface $input) {

        $wekaProcess = Process::fromShellCommandline(
            $this->getSimpleCLIPrefix() . " " .
            'weka.core.converters.JSONSaver' . " " .
            '-i ' . $this->arffFile, null, [
                'WEKA_HOME' => $this->parameters->get('weka')['home'],
            ]
        );

        try {
            $wekaProcess->mustRun();
            $jsonDecoder = new JsonDecode([JsonDecode::ASSOCIATIVE => true]);
            $jsonArffData = $jsonDecoder->decode($wekaProcess->getOutput(), JsonEncoder::FORMAT);

            $jsonArffAttributes = $jsonArffData['header']['attributes'][0]['labels'];
            array_shift($jsonArffData['header']['attributes']);
            $jsonVocabulary = $jsonArffData['header']['attributes'];
            $jsonArffData = $jsonArffData['data'];
            array_unshift($jsonArffData[0]['values'], "0:'" . $jsonArffAttributes[0] . "'");
            $totalRecords = count($jsonArffData);

            try {
                $bibleVerses = $this->dm->createQueryBuilder(BibleVerse::class)
                    ->select('id')
                    ->field('bibleVersion.id')->equals($this->bibleVersionDocument->getId())
                    ->hydrate(false)
                    ->getQuery()->execute();
                $bibleVerses = array_map('strval',
                    array_map('current', $bibleVerses->toArray())
                );

                if ($input->getOption('stem') == true) {
                    $vsmQueryBuilder = $this->dm->createQueryBuilder(BibleStemVSM::class);

                } else {
                    $vsmQueryBuilder = $this->dm->createQueryBuilder(BibleRawVSM::class);
                }

                $vsmQueryBuilder->remove()
                    ->field('bibleVerse.id')->in($bibleVerses)
                    ->getQuery()->execute();
            } catch (MongoDBException $e) {
                $io->error($e->getMessage());
            }

            $language = $this->bibleVersionDocument->getLanguage();
            $vocabularyDocuments = [];
            $vocabularyDocumentRepository = $input->getOption('stem') == true?
                $this->dm->getRepository(StemVocabulary::class) :
                $this->dm->getRepository(RawVocabulary::class)
            ;
            $io->comment('Processing records...');
            $io->progressStart($totalRecords);
            for($i=0; $i<$totalRecords; $i++ ) {
                array_shift($jsonArffData[$i]['values']);
                $bibleVerse = $this->dm->getRepository(BibleVerse::class)
                    ->findOneBy(
                        [
                            'reference' => $jsonArffAttributes[$i],
                            'bibleVersion.id' => $this->bibleVersionDocument->getId()
                        ]
                    );

                foreach ($jsonArffData[$i]['values'] as $k => $scoringRecord) {
                    $scoringRecord = explode(':', $scoringRecord);

                    if(isset($jsonVocabulary[$scoringRecord[0]-1]['name'])) {
                        $jsonVocabulary[$scoringRecord[0] - 1] = $jsonVocabulary[$scoringRecord[0] - 1]['name'];

                        $vocabularyDocument = $vocabularyDocumentRepository->findOneBy(
                            ['language.id' => $language->getId(), 'word' => $jsonVocabulary[$scoringRecord[0] - 1]]
                        );

                        if(is_null($vocabularyDocument)) {
                            if ($input->getOption('stem') == true) {
                                $vocabularyDocument = new StemVocabulary();
                            } else {
                                $vocabularyDocument = new RawVocabulary();
                            }
                            $vocabularyDocument->setWord($jsonVocabulary[$scoringRecord[0] - 1])
                                ->setLanguage($language);
                            $this->dm->persist($vocabularyDocument);
                        }

                        $vocabularyDocuments[$scoringRecord[0] - 1] = $vocabularyDocument;
                    } else {
                        $vocabularyDocument = $vocabularyDocuments[$scoringRecord[0] - 1];
                    }
                    if ($input->getOption('stem') == true) {
                        $vsmDocument = new BibleStemVSM();
                    } else {
                        $vsmDocument = new BibleRawVSM();
                    }
                    $vsmDocument->setVerse($bibleVerse)
                        ->setVocabulary($vocabularyDocument)
                        ->setTfIdfValue($scoringRecord[1]);
                    $this->dm->persist($vsmDocument);

                }

                $io->progressAdvance();
            }
            $io->progressFinish();
            try {
                $this->dm->flush();
            } catch (BulkWriteException $e) {
            } catch (MongoDBException $e) {
                $io->error($e->getMessage());
                exit();
            } catch (MongoDuplicateKeyException $e){}
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function _normalize(SymfonyStyle $io)
    {
        $wekaProcess = Process::fromShellCommandline(
            $this->getSimpleCLIPrefix() . " " .
            'weka.filters.unsupervised.instance.Normalize -N 1.0 -L 2.0' . " " .
            '-i ' . $this->arffFile, null, [
                'WEKA_HOME' => $this->parameters->get('weka')['home'],
            ]
        );

        try {
            $wekaProcess->mustRun();
            file_put_contents($this->arffFile, $wekaProcess->getOutput());
            $io->success(sprintf('Normalize filter applied to \'%s\'', $this->arffFile));
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function _applyStringToWordVector(InputInterface $input, SymfonyStyle $io)
    {
        $stopWords = ($input->getOption('stopwords') == false)?
            'weka.core.stopwords.Null':'"weka.core.stopwords.WordsFromFile '.
            '-stopwords ' . $this->dataPath . DIRECTORY_SEPARATOR . 'stopwords' . DIRECTORY_SEPARATOR .
            strtolower($this->bibleVersionDocument->getLanguage()->getName()) .
            '.txt"';

        $threshold = (int) $input->getOption('threshold');

        $vocabularySize = (int) $input->getOption('vocabulary-size');

        $wekaProcess = Process::fromShellCommandline(
            $this->getSimpleCLIPrefix() . " " .
            'weka.filters.unsupervised.attribute.StringToWordVector -R last -W' . " " . $vocabularySize . " " .
            '-prune-rate -1.0 -C -I -N 0 -L -stemmer' . " " . 'weka.core.stemmers.NullStemmer' . " " .
            '-stopwords-handler ' . $stopWords . " " . '-M' . " " . $threshold . " " .
//            '-tokenizer weka.core.tokenizers.AlphabeticTokenizer' . " " .
            '-tokenizer "weka.core.tokenizers.WordTokenizer -delimiters \" \""' . " " .
            '-dictionary' . " " . $this->tmpPath . DIRECTORY_SEPARATOR . self::TMP_PREFIX .
            $input->getArgument('bible-version') .
            (($input->getOption('stem') == true)?'-stem':'') . '.dct.txt' . " " .
            '-i ' . $this->arffFile, null, [
                'WEKA_HOME' => $this->parameters->get('weka')['home'],
            ]
        );

        try {
            $wekaProcess->mustRun();
            file_put_contents($this->arffFile, $wekaProcess->getOutput());
            $io->success(sprintf('StringToWordVector filter applied to \'%s\'', $this->arffFile));
            $io->success(
                sprintf(
                    'Dictionary file saved to to \'%s\'',
                    $this->tmpPath . DIRECTORY_SEPARATOR . self::TMP_PREFIX .
                $input->getArgument('bible-version') .
                (($input->getOption('stem') == true)?'-stem':'') . '.dct.txt'
                )
            );
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function _applyNominalToString(SymfonyStyle $io)
    {
        $wekaProcess = Process::fromShellCommandline(
            $this->getSimpleCLIPrefix() . " " .
            'weka.filters.unsupervised.attribute.NominalToString -C last ' .
            '-i ' . $this->arffFile, null, [
                'WEKA_HOME' => $this->parameters->get('weka')['home'],
            ]
        );

        try {
            $wekaProcess->mustRun();
            file_put_contents($this->arffFile, $wekaProcess->getOutput());
            $io->success(sprintf('NominalToString filter applied to \'%s\'', $this->arffFile));
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }
    }

    /**
     * @param InputInterface $input
     * @param SymfonyStyle $io
     * @throws DBALException
     */
    private function _createArffFromDatabase(InputInterface $input, SymfonyStyle $io)
    {
        $this->generateDatabaseUtilsProps(
            $this->dbalConnectionFactory->getConnection(
                'db' . $input->getArgument('bible-version')
            )
        );
        $sql = "SELECT ref, verse FROM bible";
        $wekaProcess = Process::fromShellCommandline(
            $this->getSimpleCLIPrefix() . " " . 'weka.core.converters.DatabaseLoader -Q' .
            " " . "\"{$sql}\"", null, [
                'WEKA_HOME' => $this->parameters->get('weka')['home'],
            ]
        );

        try {
            $wekaProcess->mustRun();
            file_put_contents($this->arffFile, $wekaProcess->getOutput());
            $io->success(sprintf('Arff file saved to \'%s\'', $this->arffFile));
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function _createArffFromCSV(InputInterface $input, SymfonyStyle $io)
    {
        $wekaProcess = Process::fromShellCommandline(
            $this->getSimpleCLIPrefix() . " " . 'weka.core.converters.CSVLoader' . " " .
            $this->tmpPath . DIRECTORY_SEPARATOR . self::TMP_PREFIX . $input->getArgument('bible-version') .
            (($input->getOption('stem') == true)?'-stem':'') . '-tokens' . '.csv' . " " . '-B 31103',
            null, [
                'WEKA_HOME' => $this->parameters->get('weka')['home'],
            ]
        );

        try {
            $wekaProcess->mustRun();
            file_put_contents($this->arffFile, $wekaProcess->getOutput());
            $io->success(sprintf('Arff file saved to \'%s\'', $this->arffFile));
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }
    }
}