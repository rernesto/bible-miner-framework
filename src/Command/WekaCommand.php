<?php


namespace App\Command;


use App\Database\DBAL\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class WekaCommand extends MinerCommand
{
    /**
     * @var string
     */
    protected $simpleCLIPrefix = '';

    protected $wekaConfig = [];

    protected function configure()
    {
        $this
            ->setDescription('Shows weka command.')

        ;
    }

    public function __construct(ConnectionFactory $dbalConnectionFactory, DocumentManager $dm,
                                EntityManagerInterface $em, ParameterBagInterface $parameters, ?string $name = null)
    {
        $this->wekaConfig = $parameters->get('weka');
        parent::__construct($dbalConnectionFactory, $dm, $em, $parameters, $name);
    }

    protected function getSimpleCLIPrefix()
    {
        if(empty($this->simpleCLIPrefix)) {
            // Java executable
            $this->simpleCLIPrefix = $this->wekaConfig ['java']['exec'];
            //Java options
            foreach($this->wekaConfig ['java']['options'] as $option) {
                $this->simpleCLIPrefix .= " " . $option;
            }
            // Java Classpath
            $this->simpleCLIPrefix .= " " . '-cp' . " " . "\"{$this->_createJavaClassPath()}\"";
        }

        return $this->simpleCLIPrefix;
    }

    protected function generateDatabaseUtilsProps(Connection $connection) {
        $dbPath = $connection->getParams()['path'];
        $fileName = 'DatabaseUtils.props';
        $databaseUtilsProps = file_get_contents(
            $this->parameters->get('kernel.project_dir') .
            DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .
            'packages' . DIRECTORY_SEPARATOR . 'weka' .
            DIRECTORY_SEPARATOR . $fileName
        );
        $databaseUtilsProps = str_replace('[dbPath]', $dbPath, $databaseUtilsProps);
        file_put_contents(
            $this->parameters->get('kernel.project_dir') .
            DIRECTORY_SEPARATOR . $fileName, $databaseUtilsProps
        );
    }

    private function _createJavaClassPath() {
        $javaClassPath = '';
        foreach ($this->wekaConfig['java']['classpath'] as $classPath) {
            $javaClassPath .= $this->wekaConfig['home'] . DIRECTORY_SEPARATOR .
                $classPath . ":";
        }
        foreach ($this->wekaConfig['packages'] as $package) {
            foreach ($package['classpath'] as $packageClassPath) {
                $javaClassPath .= $this->wekaConfig['home'] . DIRECTORY_SEPARATOR .
                    'packages' . DIRECTORY_SEPARATOR . $packageClassPath . ':';
            }
        }
        return rtrim($javaClassPath, ":");
    }
}