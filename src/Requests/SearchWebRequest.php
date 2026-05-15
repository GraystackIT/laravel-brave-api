<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Requests;

use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Enums\SafeSearch;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchWebRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $searchQuery,
        private readonly int $count = 10,
        private readonly int $offset = 0,
        private readonly SafeSearch $safesearch = SafeSearch::Moderate,
        private readonly string $searchLang = 'en',
        private readonly string $country = 'us',
        private readonly Freshness|string|null $freshness = null,
        private readonly bool $spellcheck = true,
        private readonly ?string $uiLang = null,
        private readonly bool $extraSnippets = false,
        private readonly ?string $gogglesId = null,
        private readonly bool $enableRichCallback = false,
        private readonly array $options = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/res/v1/web/search';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'q'           => $this->searchQuery,
            'count'       => min($this->count, 20),
            'offset'      => min(9, max(0, $this->offset)),
            'safesearch'  => $this->safesearch->value,
            'search_lang' => $this->searchLang,
            'country'     => $this->country,
            'spellcheck'  => $this->spellcheck ? 1 : 0,
        ];

        if ($this->freshness !== null) {
            $query['freshness'] = $this->freshness instanceof Freshness
                ? $this->freshness->value
                : $this->freshness;
        }

        if ($this->uiLang !== null) {
            $query['ui_lang'] = $this->uiLang;
        }

        if ($this->extraSnippets) {
            $query['extra_snippets'] = true;
        }

        if ($this->gogglesId !== null) {
            $query['goggles_id'] = $this->gogglesId;
        }

        if ($this->enableRichCallback) {
            $query['enable_rich_callback'] = 1;
        }

        return array_merge($query, $this->options);
    }
}
