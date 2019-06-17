<?php

namespace App\Document;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\PersistentCollection;

/**
 * Class Vocabulary
 * @package App\Document
 *
 * @ApiResource()
 * @ODM\Document(collection="vocabulary.raw")
 * @ODM\UniqueIndex(keys={"word"="asc", "language"="asc"})
 */
class RawVocabulary
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
    protected $word;

    /**
     * @var Language
     *
     * @ODM\ReferenceOne(targetDocument=Language::class, inversedBy="rawWords")
     */
    protected $language;

    /**
     * @var PersistentCollection
     *
     * @ODM\ReferenceMany(targetDocument=BibleRawVSM::class, mappedBy="rawVocabulary")
     *
     */
    protected $rawVsmValues;

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
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * @param string $word
     * @return RawVocabulary
     */
    public function setWord(string $word): RawVocabulary
    {
        $this->word = $word;
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
     * @param Language $language
     * @return RawVocabulary
     */
    public function setLanguage(Language $language): RawVocabulary
    {
        $this->language = $language;
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
     * @return RawVocabulary
     */
    public function setRawVsmValues(PersistentCollection $rawVsmValues): RawVocabulary
    {
        $this->rawVsmValues = $rawVsmValues;
        return $this;
    }
}