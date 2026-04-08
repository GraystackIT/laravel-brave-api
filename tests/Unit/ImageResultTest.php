<?php

declare(strict_types=1);

use Graystack\BraveSearch\Data\ImageResult;

it('builds from a full Brave API result item', function () {
    $result = ImageResult::fromArray([
        'url'       => 'https://example.com/img.jpg',
        'thumbnail' => ['src' => 'https://example.com/thumb.jpg'],
        'title'     => 'A nice image',
        'source'    => 'example.com',
    ]);

    expect($result->url)->toBe('https://example.com/img.jpg')
        ->and($result->thumbnailUrl)->toBe('https://example.com/thumb.jpg')
        ->and($result->title)->toBe('A nice image')
        ->and($result->sourceDomain)->toBe('example.com');
});

it('falls back to parsing host from url when source is missing', function () {
    $result = ImageResult::fromArray([
        'url'       => 'https://photos.example.org/img.jpg',
        'thumbnail' => ['src' => ''],
        'title'     => '',
    ]);

    expect($result->sourceDomain)->toBe('photos.example.org');
});

it('handles empty array gracefully', function () {
    $result = ImageResult::fromArray([]);

    expect($result->url)->toBe('')
        ->and($result->thumbnailUrl)->toBe('')
        ->and($result->title)->toBe('')
        ->and($result->sourceDomain)->toBe('');
});

it('serialises to array', function () {
    $result = new ImageResult(
        url: 'https://example.com/img.jpg',
        thumbnailUrl: 'https://example.com/thumb.jpg',
        title: 'Test',
        sourceDomain: 'example.com',
    );

    expect($result->toArray())->toBe([
        'url'          => 'https://example.com/img.jpg',
        'thumbnailUrl' => 'https://example.com/thumb.jpg',
        'title'        => 'Test',
        'sourceDomain' => 'example.com',
    ]);
});
