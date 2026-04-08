<?php

declare(strict_types=1);

namespace Graystack\BraveSearch\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class BraveSearchConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.search.brave.com',
    ) {}

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    protected function defaultHeaders(): array
    {
        return [
            'X-Subscription-Token' => $this->apiKey,
            'Accept-Encoding'      => 'gzip',
        ];
    }
}
