<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\Data\NewsResult;

it('builds from a full Brave API news result item', function (): void {
    $result = NewsResult::fromArray([
        'title'           => 'Laravel 12 Released',
        'url'             => 'https://laravel-news.com/laravel-12',
        'description'     => 'Laravel 12 brings many improvements.',
        'thumbnail'       => ['src' => 'https://laravel-news.com/thumb.jpg'],
        'age'             => '5 hours ago',
        'breaking'        => false,
        'family_friendly' => true,
        'source'          => [
            'name' => 'Laravel News',
            'url'  => 'https://laravel-news.com',
        ],
    ]);

    expect($result->title)->toBe('Laravel 12 Released')
        ->and($result->url)->toBe('https://laravel-news.com/laravel-12')
        ->and($result->description)->toBe('Laravel 12 brings many improvements.')
        ->and($result->thumbnailUrl)->toBe('https://laravel-news.com/thumb.jpg')
        ->and($result->age)->toBe('5 hours ago')
        ->and($result->sourceName)->toBe('Laravel News')
        ->and($result->sourceUrl)->toBe('https://laravel-news.com')
        ->and($result->breaking)->toBeFalse()
        ->and($result->familyFriendly)->toBeTrue();
});

it('handles string source when source is a plain string', function (): void {
    $result = NewsResult::fromArray([
        'url'    => 'https://example.com/article',
        'source' => 'Example News',
    ]);

    expect($result->sourceName)->toBe('Example News')
        ->and($result->sourceUrl)->toBe('');
});

it('falls back to page_age when age is absent', function (): void {
    $result = NewsResult::fromArray([
        'url'      => 'https://example.com/article',
        'page_age' => '2024-03-01T08:00:00',
    ]);

    expect($result->age)->toBe('2024-03-01T08:00:00');
});

it('falls back to thumbnail.url when thumbnail.src is absent', function (): void {
    $result = NewsResult::fromArray([
        'url'       => 'https://example.com/article',
        'thumbnail' => ['url' => 'https://example.com/fallback.jpg'],
    ]);

    expect($result->thumbnailUrl)->toBe('https://example.com/fallback.jpg');
});

it('defaults breaking to false when absent', function (): void {
    $result = NewsResult::fromArray(['url' => 'https://example.com']);

    expect($result->breaking)->toBeFalse();
});

it('handles empty array gracefully', function (): void {
    $result = NewsResult::fromArray([]);

    expect($result->title)->toBe('')
        ->and($result->url)->toBe('')
        ->and($result->description)->toBe('')
        ->and($result->thumbnailUrl)->toBe('')
        ->and($result->age)->toBe('')
        ->and($result->sourceName)->toBe('')
        ->and($result->sourceUrl)->toBe('')
        ->and($result->breaking)->toBeFalse()
        ->and($result->familyFriendly)->toBeTrue();
});

it('serialises to array', function (): void {
    $result = new NewsResult(
        title:          'Breaking News',
        url:            'https://news.example.com/story',
        description:    'A news story.',
        thumbnailUrl:   'https://news.example.com/thumb.jpg',
        age:            '30 minutes ago',
        sourceName:     'Example News',
        sourceUrl:      'https://news.example.com',
        breaking:       true,
        familyFriendly: true,
    );

    expect($result->toArray())->toBe([
        'title'          => 'Breaking News',
        'url'            => 'https://news.example.com/story',
        'description'    => 'A news story.',
        'thumbnailUrl'   => 'https://news.example.com/thumb.jpg',
        'age'            => '30 minutes ago',
        'sourceName'     => 'Example News',
        'sourceUrl'      => 'https://news.example.com',
        'breaking'       => true,
        'familyFriendly' => true,
    ]);
});
