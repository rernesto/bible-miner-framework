<?php


namespace App\Command;


use App\Document\BibleRawVSM;
use App\Document\BibleStemVSM;
use App\Document\BibleVerse;
use App\Document\BibleVersion;
use App\Document\RawVocabulary;
use App\Document\StemVocabulary;
use Doctrine\DBAL\DBALException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
     * @var DocumentManager|EntityManagerInterface
     */
    protected $manager;

    /**
     * @var BibleVersion|\App\Entity\BibleVersion
     */
    protected $bibleVersion;

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
            ->addOption(
                'orm', null, InputOption::VALUE_OPTIONAL,
                'Use mapped ORM database', true
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



        if($input->getOption('orm') == false) {
            $this->manager = $this->dm;
            /**
             * @var $bibleVersion BibleVersion
             */
            $this->bibleVersion = $this->manager->getRepository(BibleVersion::class)
                ->findOneBy(['shortName' => $input->getArgument('bible-version')]);
        } else {
            $this->manager = $this->em;
            $this->bibleVersion = $this->manager->getRepository(\App\Entity\BibleVersion::class)
                ->findOneBy(['shortName' => $input->getArgument('bible-version')]);
        }


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

        if($input->getOption('orm') == false) {
            $this->_saveWithDocumentManager($io, $input);
        } else {
            $this->_saveWithEntityManager($io, $input);
        }
    }

    private function _saveWithEntityManager(SymfonyStyle $io, InputInterface $input)
    {
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


            if ($input->getOption('stem') == true) {
                $vsmQueryBuilder = $this->em->getRepository(\App\Entity\BibleStemVSM::class)
                    ->createQueryBuilder('bvsm');

            } else {
                $vsmQueryBuilder = $this->em->getRepository(\App\Entity\BibleRawVSM::class)
                    ->createQueryBuilder('bvsm');
            }

            $bibleVerses = $this->em->getRepository(\App\Entity\BibleVerse::class)
                ->createQueryBuilder('bv')
                ->innerJoin('bv.bibleVersion', 'bvbv')
                ->where('bv.bibleVersion = :bibleVersion')
                ->setParameter('bibleVersion', $this->bibleVersion)
                ->getQuery()->getResult();

            $vsmQueryBuilder
                ->where('bvsm.verse in (:bibleVerses)')
                ->setParameter('bibleVerses', $bibleVerses)
                ->delete()
                ->getQuery()->execute();

            $language = $this->bibleVersion->getLanguage();
            $vocabularyObjects = [];
            $vocabularyRepository = $input->getOption('stem') == true?
                $this->em->getRepository(\App\Entity\StemVocabulary::class) :
                $this->em->getRepository(\App\Entity\RawVocabulary::class)
            ;
            $io->comment('Processing records...');
            $io->progressStart($totalRecords);

            for($i=0; $i<$totalRecords; $i++ ) {
                array_shift($jsonArffData[$i]['values']);

                try {
                    $bibleVerse = $this->em->getRepository(\App\Entity\BibleVerse::class)
                        ->createQueryBuilder('bv')
                        ->innerJoin('bv.bibleVersion', 'bvbv')
                        ->where('bvbv.shortName = :shortName')
                        ->andWhere('bv.reference = :reference')
                        ->setParameter('shortName', $this->bibleVersion->getShortName())
                        ->setParameter('reference', $jsonArffAttributes[$i])
                        ->getQuery()->getSingleResult();
                } catch (NoResultException $e) {
//                    $io->error($e->getMessage());
                    exit();
                } catch (NonUniqueResultException $e) {
//                    $io->error($e->getMessage());
                    exit();
                }

                foreach ($jsonArffData[$i]['values'] as $k => $scoringRecord) {

                    $scoringRecord = explode(':', $scoringRecord);

                    if(isset($jsonVocabulary[$scoringRecord[0]-1]['name'])) {
                        $jsonVocabulary[$scoringRecord[0] - 1] = $jsonVocabulary[$scoringRecord[0] - 1]['name'];

                        try {
                            $vocabularyObject = $vocabularyRepository->createQueryBuilder('v')
                                ->innerJoin('v.language', 'l')
                                ->where('l.id = :languageId')
                                ->andWhere('v.word = :word')
                                ->setParameter('word', $jsonVocabulary[$scoringRecord[0] - 1])
                                ->setParameter('languageId', $language->getId())
                                ->getQuery()->getSingleResult();
                        } catch (NoResultException $e) {
                            if ($input->getOption('stem') == true) {
                                $vocabularyObject = new \App\Entity\StemVocabulary();
                            } else {
                                $vocabularyObject = new \App\Entity\RawVocabulary();
                            }
                            $vocabularyObject->setWord($jsonVocabulary[$scoringRecord[0] - 1])
                                ->setLanguage($language);
                            $this->em->persist($vocabularyObject);
                            $this->em->flush();
                        } catch (NonUniqueResultException $e) {
                            $io->error($e->getMessage());
                            $vocabularyObject = null;
                        };
                        $vocabularyObjects[$scoringRecord[0] - 1] = $vocabularyObject;
                    } else {
                        $vocabularyObject = $vocabularyObjects[$scoringRecord[0] - 1];
                    }
                    if ($input->getOption('stem') == true) {
                        $vsmDocument = new \App\Entity\BibleStemVSM();
                    } else {
                        $vsmDocument = new \App\Entity\BibleRawVSM();
                    }
                    $vsmDocument->setVerse($bibleVerse)
                        ->setVocabulary($vocabularyObject)
                        ->setTfIdfValue($scoringRecord[1]);
                    $this->em->persist($vsmDocument);

                }

                $io->progressAdvance();
            }
            $io->progressFinish();
            $this->em->flush();
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }
    }

    private function _saveWithDocumentManager(SymfonyStyle $io, InputInterface $input)
    {

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
                    ->field('bibleVersion.id')->equals($this->bibleVersion->getId())
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

            $language = $this->bibleVersion->getLanguage();
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
                            'bibleVersion.id' => $this->bibleVersion->getId()
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
            strtolower($this->bibleVersion->getLanguage()->getName()) .
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