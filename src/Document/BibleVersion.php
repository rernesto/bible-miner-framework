<?php

namespace App\Document;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\PersistentCollection;

/**
 * Class BibleVersion
 * @package App\Document
 *
 * @ApiResource()
 * @ODM\Document(collection="bible.versions")
 */
class BibleVersion
{
    /**
     * @var string
     *
     * @ODM\Id()
     */
    protected $id;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $shortName;

    /**
     * @var Language
     *
     * @ODM\ReferenceOne(targetDocument=Language::class, inversedBy="bibleVersions")
     */
    protected $language;

    /**
     * @var PersistentCollection
     *
     * @ODM\ReferenceMany(targetDocument=BibleVerse::class, mappedBy="bibleVersion")
     *
     */
    protected $bibleVerses;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return BibleVersion
     */
    public function setShortName(string $shortName): BibleVersion
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * @param Language|object $language
     * @return BibleVersion
     */
    public function setLanguage(Language $language): BibleVersion
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getBibleVerses(): PersistentCollection
    {
        return $this->bibleVerses;
    }

    /**
     * @param PersistentCollection $bibleVerses
     * @return BibleVersion
     */
    public function setBibleVerses(PersistentCollection $bibleVerses): BibleVersion
    {
        $this->bibleVerses = $bibleVerses;
        return $this;
    }
}