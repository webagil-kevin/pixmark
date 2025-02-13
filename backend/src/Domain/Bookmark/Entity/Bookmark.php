<?php

namespace App\Domain\Bookmark\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Application\Bookmark\DTO\BookmarkInput;
use App\Domain\Bookmark\Validator\Constraints as AppAssert;
use App\Infrastructure\Persistence\Doctrine\BookmarkRepository;
use App\Infrastructure\Processor\BookmarkCompositeProcessor;
use App\Infrastructure\Processor\BookmarkDeleteProcessor;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookmarkRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            input: BookmarkInput::class,
            processor: BookmarkCompositeProcessor::class
        ),
        new Delete(
            processor: BookmarkDeleteProcessor::class
        ),
    ]
)]
class Bookmark
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line property.unusedType
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The URL must not be blank.')]
    #[Assert\Url(message: "The URL '{{ value }}' is not a valid URL.")]
    #[AppAssert\AllowedDomain]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The title must not be blank.')]
    #[Assert\Type('string')]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The author must not be blank.')]
    #[Assert\Type('string')]
    private ?string $author = null;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    #[Assert\NotNull(message: 'Metadata must not be null.')]
    #[Assert\Type('array')]
    private array $metadata = [];

    #[ORM\Column]
    #[Assert\NotNull(message: 'The creation date must be set.')]
    private ?DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (null === $this->createdAt) {
            $this->setCreatedAt(new DateTimeImmutable());
        }
    }
}
