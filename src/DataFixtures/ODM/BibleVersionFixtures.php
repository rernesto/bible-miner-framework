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

        foreach ($data[BibleVersion::class] as $k => $record) {
            /**
             * @var $document BibleVersion
             */
            $document = new BibleVersion();
            $document->setShortName($record['shortName']);
            $document->setLanguage($this->getReference(md5($record['language'])));
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
        return 2;
    }
}