<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Data;

class WebResult
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly string $description,
        public readonly string $thumbnailUrl,
        public readonly string $age,
        public readonly string $language,
        public readonly bool $familyFriendly,
        public readonly array $extraSnippets,
    ) {}

    /**
     * Build from a raw Brave Search API web result item.
     *
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        return new self(
            title:          (string) ($item['title'] ?? ''),
            url:            (string) ($item['url'] ?? ''),
            description:    (string) ($item['description'] ?? ''),
            thumbnailUrl:   (string) ($item['thumbnail']['src'] ?? $item['thumbnail']['url'] ?? ''),
            age:            (string) ($item['age'] ?? $item['page_age'] ?? ''),
            language:       (string) ($item['language'] ?? ''),
            familyFriendly: (bool) ($item['family_friendly'] ?? true),
            extraSnippets:  (array) ($item['extra_snippets'] ?? []),
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
            'language'       => $this->language,
            'familyFriendly' => $this->familyFriendly,
            'extraSnippets'  => $this->extraSnippets,
        ];
    }
}
