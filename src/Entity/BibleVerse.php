<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\BibleVerseRepository")
 */
class BibleVerse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $reference;

    /**
     * @ORM\Column(type="text")
     */
    private $verseText;

    /**
     * @ORM\Column(type="text")
     */
    private $verseTokens;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BibleVersion")
     * @ORM\JoinColumn(nullable=false)
     */
    private $bibleVersion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getVerseText(): ?string
    {
        return $this->verseText;
    }

    public function setVerseText(string $verseText): self
    {
        $this->verseText = $verseText;

        return $this;
    }

    public function getVerseTokens(): ?string
    {
        return $this->verseTokens;
    }

    public function setVerseTokens(string $verseTokens): self
    {
        $this->verseTokens = $verseTokens;

        return $this;
    }

    public function getBibleVersion(): ?BibleVersion
    {
        return $this->bibleVersion;
    }

    public function setBibleVersion(?BibleVersion $bibleVersion): self
    {
        $this->bibleVersion = $bibleVersion;

        return $this;
    }
}
