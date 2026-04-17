<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\BraveSearchClient;
use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use GraystackIT\BraveSearch\Data\WebResult;
use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Enums\SafeSearch;
use GraystackIT\BraveSearch\Exceptions\BraveApiException;
use GraystackIT\BraveSearch\Requests\SearchWebRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('is resolved from the container', function (): void {
    expect(app(BraveSearchClient::class))->toBeInstanceOf(BraveSearchClient::class);
});

it('returns WebResult array on successful web search', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make([
            'web' => [
                'results' => [
                    [
                        'title'          => 'Example Page',
                        'url'            => 'https://example.com',
                        'description'    => 'An example website.',
                        'thumbnail'      => ['src' => 'https://example.com/thumb.jpg'],
                        'age'            => '2 days ago',
                        'language'       => 'en',
                        'family_friendly' => true,
                        'extra_snippets' => ['Snippet one', 'Snippet two'],
                    ],
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchWeb('laravel tutorial');

    expect($results)->toHaveCount(1)
        ->and($results[0])->toBeInstanceOf(WebResult::class)
        ->and($results[0]->title)->toBe('Example Page')
        ->and($results[0]->url)->toBe('https://example.com')
        ->and($results[0]->description)->toBe('An example website.')
        ->and($results[0]->thumbnailUrl)->toBe('https://example.com/thumb.jpg')
        ->and($results[0]->age)->toBe('2 days ago')
        ->and($results[0]->language)->toBe('en')
        ->and($results[0]->familyFriendly)->toBeTrue()
        ->and($results[0]->extraSnippets)->toHaveCount(2);
});

it('returns empty array when web.results key is absent', function (): void {
    $mockClient = new MockClient([
        SearchWebRequest::class => MockResponse::make(['query' => 'laravel'], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchWeb('laravel');

    expect($results)->toBe([]);
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

it('passes freshness and safesearch to the request', function (): void {
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
