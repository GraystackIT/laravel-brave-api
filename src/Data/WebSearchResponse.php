<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Data;

class WebSearchResponse
{
    /**
     * @param  WebResult[]                           $results
     * @param  array<array{id: string, title: string}>  $locations  Raw location IDs from local enrichment
     */
    public function __construct(
        public readonly array $results,
        public readonly ?SpellcheckInfo $spellcheck,
        public readonly array $locations,
        public readonly ?RichResultHint $rich,
    ) {}
}
