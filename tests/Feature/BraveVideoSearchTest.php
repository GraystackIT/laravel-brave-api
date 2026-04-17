<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\BraveSearchClient;
use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use GraystackIT\BraveSearch\Data\VideoResult;
use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Exceptions\BraveApiException;
use GraystackIT\BraveSearch\Requests\SearchVideosRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('is resolved from the container for video search', function (): void {
    expect(app(BraveSearchClient::class))->toBeInstanceOf(BraveSearchClient::class);
});

it('returns VideoResult array on successful video search', function (): void {
    $mockClient = new MockClient([
        SearchVideosRequest::class => MockResponse::make([
            'results' => [
                [
                    'title'           => 'Laravel Tutorial',
                    'url'             => 'https://www.youtube.com/watch?v=abc123',
                    'description'     => 'A comprehensive Laravel tutorial.',
                    'thumbnail'       => ['src' => 'https://img.youtube.com/thumb.jpg'],
                    'age'             => '1 week ago',
                    'family_friendly' => true,
                    'video'           => [
                        'duration'  => '15:32',
                        'views'     => 45000,
                        'creator'   => 'John Doe',
                        'publisher' => 'YouTube',
                    ],
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchVideos('laravel tutorial');

    expect($results)->toHaveCount(1)
        ->and($results[0])->toBeInstanceOf(VideoResult::class)
        ->and($results[0]->title)->toBe('Laravel Tutorial')
        ->and($results[0]->url)->toBe('https://www.youtube.com/watch?v=abc123')
        ->and($results[0]->description)->toBe('A comprehensive Laravel tutorial.')
        ->and($results[0]->thumbnailUrl)->toBe('https://img.youtube.com/thumb.jpg')
        ->and($results[0]->duration)->toBe('15:32')
        ->and($results[0]->views)->toBe(45000)
        ->and($results[0]->creator)->toBe('John Doe')
        ->and($results[0]->publisher)->toBe('YouTube')
        ->and($results[0]->age)->toBe('1 week ago')
        ->and($results[0]->familyFriendly)->toBeTrue();
});

it('returns empty array when results key is absent for video search', function (): void {
    $mockClient = new MockClient([
        SearchVideosRequest::class => MockResponse::make(['query' => 'laravel'], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchVideos('laravel');

    expect($results)->toBe([]);
});

it('throws BraveApiException on 401 for video search', function (): void {
    $mockClient = new MockClient([
        SearchVideosRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new BraveSearchClient($connector))->searchVideos('laravel'))
        ->toThrow(BraveApiException::class);
});

it('throws InvalidArgumentException for empty query in video search', function (): void {
    $client = new BraveSearchClient(app(BraveSearchConnector::class));

    expect(fn () => $client->searchVideos(''))
        ->toThrow(\InvalidArgumentException::class);
});

it('caps count at 50 for video search', function (): void {
    $mockClient = new MockClient([
        SearchVideosRequest::class => MockResponse::make(['results' => []], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchVideos('laravel', count: 999);

    $lastRequest = $mockClient->getLastRequest();
    expect($lastRequest)->toBeInstanceOf(SearchVideosRequest::class);

    $reflection = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query      = $reflection->invoke($lastRequest);

    expect((int) $query['count'])->toBe(50);
});

it('passes freshness to the video request', function (): void {
    $mockClient = new MockClient([
        SearchVideosRequest::class => MockResponse::make(['results' => []], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchVideos('laravel', freshness: Freshness::PastMonth);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['freshness'])->toBe('pm');
});
