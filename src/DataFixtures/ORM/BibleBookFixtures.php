<?php

namespace App\DataFixtures\ORM;

use App\Entity\BibleBook;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class BibleBookFixtures extends Fixture implements OrderedFixtureInterface
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
                realpath(__DIR__ . '/../fixtures/bible_books.yaml')
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

        foreach ($data['BibleBook'] as $k => $record) {
            /**
             * @var $object BibleBook
             */
            $object = new BibleBook();
            $object->setShortName($record['shortName']);
            $object->setCanonicalName($record['canonicalName']);
            $object->setLanguage($this->getReference(md5($record['language'])));
            $manager->persist($object);

            $this->addReference(md5($object->getLanguage()->getShortName() . '-' . $object->getShortName()), $object);
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
        return 3;
    }
}