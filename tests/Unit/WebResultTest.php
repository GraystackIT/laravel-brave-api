<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\Data\WebResult;

it('builds from a full Brave API web result item', function (): void {
    $result = WebResult::fromArray([
        'title'           => 'Laravel Documentation',
        'url'             => 'https://laravel.com/docs',
        'description'     => 'Official Laravel documentation.',
        'thumbnail'       => ['src' => 'https://laravel.com/thumb.jpg'],
        'age'             => '1 day ago',
        'language'        => 'en',
        'family_friendly' => true,
        'extra_snippets'  => ['Get started', 'Installation guide'],
    ]);

    expect($result->title)->toBe('Laravel Documentation')
        ->and($result->url)->toBe('https://laravel.com/docs')
        ->and($result->description)->toBe('Official Laravel documentation.')
        ->and($result->thumbnailUrl)->toBe('https://laravel.com/thumb.jpg')
        ->and($result->age)->toBe('1 day ago')
        ->and($result->language)->toBe('en')
        ->and($result->familyFriendly)->toBeTrue()
        ->and($result->extraSnippets)->toBe(['Get started', 'Installation guide']);
});

it('falls back to page_age when age is absent', function (): void {
    $result = WebResult::fromArray([
        'title'     => 'Some page',
        'url'       => 'https://example.com',
        'page_age'  => '2024-01-15T10:00:00',
    ]);

    expect($result->age)->toBe('2024-01-15T10:00:00');
});

it('falls back to thumbnail.url when thumbnail.src is absent', function (): void {
    $result = WebResult::fromArray([
        'url'       => 'https://example.com',
        'thumbnail' => ['url' => 'https://example.com/fallback.jpg'],
    ]);

    expect($result->thumbnailUrl)->toBe('https://example.com/fallback.jpg');
});

it('defaults family_friendly to true when absent', function (): void {
    $result = WebResult::fromArray(['url' => 'https://example.com']);

    expect($result->familyFriendly)->toBeTrue();
});

it('handles empty array gracefully', function (): void {
    $result = WebResult::fromArray([]);

    expect($result->title)->toBe('')
        ->and($result->url)->toBe('')
        ->and($result->description)->toBe('')
        ->and($result->thumbnailUrl)->toBe('')
        ->and($result->age)->toBe('')
        ->and($result->language)->toBe('')
        ->and($result->familyFriendly)->toBeTrue()
        ->and($result->extraSnippets)->toBe([]);
});

it('serialises to array', function (): void {
    $result = new WebResult(
        title:          'Test Page',
        url:            'https://example.com',
        description:    'A test page.',
        thumbnailUrl:   'https://example.com/thumb.jpg',
        age:            '2 hours ago',
        language:       'en',
        familyFriendly: true,
        extraSnippets:  ['snippet'],
    );

    expect($result->toArray())->toBe([
        'title'          => 'Test Page',
        'url'            => 'https://example.com',
        'description'    => 'A test page.',
        'thumbnailUrl'   => 'https://example.com/thumb.jpg',
        'age'            => '2 hours ago',
        'language'       => 'en',
        'familyFriendly' => true,
        'extraSnippets'  => ['snippet'],
    ]);
});
