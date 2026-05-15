<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\BraveSearchClient;
use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use GraystackIT\BraveSearch\Data\SpellcheckInfo;
use GraystackIT\BraveSearch\Data\RichResultHint;
use GraystackIT\BraveSearch\Data\WebResult;
use GraystackIT\BraveSearch\Data\WebSearchResponse;
use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Enums\SafeSearch;
use GraystackIT\BraveSearch\Exceptions\BraveApiException;
use GraystackIT\BraveSearch\Requests\SearchWebRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('is resolved from the container', function (): void {
    expect(app(BraveSearchClient::class))->toBeInstanceOf(BraveSearchClient::class);
});

it('returns WebSearchResponse with results on successful web search', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make([
            'web' => [
                'results' => [
                    [
                        'title'           => 'Example Page',
                        'url'             => 'https://example.com',
                        'description'     => 'An example website.',
                        'thumbnail'       => ['src' => 'https://example.com/thumb.jpg'],
                        'age'             => '2 days ago',
                        'language'        => 'en',
                        'family_friendly' => true,
                        'extra_snippets'  => ['Snippet one', 'Snippet two'],
                    ],
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $response = (new BraveSearchClient($connector))->searchWeb('laravel tutorial');

    expect($response)->toBeInstanceOf(WebSearchResponse::class)
        ->and($response->results)->toHaveCount(1)
        ->and($response->results[0])->toBeInstanceOf(WebResult::class)
        ->and($response->results[0]->title)->toBe('Example Page')
        ->and($response->results[0]->url)->toBe('https://example.com')
        ->and($response->results[0]->description)->toBe('An example website.')
        ->and($response->results[0]->thumbnailUrl)->toBe('https://example.com/thumb.jpg')
        ->and($response->results[0]->age)->toBe('2 days ago')
        ->and($response->results[0]->language)->toBe('en')
        ->and($response->results[0]->familyFriendly)->toBeTrue()
        ->and($response->results[0]->extraSnippets)->toHaveCount(2);
});

it('returns empty results when web.results key is absent', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['query' => 'laravel'], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $response = (new BraveSearchClient($connector))->searchWeb('laravel');

    expect($response)->toBeInstanceOf(WebSearchResponse::class)
        ->and($response->results)->toBe([]);
});

it('populates spellcheck info when present in response', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make([
            'web'        => ['results' => []],
            'spellcheck' => [
                'changed'   => true,
                'original'  => 'larval',
                'corrected' => 'laravel',
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $response = (new BraveSearchClient($connector))->searchWeb('larval');

    expect($response->spellcheck)->toBeInstanceOf(SpellcheckInfo::class)
        ->and($response->spellcheck->changed)->toBeTrue()
        ->and($response->spellcheck->original)->toBe('larval')
        ->and($response->spellcheck->corrected)->toBe('laravel');
});

it('returns null spellcheck when absent from response', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $response = (new BraveSearchClient($connector))->searchWeb('laravel');

    expect($response->spellcheck)->toBeNull();
});

it('populates locations when present in response', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make([
            'web'       => ['results' => []],
            'locations' => [
                'results' => [
                    ['id' => 'abc123', 'title' => 'Pizza Place'],
                    ['id' => 'def456', 'title' => 'Coffee Shop'],
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $response = (new BraveSearchClient($connector))->searchWeb('pizza near me');

    expect($response->locations)->toHaveCount(2)
        ->and($response->locations[0]['id'])->toBe('abc123')
        ->and($response->locations[0]['title'])->toBe('Pizza Place');
});

it('populates rich hint when present in response', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make([
            'web'  => ['results' => []],
            'rich' => [
                'type' => 'rich',
                'hint' => [
                    'vertical'     => 'weather',
                    'callback_key' => 'weather_key_abc',
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $response = (new BraveSearchClient($connector))->searchWeb('weather today');

    expect($response->rich)->toBeInstanceOf(RichResultHint::class)
        ->and($response->rich->vertical)->toBe('weather')
        ->and($response->rich->callbackKey)->toBe('weather_key_abc');
});

it('returns null rich hint when absent from response', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $response = (new BraveSearchClient($connector))->searchWeb('laravel');

    expect($response->rich)->toBeNull();
});

it('throws BraveApiException on 401', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new BraveSearchClient($connector))->searchWeb('laravel'))
        ->toThrow(BraveApiException::class);
});

it('throws BraveApiException on 500', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['error' => 'Server error'], 500),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new BraveSearchClient($connector))->searchWeb('laravel'))
        ->toThrow(BraveApiException::class);
});

it('throws InvalidArgumentException for empty query', function (): void {
    $client = new BraveSearchClient(app(BraveSearchConnector::class));

    expect(fn () => $client->searchWeb(''))
        ->toThrow(\InvalidArgumentException::class);
});

it('caps count at 20', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel', count: 999);

    $lastRequest = $mockClient->getLastRequest();
    expect($lastRequest)->toBeInstanceOf(SearchWebRequest::class);

    $reflection = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query      = $reflection->invoke($lastRequest);

    expect((int) $query['count'])->toBe(20);
});

it('caps offset at 9', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel', offset: 99);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect((int) $query['offset'])->toBe(9);
});

it('passes freshness enum and safesearch to the request', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb(
        query:      'laravel',
        freshness:  Freshness::PastWeek,
        safesearch: SafeSearch::Strict,
    );

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['freshness'])->toBe('pw')
        ->and($query['safesearch'])->toBe('strict');
});

it('accepts a custom freshness date range string', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel', freshness: '2024-01-01to2024-12-31');

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['freshness'])->toBe('2024-01-01to2024-12-31');
});

it('sends extra_snippets param when enabled', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel', extraSnippets: true);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['extra_snippets'])->toBeTrue();
});

it('omits extra_snippets param when disabled', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel');

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query)->not->toHaveKey('extra_snippets');
});

it('sends ui_lang param when set', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel', uiLang: 'en-US');

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['ui_lang'])->toBe('en-US');
});

it('sends goggles_id param when set', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel', gogglesId: 'https://example.com/my.goggle');

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['goggles_id'])->toBe('https://example.com/my.goggle');
});

it('sends enable_rich_callback param when enabled', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['web' => ['results' => []]], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchWeb('laravel', enableRichCallback: true);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['enable_rich_callback'])->toBe(1);
});
