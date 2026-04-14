<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\BraveSearchClient;
use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use GraystackIT\BraveSearch\Data\ImageResult;
use GraystackIT\BraveSearch\Exceptions\BraveApiException;
use GraystackIT\BraveSearch\Requests\SearchImagesRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('is resolved from the container', function () {
    expect(app(BraveSearchClient::class))->toBeInstanceOf(BraveSearchClient::class);
});

it('returns ImageResult array on successful search', function () {
    $mockClient = new MockClient([
        SearchImagesRequest::class => MockResponse::make([
            'results' => [
                [
                    'url'       => 'https://example.com/img.jpg',
                    'thumbnail' => ['src' => 'https://example.com/thumb.jpg'],
                    'title'     => 'Test Image',
                    'source'    => 'example.com',
                ],
            ],
        ], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $client = new BraveSearchClient($connector);
    $results = $client->searchImages('shoes');

    expect($results)->toHaveCount(1)
        ->and($results[0])->toBeInstanceOf(ImageResult::class)
        ->and($results[0]->url)->toBe('https://example.com/img.jpg')
        ->and($results[0]->thumbnailUrl)->toBe('https://example.com/thumb.jpg')
        ->and($results[0]->title)->toBe('Test Image')
        ->and($results[0]->sourceDomain)->toBe('example.com');
});

it('returns empty array when results key is absent', function () {
    $mockClient = new MockClient([
        SearchImagesRequest::class => MockResponse::make(['query' => 'shoes'], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    $results = (new BraveSearchClient($connector))->searchImages('shoes');

    expect($results)->toBe([]);
});

it('throws BraveApiException on 401', function () {
    $mockClient = new MockClient([
        SearchImagesRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new BraveSearchClient($connector))->searchImages('shoes'))
        ->toThrow(BraveApiException::class);
});

it('caps count at 100', function () {
    $mockClient = new MockClient([
        SearchImagesRequest::class => MockResponse::make(['results' => []], 200),
    ]);

    $connector = app(BraveSearchConnector::class);
    $connector->withMockClient($mockClient);

    (new BraveSearchClient($connector))->searchImages('shoes', 999);

    $mockClient->assertSent(SearchImagesRequest::class);

    $lastRequest = $mockClient->getLastRequest();
    expect($lastRequest)->toBeInstanceOf(SearchImagesRequest::class);

    // Resolve query by sending to a pending request and inspecting defaultQuery
    $reflection = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query = $reflection->invoke($lastRequest);

    expect((int) $query['count'])->toBe(100);
});
