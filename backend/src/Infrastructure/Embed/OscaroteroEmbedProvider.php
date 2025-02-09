<?php

namespace App\Infrastructure\Embed;

use App\Domain\Embed\EmbedProviderInterface;
use Embed\Embed;

class OscaroteroEmbedProvider implements EmbedProviderInterface
{
    /**
     * @param string[] $metadataFields list of metadata fields to retrieve
     */
    public function __construct(
        private array $metadataFields,
    ) {
    }

    /**
     * Retrieves embed information for a given URL.
     *
     * @param string $url the URL from which to retrieve embed information
     *
     * @return array<string, mixed> an associative array containing embed details such as title, URL, author, and metadata
     */
    public function getEmbedInfo(string $url): array
    {
        $info = (new Embed())->get($url);
        $oembed = $info->getOEmbed();

        $data = [
            'title' => $info->title,
            'url' => $info->url,
            'author' => $info->authorName,
            'metadata' => [],
        ];

        foreach ($this->metadataFields as $field) {
            // Ici, $field est de type string (grâce au type hint et à la docblock)
            $data['metadata'][$field] = $oembed->get($field);
        }

        return $data;
    }
}
