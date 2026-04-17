<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Data;

class VideoResult
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly string $description,
        public readonly string $thumbnailUrl,
        public readonly string $duration,
        public readonly ?int $views,
        public readonly string $creator,
        public readonly string $publisher,
        public readonly string $age,
        public readonly bool $familyFriendly,
    ) {}

    /**
     * Build from a raw Brave Search API video result item.
     *
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        $video = (array) ($item['video'] ?? []);

        return new self(
            title:          (string) ($item['title'] ?? ''),
            url:            (string) ($item['url'] ?? ''),
            description:    (string) ($item['description'] ?? ''),
            thumbnailUrl:   (string) ($item['thumbnail']['src'] ?? $video['thumbnail']['src'] ?? ''),
            duration:       (string) ($video['duration'] ?? ''),
            views:          isset($video['views']) ? (int) $video['views'] : null,
            creator:        (string) ($video['creator'] ?? ''),
            publisher:      (string) ($video['publisher'] ?? ''),
            age:            (string) ($item['age'] ?? $item['page_age'] ?? ''),
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
            'duration'       => $this->duration,
            'views'          => $this->views,
            'creator'        => $this->creator,
            'publisher'      => $this->publisher,
            'age'            => $this->age,
            'familyFriendly' => $this->familyFriendly,
        ];
    }
}
