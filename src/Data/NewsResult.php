<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Data;

class NewsResult
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly string $description,
        public readonly string $thumbnailUrl,
        public readonly string $age,
        public readonly string $sourceName,
        public readonly string $sourceUrl,
        public readonly bool $breaking,
        public readonly bool $familyFriendly,
    ) {}

    /**
     * Build from a raw Brave Search API news result item.
     *
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        $source = (array) ($item['source'] ?? []);

        return new self(
            title:          (string) ($item['title'] ?? ''),
            url:            (string) ($item['url'] ?? ''),
            description:    (string) ($item['description'] ?? ''),
            thumbnailUrl:   (string) ($item['thumbnail']['src'] ?? $item['thumbnail']['url'] ?? ''),
            age:            (string) ($item['age'] ?? $item['page_age'] ?? ''),
            sourceName:     (string) ($source['name'] ?? (is_string($item['source'] ?? null) ? $item['source'] : '')),
            sourceUrl:      (string) ($source['url'] ?? ''),
            breaking:       (bool) ($item['breaking'] ?? false),
            familyFriendly: (bool) ($item['family_friendly'] ?? true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title'          => $this->title,
            'url'            => $this->url,
            'description'    => $this->description,
            'thumbnailUrl'   => $this->thumbnailUrl,
            'age'            => $this->age,
            'sourceName'     => $this->sourceName,
            'sourceUrl'      => $this->sourceUrl,
            'breaking'       => $this->breaking,
            'familyFriendly' => $this->familyFriendly,
        ];
    }
}
