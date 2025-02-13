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
     * Processes the given data object within the context of the specified operation.
     *
     * This method ensures the provided data is an instance of the Bookmark class.
     * If the Bookmark object has a URL and lacks essential details such as title, author,
     * or metadata, the method retrieves additional information using the embed provider
     * and updates the Bookmark accordingly. It then persists and flushes the updated
     * Bookmark object to the database.
     *
     * @param mixed                $data         The input data to be processed. Expected to be an instance of Bookmark.
     * @param Operation            $operation    the operation context under which the data is being processed
     * @param array<string, mixed> $uriVariables optional URI variables contextualizing the operation
     * @param array<string, mixed> $context      optional additional context for processing the operation
     *
     * @return Bookmark the processed and, if necessary, updated Bookmark object
     *
     * @throws InvalidArgumentException if the provided data is not an instance of Bookmark
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        if (!$data instanceof Bookmark) {
            throw new InvalidArgumentException(sprintf('Expected instance of %s, got %s', Bookmark::class, get_debug_type($data)));
        }

        if ($data->getUrl()
            && (
                empty($data->getTitle())
                || empty($data->getAuthor())
                || empty($data->getMetadata())
            )
        ) {
            $embedInfo = $this->embedProvider->getEmbedInfo($data->getUrl());

            if (isset($embedInfo['title']) && is_string($embedInfo['title'])) {
                $data->setTitle($embedInfo['title']);
            }

            if (isset($embedInfo['author']) && is_string($embedInfo['author'])) {
                $data->setAuthor($embedInfo['author']);
            }

            if (isset($embedInfo['metadata']) && is_array($embedInfo['metadata'])) {
                /** @var array<string, mixed> $metadata */
                $metadata = array_filter($embedInfo['metadata'], function ($value): bool {
                    return (bool) $value;
                });

                $data->setMetadata($metadata);
            }

            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}
