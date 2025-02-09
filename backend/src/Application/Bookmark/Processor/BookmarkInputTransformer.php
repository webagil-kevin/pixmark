<?php

namespace App\Application\Bookmark\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Bookmark\DTO\BookmarkInput;
use App\Domain\Bookmark\Entity\Bookmark;
use InvalidArgumentException;

use function sprintf;

/**
 * @implements ProcessorInterface<mixed, Bookmark>
 */
class BookmarkInputTransformer implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        if ($data instanceof Bookmark) {
            return $data;
        }

        if (!$data instanceof BookmarkInput) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of %s or %s, got %s',
                Bookmark::class,
                BookmarkInput::class,
                get_debug_type($data)
            ));
        }

        if (!$data->url) {
            throw new InvalidArgumentException('Url not defined.');
        }

        $bookmark = new Bookmark();
        $bookmark->setUrl($data->url);

        return $bookmark;
    }
}
