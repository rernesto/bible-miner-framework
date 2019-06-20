<?php

namespace App\DataFixtures\ODM;

use App\Document\BibleVersion;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class BibleVersionFixtures extends Fixture implements OrderedFixtureInterface
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
                realpath(__DIR__ . '/../fixtures/bible_versions.yaml')
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

        foreach ($data['BibleVersion'] as $k => $record) {
            /**
             * @var $object BibleVersion
             */
            $object = new BibleVersion();
            $object->setShortName($record['shortName']);
            $object->setLanguage($this->getReference(md5($record['language'])));
            $manager->persist($object);

            $this->addReference(md5($object->getShortName()), $object);
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
        return 2;
    }
}