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
}