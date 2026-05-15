<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Requests;

use GraystackIT\BraveSearch\Enums\SafeSearch;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchImagesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $searchQuery,
        private readonly int $count = 20,
        private readonly SafeSearch $safesearch = SafeSearch::Strict,
        private readonly string $searchLang = 'en',
        private readonly string $country = 'us',
        private readonly bool $spellcheck = true,
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
            'count'       => min($this->count, 200),
            'safesearch'  => $this->safesearch->value,
            'search_lang' => $this->searchLang,
            'country'     => $this->country,
            'spellcheck'  => $this->spellcheck ? 1 : 0,
        ], $this->options);
    }
}
