<?php

namespace App\Command;


use App\Database\DBAL\ConnectionFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class MinerCommand extends Command
{
    /**
     * @var ConnectionFactory
     */
    protected $dbalConnectionFactory;

    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var string
     */
    protected $dataPath;

    public CONST TMP_PREFIX = '__';

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ParameterBagInterface
     */
    protected $parameters;

    public function __construct(ConnectionFactory $dbalConnectionFactory,
                                DocumentManager $dm, EntityManagerInterface $em, ParameterBagInterface $parameters,
                                ?string $name = null
    )
    {
        $this->dbalConnectionFactory = $dbalConnectionFactory;
        $this->dm = $dm;
        $this->em = $em;
        $this->parameters = $parameters;
        $this->tmpPath = $this->parameters->get('kernel.project_dir') . DIRECTORY_SEPARATOR .
            'var' . DIRECTORY_SEPARATOR . 'cache';
        parent::__construct($name);

        $this->dataPath = $this->parameters->get('kernel.project_dir') . DIRECTORY_SEPARATOR .
            'data';
        parent::__construct($name);
    }
}