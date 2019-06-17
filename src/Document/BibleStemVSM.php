<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * Class BibleVersion
 * @package App\Document
 *
 * @ApiResource()
 * @ODM\Document(collection="bible.stem_vsm")
 * @ODM\UniqueIndex(keys={"verse"="asc", "vocabulary"="asc"})
 */
class BibleStemVSM
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
     * @ODM\ReferenceOne(targetDocument=BibleVerse::class, inversedBy="stemVsmValues")
     */
    protected $verse;

    /**
     * @var StemVocabulary
     *
     * @ODM\ReferenceOne(targetDocument=StemVocabulary::class, inversedBy="stemVsmValues")
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
     * @return BibleStemVSM
     */
    public function setVerse(BibleVerse $verse): BibleStemVSM
    {
        $this->verse = $verse;
        return $this;
    }

    /**
     * @return StemVocabulary
     */
    public function getVocabulary(): StemVocabulary
    {
        return $this->vocabulary;
    }

    /**
     * @param StemVocabulary $vocabulary
     * @return BibleStemVSM
     */
    public function setVocabulary(StemVocabulary $vocabulary): BibleStemVSM
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
     * @return BibleStemVSM
     */
    public function setTfIdfValue(float $tfIdfValue): BibleStemVSM
    {
        $this->tfIdfValue = $tfIdfValue;
        return $this;
    }

}