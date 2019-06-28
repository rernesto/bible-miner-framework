<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\Api\Operation\VerseSearchOperationApiController;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "search_ranked"={
 *              "method"="GET",
 *              "controller"=VerseSearchOperationApiController::class,
 *              "path"="/bible_verses/search_ranked",
 *              "swagger_context" = {
 *                  "parameters"={
 *                      {
 *                          "name"="search_query",
 *                          "in"="query",
 *                          "description"="Search Query",
 *                          "required"="true",
 *                          "type"="string"
 *                      },
 *                      {
 *                          "name"="bible_version",
 *                          "in"="query",
 *                          "description"="Bible Version",
 *                          "required"="true",
 *                          "type"="integer"
 *                      }
 *                  }
 *              }
 *          }
 *     }
 * )
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
     * @var string
     * @ORM\Column(type="string", length=16)
     */
    private $reference;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $verseText;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $verseTokens;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $stemVerseTokens;

    /**
     * @var BibleVersion
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

    public function getStemVerseTokens(): ?string
    {
        return $this->stemVerseTokens;
    }

    public function setStemVerseTokens(string $stemVerseTokens): self
    {
        $this->stemVerseTokens = $stemVerseTokens;

        return $this;
    }
}
