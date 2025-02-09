<?php

namespace App\Infrastructure\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Bookmark\Entity\Bookmark;
use App\Domain\Embed\EmbedProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

use function is_array;
use function is_string;
use function sprintf;

/**
 * @implements ProcessorInterface<Bookmark, Bookmark>
 */
class BookmarkEnricherProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmbedProviderInterface $embedProvider,
    ) {
    }

    /**
     * Processes the given Bookmark and performs operations such as deletion or metadata enrichment.
     *
     * @param mixed                $data         The entity to process. Expected instance of Bookmark.
     * @param Operation            $operation    the operation to be performed
     * @param array<string, mixed> $uriVariables variables from the URI (optional)
     * @param array<string, mixed> $context      additional context (optional)
     *
     * @return Bookmark the processed entity
     *
     * @throws InvalidArgumentException if $data is not an instance of Bookmark
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        if (!$data instanceof Bookmark) {
            throw new InvalidArgumentException(sprintf('Expected instance of %s, got %s', Bookmark::class, get_debug_type($data)));
        }

        if ($data->getUrl() && empty($data->getMetadata())) {
            $embedInfo = $this->embedProvider->getEmbedInfo($data->getUrl());

            if (isset($embedInfo['title']) && is_string($embedInfo['title'])) {
                $data->setTitle($embedInfo['title']);
            }

            if (isset($embedInfo['author']) && is_string($embedInfo['author'])) {
                $data->setAuthor($embedInfo['author']);
            }

            $metadata = [];
            if (isset($embedInfo['metadata']) && is_array($embedInfo['metadata'])) {
                /** @var array<string, mixed> $metadata */
                $metadata = array_filter($embedInfo['metadata'], function ($value): bool {
                    return (bool) $value;
                });
            }

            $data->setMetadata($metadata);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
