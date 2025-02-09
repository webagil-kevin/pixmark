<?php

namespace App\Infrastructure\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Bookmark\Entity\Bookmark;
use Doctrine\ORM\EntityManagerInterface;

use function assert;

/**
 * @implements ProcessorInterface<mixed, Bookmark>
 */
class BookmarkDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        assert($data instanceof Bookmark);

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return $data;
    }
}
