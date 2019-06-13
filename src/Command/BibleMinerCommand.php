<?php

namespace App\Command;


use App\Database\DBAL\ConnectionFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class BibleMinerCommand extends Command
{
    /**
     * @var ConnectionFactory
     */
    protected $dbalConnectionFactory;

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
        parent::__construct($name);
    }
}