<?php

namespace App\Infrastructure\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Bookmark\DTO\BookmarkInput;
use App\Application\Bookmark\Processor\BookmarkInputTransformer;
use App\Domain\Bookmark\Entity\Bookmark;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ProcessorInterface<BookmarkInput, Bookmark>
 */
class CompositeBookmarkProcessor implements ProcessorInterface
{
    public function __construct(
        private BookmarkInputTransformer $inputTransformer,
        private BookmarkEnricherProcessor $enricherProcessor,
    ) {
    }

    /**
     * Processes the given BookmarkInput and returns a Bookmark.
     *
     * @param mixed                $data         The data to process. Expected instance of BookmarkInput.
     * @param Operation            $operation    the operation to be performed
     * @param array<string, mixed> $uriVariables variables from the URI (optional)
     * @param array<string, mixed> $context      Additional context (optional). Expected to have keys:
     *                                           - request?: \Symfony\Component\HttpFoundation\Request,
     *                                           - previous_data?: mixed,
     *                                           - resource_class?: string|null,
     *                                           - original_data?: mixed
     *
     * @return Bookmark the processed entity
     *
     * @throws InvalidArgumentException if $data is not an instance of BookmarkInput
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        /** @var BookmarkInput $data */
        /** @var array{request?: Request, previous_data?: mixed, resource_class?: string|null, original_data?: mixed} $context */
        $entity = $this->inputTransformer->process($data, $operation, $uriVariables, $context);

        return $this->enricherProcessor->process($entity, $operation, $uriVariables, $context);
    }
}
