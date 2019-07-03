<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\Api\Operation\VerseSearchOperationApiController;
use App\Controller\Api\Operation\VerseChapterOperationApiController;
use App\Controller\Api\Operation\VerseNumberOperationApiController;
use App\Controller\Api\Operation\VerseReadOperationApiController;

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
 *          },
 *          "chapters"={
 *              "method"="GET",
 *              "controller"=VerseChapterOperationApiController::class,
 *              "path"="/bible_verses/chapters",
 *              "swagger_context" = {
 *                  "parameters"={
 *                      {
 *                          "name"="book_id",
 *                          "in"="query",
 *                          "description"="Book id",
 *                          "required"="true",
 *                          "type"="integer"
 *                      }
 *                  }
 *              }
 *          },
 *          "verses"={
 *              "method"="GET",
 *              "controller"=VerseNumberOperationApiController::class,
 *              "path"="/bible_verses/verses",
 *              "swagger_context" = {
 *                  "parameters"={
 *                      {
 *                          "name"="book_id",
 *                          "in"="query",
 *                          "description"="Book id",
 *                          "required"="true",
 *                          "type"="integer"
 *                      },
 *                      {
 *                          "name"="chapter",
 *                          "in"="query",
 *                          "description"="Chapter number",
 *                          "required"="true",
 *                          "type"="integer"
 *                      }
 *                  }
 *              }
 *          },
 *          "read"={
 *              "method"="GET",
 *              "controller"=VerseReadOperationApiController::class,
 *              "path"="/bible_verses/read",
 *              "swagger_context" = {
 *                  "parameters"={
 *                      {
 *                          "name"="version",
 *                          "in"="query",
 *                          "description"="Bible version",
 *                          "required"="true",
 *                          "type"="integer"
 *                      },
 *                      {
 *                          "name"="book",
 *                          "in"="query",
 *                          "description"="Book id",
 *                          "required"="true",
 *                          "type"="integer"
 *                      },
 *                      {
 *                          "name"="chapter",
 *                          "in"="query",
 *                          "description"="Chapter number",
 *                          "required"="true",
 *                          "type"="integer"
 *                      }
 *                      ,
 *                      {
 *                          "name"="verse",
 *                          "in"="query",
 *                          "description"="Verse number",
 *                          "required"="true",
 *                          "type"="integer"
 *                      }
 *                  }
 *              }
 *          }
 *     },
 *     itemOperations={
 *          "get"
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
     * @ORM\ManyToOne(targetEntity="App\Entity\BibleBook")
     * @ORM\JoinColumn(nullable=false)
     */
    private $book;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $chapter;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $verse;

    /**
     * @var string
     * @ORM\Column(type="string", length=16)
     */
    private $reference;

    /**
     * @var string
     * @ORM\Column(type="string", length=16)
     */
    private $localReference;

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

    public function getVerse(): ?int
    {
        return $this->verse;
    }

    public function setVerse(int $verse): self
    {
        $this->verse = $verse;

        return $this;
    }

    public function getChapter(): ?int
    {
        return $this->chapter;
    }

    public function setChapter(int $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getLocalReference(): ?string
    {
        return $this->localReference;
    }

    public function setLocalReference(string $localReference): self
    {
        $this->localReference = $localReference;

        return $this;
    }

    public function getBook(): ?BibleBook
    {
        return $this->book;
    }

    public function setBook(?BibleBook $book): self
    {
        $this->book = $book;

        return $this;
    }
}
