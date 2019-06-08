<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use ApiPlatform\Core\Annotation\ApiResource;

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


}