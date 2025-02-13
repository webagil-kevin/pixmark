<?php

namespace App\Tests\Application\Bookmark\Processor;

use ApiPlatform\Metadata\Operation;
use App\Application\Bookmark\DTO\BookmarkInput;
use App\Application\Bookmark\Processor\BookmarkInputTransformer;
use App\Domain\Bookmark\Entity\Bookmark;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BookmarkInputTransformerTest extends TestCase
{
    private $transformer;

    protected function setUp(): void
    {
        $this->transformer = new BookmarkInputTransformer();
    }

    public function testProcessReturnsBookmarkInstance()
    {
        $bookmark = new Bookmark();
        $operation = $this->createMock(Operation::class);

        $result = $this->transformer->process($bookmark, $operation);

        $this->assertSame($bookmark, $result);
    }

    public function testProcessThrowsExceptionForInvalidData()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->transformer->process('invalid data', $this->createMock(Operation::class));
    }

    public function testProcessThrowsExceptionForUndefinedUrl()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Url not defined.');

        $bookmarkInput = new BookmarkInput();
        $bookmarkInput->url = null;

        $this->transformer->process($bookmarkInput, $this->createMock(Operation::class));
    }

    public function testProcessTransformsBookmarkInputToBookmark()
    {
        $bookmarkInput = new BookmarkInput();
        $bookmarkInput->url = 'http://example.com';

        $operation = $this->createMock(Operation::class);

        $result = $this->transformer->process($bookmarkInput, $operation);

        $this->assertInstanceOf(Bookmark::class, $result);
        $this->assertSame('http://example.com', $result->getUrl());
    }
}
