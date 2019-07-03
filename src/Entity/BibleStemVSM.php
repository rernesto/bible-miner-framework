<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get"
 *     },
 *     itemOperations={
 *          "get"
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BibleStemVSMRepository")
 */
class BibleStemVSM
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BibleVerse")
     * @ORM\JoinColumn(nullable=false)
     */
    private $verse;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StemVocabulary")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vocabulary;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $tfIdfValue;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVerse(): ?BibleVerse
    {
        return $this->verse;
    }

    public function setVerse(?BibleVerse $verse): self
    {
        $this->verse = $verse;

        return $this;
    }

    public function getVocabulary(): ?StemVocabulary
    {
        return $this->vocabulary;
    }

    public function setVocabulary(?StemVocabulary $vocabulary): self
    {
        $this->vocabulary = $vocabulary;

        return $this;
    }

    public function getTfIdfValue(): ?float
    {
        return $this->tfIdfValue;
    }

    public function setTfIdfValue(float $tfIdfValue): self
    {
        $this->tfIdfValue = $tfIdfValue;

        return $this;
    }
}
