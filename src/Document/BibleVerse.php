<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ODM\MongoDB\PersistentCollection;

/**
 * Class BibleVersion
 * @package App\Document
 *
 * @ApiResource()
 * @ODM\Document(collection="bible.verses")
 */
class BibleVerse
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
    protected $reference;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $verseText;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $verseTokens;

    /**
     * @var BibleVersion
     *
     * @ODM\ReferenceOne(targetDocument=BibleVersion::class, inversedBy="bibleVerses")
     */
    protected $bibleVersion;

    /**
     * @var PersistentCollection
     *
     * @ODM\ReferenceMany(targetDocument=BibleRawVSM::class, mappedBy="bibleVerse")
     *
     */
    protected $rawVsmValues;

    /**
     * @var PersistentCollection
     *
     * @ODM\ReferenceMany(targetDocument=BibleStemVSM::class, mappedBy="bibleVerse")
     *
     */
    protected $stemVsmValues;

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
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return BibleVerse
     */
    public function setReference(string $reference): BibleVerse
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return string
     */
    public function getVerseText(): string
    {
        return $this->verseText;
    }

    /**
     * @param string $verseText
     * @return BibleVerse
     */
    public function setVerseText(string $verseText): BibleVerse
    {
        $this->verseText = $verseText;
        return $this;
    }

    /**
     * @return string
     */
    public function getVerseTokens(): string
    {
        return $this->verseTokens;
    }

    /**
     * @param string $verseTokens
     * @return BibleVerse
     */
    public function setVerseTokens(string $verseTokens): BibleVerse
    {
        $this->verseTokens = $verseTokens;
        return $this;
    }

    /**
     * @return BibleVersion
     */
    public function getBibleVersion(): BibleVersion
    {
        return $this->bibleVersion;
    }

    /**
     * @param BibleVersion $bibleVersion
     * @return BibleVerse
     */
    public function setBibleVersion(BibleVersion $bibleVersion): BibleVerse
    {
        $this->bibleVersion = $bibleVersion;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getRawVsmValues(): PersistentCollection
    {
        return $this->rawVsmValues;
    }

    /**
     * @param PersistentCollection $rawVsmValues
     * @return BibleVerse
     */
    public function setRawVsmValues(PersistentCollection $rawVsmValues): BibleVerse
    {
        $this->rawVsmValues = $rawVsmValues;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getStemVsmValues(): PersistentCollection
    {
        return $this->stemVsmValues;
    }

    /**
     * @param PersistentCollection $stemVsmValues
     * @return BibleVerse
     */
    public function setStemVsmValues(PersistentCollection $stemVsmValues): BibleVerse
    {
        $this->stemVsmValues = $stemVsmValues;
        return $this;
    }
}