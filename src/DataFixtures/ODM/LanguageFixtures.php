<?php

namespace App\DataFixtures\ODM;

use App\Document\Language;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LanguageFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * Load data from yaml file
     *
     * @return array
     */
    protected function loadData()
    {
        return Yaml::parse(
            file_get_contents(
                realpath(__DIR__ . '/../fixtures/languages.yaml')
            )
        );
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $data = $this->loadData();

        foreach ($data[Language::class] as $k => $record) {
            /**
             * @var $document Language
             */
            $document = new Language();
            $document->setShortName($record['shortName']);
            $document->setName($record['name']);

            $manager->persist($document);
            $this->addReference(md5($document->getShortName()), $document);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}