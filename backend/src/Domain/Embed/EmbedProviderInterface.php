<?php

namespace App\Domain\Embed;

interface EmbedProviderInterface
{
    /**
     * Retrieves and returns embedded information from a given URL.
     *
     * @param string $url the URL to extract embed information from
     *
     * @return array<string, mixed> an associative array containing the extracted embed information
     */
    public function getEmbedInfo(string $url): array;
}
