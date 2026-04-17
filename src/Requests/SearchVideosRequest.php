<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Requests;

use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Enums\SafeSearch;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchVideosRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $searchQuery,
        private readonly int $count = 10,
        private readonly int $offset = 0,
        private readonly SafeSearch $safesearch = SafeSearch::Moderate,
        private readonly string $searchLang = 'en',
        private readonly string $country = 'us',
        private readonly ?Freshness $freshness = null,
        private readonly array $options = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/res/v1/videos/search';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'q'           => $this->searchQuery,
            'count'       => min($this->count, 50),
            'offset'      => max(0, $this->offset),
            'safesearch'  => $this->safesearch->value,
            'search_lang' => $this->searchLang,
            'country'     => $this->country,
        ];

        if ($this->freshness !== null) {
            $query['freshness'] = $this->freshness->value;
        }

        return array_merge($query, $this->options);
    }
}
