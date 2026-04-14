<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Data;

class ImageResult
{
    public function __construct(
        public readonly string $url,
        public readonly string $thumbnailUrl,
        public readonly string $title,
        public readonly string $sourceDomain,
    ) {}

    /**
     * Build from a raw Brave Search API result item.
     *
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        return new self(
            url: (string) ($item['url'] ?? $item['image']['url'] ?? ''),
            thumbnailUrl: (string) ($item['thumbnail']['src'] ?? $item['thumbnail']['url'] ?? ''),
            title: (string) ($item['title'] ?? ''),
            sourceDomain: (string) ($item['source'] ?? parse_url((string) ($item['url'] ?? ''), PHP_URL_HOST) ?? ''),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'url'          => $this->url,
            'thumbnailUrl' => $this->thumbnailUrl,
            'title'        => $this->title,
            'sourceDomain' => $this->sourceDomain,
        ];
    }
}
