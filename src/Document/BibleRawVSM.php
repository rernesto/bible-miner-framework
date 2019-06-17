<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * Class BibleVersion
 * @package App\Document
 *
 * @ApiResource()
 * @ODM\Document(collection="bible.raw_vsm")
 * @ODM\UniqueIndex(keys={"verse"="asc", "vocabulary"="asc"})
 */
class BibleRawVSM
{
    /**
     * @var string
     *
     * @ODM\Id()
     */
    protected $id;

    /**
     * @var BibleVerse
     *
     * @ODM\ReferenceOne(targetDocument=BibleVerse::class, inversedBy="rawVsmValues")
     */
    protected $verse;

    /**
     * @var RawVocabulary
     *
     * @ODM\ReferenceOne(targetDocument=RawVocabulary::class, inversedBy="rawVsmValues")
     */
    protected $vocabulary;

    /**
     * @var float
     *
     * @ODM\Field(type="float")
     */
    protected $tfIdfValue;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return BibleVerse
     */
    public function getVerse(): BibleVerse
    {
        return $this->verse;
    }

    /**
     * @param BibleVerse $verse
     * @return BibleRawVSM
     */
    public function setVerse(BibleVerse $verse): BibleRawVSM
    {
        $this->verse = $verse;
        return $this;
    }

    /**
     * @return RawVocabulary
     */
    public function getVocabulary(): RawVocabulary
    {
        return $this->vocabulary;
    }

    /**
     * @param RawVocabulary $vocabulary
     * @return BibleRawVSM
     */
    public function setVocabulary(RawVocabulary $vocabulary): BibleRawVSM
    {
        $this->vocabulary = $vocabulary;
        return $this;
    }

    /**
     * @return float
     */
    public function getTfIdfValue(): float
    {
        return $this->tfIdfValue;
    }

    /**
     * @param float $tfIdfValue
     * @return BibleRawVSM
     */
    public function setTfIdfValue(float $tfIdfValue): BibleRawVSM
    {
        $this->tfIdfValue = $tfIdfValue;
        return $this;
    }

}