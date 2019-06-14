<?php

namespace App\Command;


use App\Database\DBAL\ConnectionFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
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

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var ParameterBagInterface
     */
    protected $parameters;

    public function __construct(ConnectionFactory $dbalConnectionFactory,
                                DocumentManager $dm, ParameterBagInterface $parameters,
                                ?string $name = null
    )
    {
        $this->dbalConnectionFactory = $dbalConnectionFactory;
        $this->dm = $dm;
        $this->parameters = $parameters;
        $this->tmpPath = $this->parameters->get('kernel.project_dir') . DIRECTORY_SEPARATOR .
            'var' . DIRECTORY_SEPARATOR . 'cache';
        parent::__construct($name);

        $this->dataPath = $this->parameters->get('kernel.project_dir') . DIRECTORY_SEPARATOR .
            'data';
        parent::__construct($name);
    }
}