<?php

namespace App\Document;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * Class Vocabulary
 * @package App\Document
 *
 * @ApiResource()
 * @ODM\Document(collection="vocabulary.stem")
 * @ODM\UniqueIndex(keys={"word"="asc", "language"="asc"})
 */
class StemVocabulary
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
     * @ODM\ReferenceOne(targetDocument=Language::class, inversedBy="stemWords")
     */
    protected $language;

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
     * @return StemVocabulary
     */
    public function setWord(string $word): StemVocabulary
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
     * @return StemVocabulary
     */
    public function setLanguage(Language $language): StemVocabulary
    {
        $this->language = $language;
        return $this;
    }
}