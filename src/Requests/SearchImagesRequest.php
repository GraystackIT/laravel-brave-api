<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchImagesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $searchQuery,
        private readonly int $count = 20,
        private readonly array $options = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/res/v1/images/search';
    }

    protected function defaultQuery(): array
    {
        return array_merge([
            'q'           => $this->searchQuery,
            'count'       => min($this->count, 100),
            'safesearch'  => 'strict',
            'search_lang' => 'en',
            'country'     => 'us',
            'spellcheck'  => 1,
        ], $this->options);
    }
}
