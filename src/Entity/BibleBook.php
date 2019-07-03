<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\Api\Operation\BookLocalizedOperationApiController;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "bible_book_localized"={
 *              "method"="GET",
 *              "controller"=BookLocalizedOperationApiController::class,
 *              "path"="/bible_books/localized",
 *              "swagger_context" = {
 *                  "parameters"={
 *                      {
 *                          "name"="language",
 *                          "in"="query",
 *                          "description"="Language parameter (es|en)",
 *                          "required"="true",
 *                          "type"="string"
 *                      }
 *                  }
 *              }
 *          }
 *     },
 *     itemOperations={
 *          "get"
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BibleBookRepository")
 */
class BibleBook
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $canonicalName;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $shortName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     */
    private $language;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCanonicalName(): ?string
    {
        return $this->canonicalName;
    }

    public function setCanonicalName(string $canonicalName): self
    {
        $this->canonicalName = $canonicalName;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }
}
