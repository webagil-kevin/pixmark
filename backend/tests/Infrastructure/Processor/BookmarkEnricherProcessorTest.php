<?php

namespace App\Tests\Infrastructure\Processor;

use ApiPlatform\Metadata\Operation;
use App\Domain\Bookmark\Entity\Bookmark;
use App\Domain\Embed\EmbedProviderInterface;
use App\Infrastructure\Processor\BookmarkEnricherProcessor;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BookmarkEnricherProcessorTest extends TestCase
{
    private $entityManager;
    private $embedProvider;
    private $processor;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->embedProvider = $this->createMock(EmbedProviderInterface::class);
        $this->processor = new BookmarkEnricherProcessor($this->entityManager, $this->embedProvider);
    }

    public function testProcessThrowsExceptionForNonBookmarkInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->processor->process('not a bookmark', $this->createMock(Operation::class));
    }

    public function testProcessEnrichesBookmarkWithEmbedInfo()
    {
        $bookmark = new Bookmark();
        $bookmark->setUrl('http://example.com');

        $embedInfo = [
            'title' => 'Example Title',
            'author' => 'Example Author',
            'metadata' => ['key' => 'value'],
        ];

        $this->embedProvider->method('getEmbedInfo')->willReturn($embedInfo);
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $operation = $this->createMock(Operation::class);

        $result = $this->processor->process($bookmark, $operation);

        $this->assertSame('Example Title', $result->getTitle());
        $this->assertSame('Example Author', $result->getAuthor());
        $this->assertSame(['key' => 'value'], $result->getMetadata());
    }

    public function testProcessDoesNotEnrichBookmarkWithoutUrl()
    {
        $bookmark = new Bookmark();
        $bookmark->setUrl('');

        $this->embedProvider->expects($this->never())->method('getEmbedInfo');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $operation = $this->createMock(Operation::class);

        $result = $this->processor->process($bookmark, $operation);

        $this->assertNull($result->getTitle());
        $this->assertNull($result->getAuthor());
        $this->assertEmpty($result->getMetadata());
    }

    public function testProcessDoesNotEnrichBookmarkWithAllFieldsFilled()
    {
        $bookmark = new Bookmark();
        $bookmark->setUrl('http://example.com');
        $bookmark->setTitle('Existing Title');
        $bookmark->setAuthor('Existing Author');
        $bookmark->setMetadata(['existing' => 'metadata']);

        $this->embedProvider->expects($this->never())->method('getEmbedInfo');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $operation = $this->createMock(Operation::class);

        $result = $this->processor->process($bookmark, $operation);

        $this->assertSame('Existing Title', $result->getTitle());
        $this->assertSame('Existing Author', $result->getAuthor());
        $this->assertSame(['existing' => 'metadata'], $result->getMetadata());
    }

    public function testProcessEnrichesBookmarkWithMissingTitle()
    {
        $bookmark = new Bookmark();
        $bookmark->setUrl('http://example.com');
        $bookmark->setAuthor('Existing Author');
        $bookmark->setMetadata(['existing' => 'metadata']);

        $embedInfo = [
            'title' => 'New Title',
        ];

        $this->embedProvider->method('getEmbedInfo')->willReturn($embedInfo);
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $operation = $this->createMock(Operation::class);

        $result = $this->processor->process($bookmark, $operation);

        $this->assertSame('New Title', $result->getTitle());
        $this->assertSame('Existing Author', $result->getAuthor());
        $this->assertSame(['existing' => 'metadata'], $result->getMetadata());
    }

    public function testProcessEnrichesBookmarkWithMissingAuthor()
    {
        $bookmark = new Bookmark();
        $bookmark->setUrl('http://example.com');
        $bookmark->setTitle('Existing Title');
        $bookmark->setMetadata(['existing' => 'metadata']);

        $embedInfo = [
            'author' => 'New Author',
        ];

        $this->embedProvider->method('getEmbedInfo')->willReturn($embedInfo);
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $operation = $this->createMock(Operation::class);

        $result = $this->processor->process($bookmark, $operation);

        $this->assertSame('Existing Title', $result->getTitle());
        $this->assertSame('New Author', $result->getAuthor());
        $this->assertSame(['existing' => 'metadata'], $result->getMetadata());
    }

    public function testProcessEnrichesBookmarkWithMissingMetadata()
    {
        $bookmark = new Bookmark();
        $bookmark->setUrl('http://example.com');
        $bookmark->setTitle('Existing Title');
        $bookmark->setAuthor('Existing Author');

        $embedInfo = [
            'metadata' => ['new' => 'metadata'],
        ];

        $this->embedProvider->method('getEmbedInfo')->willReturn($embedInfo);
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $operation = $this->createMock(Operation::class);

        $result = $this->processor->process($bookmark, $operation);

        $this->assertSame('Existing Title', $result->getTitle());
        $this->assertSame('Existing Author', $result->getAuthor());
        $this->assertSame(['new' => 'metadata'], $result->getMetadata());
    }
}
