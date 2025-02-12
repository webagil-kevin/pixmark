<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class containing tests for the Bookmark API.
 * Includes tests for creating, retrieving, and deleting bookmarks with various scenarios.
 *
 * POST with a valid Vimeo URL should succeed
 * POST with a valid Flickr URL should succeed
 * POST with an invalid domain (e.g. "https://john.doe") should return a 422
 * GET the collection should succeed
 * GET an existing bookmark by ID should succeed
 * GET a non-existent bookmark should return a 404
 * DELETE an existing bookmark should succeed
 * DELETE a non-existent bookmark should return a 404
 */
class BookmarkApiTest extends ApiTestCase
{
    private const URL =  [
        'API' => '/back/api/bookmarks',
        'vimeo-ok' => 'https://vimeo.com/900680873',
        'flickr-ok' => 'https://flic.kr/p/2gPAGVq',
        '404' => 'https://john.doe'
    ];
    
    /**
     * Tests creating a bookmark with a valid Vimeo URL.
     */
    public function testCreateBookmarkVimeo(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', self::URL['API'], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'url' => self::URL['vimeo-ok'],
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertNotEmpty($data['title'], 'The title should not be empty for Vimeo.');
        $this->assertNotEmpty($data['author'], 'The author should not be empty for Vimeo.');
        $this->assertNotEmpty($data['metadata'], 'The metadata should not be empty for Vimeo.');
    }

    /**
     * Tests creating a bookmark with a valid Flickr URL.
     */
    public function testCreateBookmarkFlickr(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', self::URL['API'], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'url' => self::URL['flickr-ok'],
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertNotEmpty($data['title'], 'The title should not be empty for Flickr.');
        $this->assertNotEmpty($data['author'], 'The author should not be empty for Flickr.');
        $this->assertNotEmpty($data['metadata'], 'The metadata should not be empty for Flickr.');
    }

    /**
     * Tests creating a bookmark with an invalid domain.
     * This should return a 422 validation error.
     */
    public function testCreateBookmarkInvalidDomain(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', self::URL['API'], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'url' => self::URL['404'],
            ],
        ]);

        // Expect a 422 Unprocessable Entity error.
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        // Update the expected header to "application/problem+json; charset=utf-8" for error responses.
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
    }

    /**
     * Tests retrieving the collection of bookmarks.
     */
    public function testGetBookmarksCollection(): void
    {
        $client = static::createClient();

        // Create a bookmark first to ensure collection isn't empty
        $createResponse = $client->request('POST', self::URL['API'], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'url' => self::URL['vimeo-ok'],
            ],
        ]);

        // Verify the bookmark was created successfully
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Get the collection
        $response = $client->request('GET', self::URL['API'], [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();

        // Verify JSON-LD structure
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertEquals('Collection', $data['@type']);

        // Verify collection metadata
        $this->assertArrayHasKey('totalItems', $data);
        $this->assertIsInt($data['totalItems']);

        // Verify members array
        $this->assertArrayHasKey('member', $data);
        $this->assertIsArray($data['member']);
        $this->assertNotEmpty($data['member']);

        // Verify structure of the first bookmark in the collection
        $firstBookmark = $data['member'][0];
        $this->assertArrayHasKey('@id', $firstBookmark);
        $this->assertArrayHasKey('@type', $firstBookmark);
        $this->assertEquals('Bookmark', $firstBookmark['@type']);
        $this->assertArrayHasKey('id', $firstBookmark);
        $this->assertArrayHasKey('url', $firstBookmark);
        $this->assertArrayHasKey('title', $firstBookmark);
        $this->assertArrayHasKey('author', $firstBookmark);
        $this->assertArrayHasKey('metadata', $firstBookmark);
        $this->assertArrayHasKey('createdAt', $firstBookmark);

        // Verify metadata structure
        $this->assertIsArray($firstBookmark['metadata']);
        $this->assertArrayHasKey('width', $firstBookmark['metadata']);
        $this->assertArrayHasKey('height', $firstBookmark['metadata']);
    }

    /**
     * Tests retrieving an existing bookmark by ID.
     */
    public function testGetBookmarkByIdSuccess(): void
    {
        $client = static::createClient();
        // Create a bookmark to retrieve its ID
        $postResponse = $client->request('POST', self::URL['API'], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'url' => self::URL['vimeo-ok'],
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $bookmark = $postResponse->toArray();
        $id = $bookmark['id'];

        // Retrieve the created bookmark
        $getResponse = $client->request('GET',  self::URL['API'] . "/{$id}", [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $data = $getResponse->toArray();
        $this->assertSame($id, $data['id']);
    }

    /**
     * Tests retrieving a non-existent bookmark.
     */
    public function testGetBookmarkByIdNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', self::URL['API'] . '/99', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Tests deleting an existing bookmark.
     */
    public function testDeleteBookmarkSuccess(): void
    {
        $client = static::createClient();
        // Create a bookmark to delete
        $postResponse = $client->request('POST', self::URL['API'], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'url' => self::URL['vimeo-ok'],
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $bookmark = $postResponse->toArray();
        $id = $bookmark['id'];

        // Delete the bookmark
        $client->request('DELETE', self::URL['API'] . "/{$id}", [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify that the bookmark no longer exists (GET should return 404)
        $client->request('GET', self::URL['API'] . "/{$id}", [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Tests deleting a non-existent bookmark.
     */
    public function testDeleteBookmarkNotFound(): void
    {
        $client = static::createClient();
        $client->request('DELETE', self::URL['API'] . '/99', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
