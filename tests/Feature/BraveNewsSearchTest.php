<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\BraveSearchClient;
use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use GraystackIT\BraveSearch\Data\NewsResult;
use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Exceptions\BraveApiException;
use GraystackIT\BraveSearch\Requests\SearchNewsRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('is resolved from the container for news search', function (): void {
    expect(app(BraveSearchClient::class))->toBeInstanceOf(BraveSearchClient::class);
});

it('returns NewsResult array on successful news search', function (): void {
    $mockClient = new MockClient([
        SearchNewsRequest::class => MockResponse::make([
            'results' => [
                [
                    'title'           => 'Laravel 12 Released',
                    'url'             => 'https://laravel-news.com/laravel-12',
                    'description'     => 'Laravel 12 is here with exciting new features.',
                    'thumbnail'       => ['src' => 'https://laravel-news.com/thumb.jpg'],
                    'age'             => '3 hours ago',
                    'breaking'        => false,
                    'family_friendly' => true,
                    'source'          => [
                        'name' => 'Laravel News',
                        'url'  => 'https://laravel-news.com',
                    ],
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchNews('laravel 12');

    expect($results)->toHaveCount(1)
        ->and($results[0])->toBeInstanceOf(NewsResult::class)
        ->and($results[0]->title)->toBe('Laravel 12 Released')
        ->and($results[0]->url)->toBe('https://laravel-news.com/laravel-12')
        ->and($results[0]->description)->toBe('Laravel 12 is here with exciting new features.')
        ->and($results[0]->thumbnailUrl)->toBe('https://laravel-news.com/thumb.jpg')
        ->and($results[0]->age)->toBe('3 hours ago')
        ->and($results[0]->sourceName)->toBe('Laravel News')
        ->and($results[0]->sourceUrl)->toBe('https://laravel-news.com')
        ->and($results[0]->breaking)->toBeFalse()
        ->and($results[0]->familyFriendly)->toBeTrue();
});

it('returns empty array when results key is absent for news search', function (): void {
    $mockClient = new MockClient([
        SearchNewsRequest::class => MockResponse::make(['query' => 'laravel'], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchNews('laravel');

    expect($results)->toBe([]);
});

it('throws BraveApiException on 401 for news search', function (): void {
    $mockClient = new MockClient([
        SearchNewsRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new BraveSearchClient($connector))->searchNews('laravel'))
        ->toThrow(BraveApiException::class);
});

it('throws InvalidArgumentException for empty query in news search', function (): void {
    $client = new BraveSearchClient(app(BraveSearchConnector::class));

    expect(fn () => $client->searchNews(''))
        ->toThrow(\InvalidArgumentException::class);
});

it('caps count at 50 for news search', function (): void {
    $mockClient = new MockClient([
        SearchNewsRequest::class => MockResponse::make(['results' => []], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchNews('laravel', count: 999);

    $lastRequest = $mockClient->getLastRequest();
    expect($lastRequest)->toBeInstanceOf(SearchNewsRequest::class);

    $reflection = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query      = $reflection->invoke($lastRequest);

    expect((int) $query['count'])->toBe(50);
});

it('passes freshness to the news request', function (): void {
    $mockClient = new MockClient([
        SearchNewsRequest::class => MockResponse::make(['results' => []], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchNews('laravel', freshness: Freshness::PastDay);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['freshness'])->toBe('pd');
});

it('handles breaking news flag', function (): void {
    $mockClient = new MockClient([
        SearchNewsRequest::class => MockResponse::make([
            'results' => [
                [
                    'title'    => 'Breaking: Major Update',
                    'url'      => 'https://news.example.com/breaking',
                    'breaking' => true,
                    'source'   => ['name' => 'Example News', 'url' => 'https://news.example.com'],
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchNews('breaking news');

    expect($results[0]->breaking)->toBeTrue();
});
