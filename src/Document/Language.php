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
 * @ODM\Document(collection="languages")
 */
class Language
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
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $name;

    /**
     * @var PersistentCollection
     *
     * @ODM\ReferenceMany(targetDocument=BibleVersion::class, mappedBy="language")
     *
     */
    protected $bibleVersions;

    /**
     * @var PersistentCollection
     *
     * @ODM\ReferenceMany(targetDocument=RawVocabulary::class, mappedBy="language")
     *
     */
    protected $rawWords;

    /**
     * @var PersistentCollection
     *
     * @ODM\ReferenceMany(targetDocument=StemVocabulary::class, mappedBy="language")
     *
     */
    protected $stemWords;

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
    public function setShortName(string $shortName): Language
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Language
     */
    public function setName(string $name): Language
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getBibleVersions(): PersistentCollection
    {
        return $this->bibleVersions;
    }

    /**
     * @param PersistentCollection $bibleVersions
     * @return Language
     */
    public function setBibleVersions(PersistentCollection $bibleVersions): Language
    {
        $this->bibleVersions = $bibleVersions;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getRawWords(): PersistentCollection
    {
        return $this->rawWords;
    }

    /**
     * @param PersistentCollection $rawWords
     * @return Language
     */
    public function setRawWords(PersistentCollection $rawWords): Language
    {
        $this->rawWords = $rawWords;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getStemWords(): PersistentCollection
    {
        return $this->stemWords;
    }

    /**
     * @param PersistentCollection $stemWords
     * @return Language
     */
    public function setStemWords(PersistentCollection $stemWords): Language
    {
        $this->stemWords = $stemWords;
        return $this;
    }
}