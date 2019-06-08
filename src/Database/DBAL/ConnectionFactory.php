<?php

namespace App\Database\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ConnectionFactory
{
    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(ParameterBagInterface $params, EventManager $eventManager, Configuration $config)
    {
        $this->params = $params;
        $this->eventManager = $eventManager;
        $this->config = $config;
    }

    /**
     * @param string $connectionName
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getConnection(string $connectionName)
    {
        return DriverManager::getConnection($this->params->get($connectionName), $this->config, $this->eventManager);
    }
}