<?php

declare(strict_types=1);

use GraystackIT\BraveSearch\Data\VideoResult;

it('builds from a full Brave API video result item', function (): void {
    $result = VideoResult::fromArray([
        'title'           => 'Laravel Tutorial',
        'url'             => 'https://www.youtube.com/watch?v=abc123',
        'description'     => 'Learn Laravel from scratch.',
        'thumbnail'       => ['src' => 'https://img.youtube.com/thumb.jpg'],
        'age'             => '3 days ago',
        'family_friendly' => true,
        'video'           => [
            'duration'  => '22:45',
            'views'     => 120000,
            'creator'   => 'Jane Smith',
            'publisher' => 'YouTube',
        ],
    ]);

    expect($result->title)->toBe('Laravel Tutorial')
        ->and($result->url)->toBe('https://www.youtube.com/watch?v=abc123')
        ->and($result->description)->toBe('Learn Laravel from scratch.')
        ->and($result->thumbnailUrl)->toBe('https://img.youtube.com/thumb.jpg')
        ->and($result->duration)->toBe('22:45')
        ->and($result->views)->toBe(120000)
        ->and($result->creator)->toBe('Jane Smith')
        ->and($result->publisher)->toBe('YouTube')
        ->and($result->age)->toBe('3 days ago')
        ->and($result->familyFriendly)->toBeTrue();
});

it('falls back to video thumbnail when item thumbnail is absent', function (): void {
    $result = VideoResult::fromArray([
        'url'   => 'https://example.com/video',
        'video' => [
            'thumbnail' => ['src' => 'https://example.com/video-thumb.jpg'],
        ],
    ]);

    expect($result->thumbnailUrl)->toBe('https://example.com/video-thumb.jpg');
});

it('sets views to null when absent', function (): void {
    $result = VideoResult::fromArray([
        'url'   => 'https://example.com/video',
        'video' => ['duration' => '5:00'],
    ]);

    expect($result->views)->toBeNull();
});

it('falls back to page_age when age is absent', function (): void {
    $result = VideoResult::fromArray([
        'url'      => 'https://example.com/video',
        'page_age' => '2024-01-10T12:00:00',
    ]);

    expect($result->age)->toBe('2024-01-10T12:00:00');
});

it('handles empty array gracefully', function (): void {
    $result = VideoResult::fromArray([]);

    expect($result->title)->toBe('')
        ->and($result->url)->toBe('')
        ->and($result->description)->toBe('')
        ->and($result->thumbnailUrl)->toBe('')
        ->and($result->duration)->toBe('')
        ->and($result->views)->toBeNull()
        ->and($result->creator)->toBe('')
        ->and($result->publisher)->toBe('')
        ->and($result->age)->toBe('')
        ->and($result->familyFriendly)->toBeTrue();
});

it('serialises to array', function (): void {
    $result = new VideoResult(
        title:          'My Video',
        url:            'https://example.com/video',
        description:    'A video.',
        thumbnailUrl:   'https://example.com/thumb.jpg',
        duration:       '10:00',
        views:          500,
        creator:        'Alice',
        publisher:      'Vimeo',
        age:            '1 week ago',
        familyFriendly: true,
    );

    expect($result->toArray())->toBe([
        'title'          => 'My Video',
        'url'            => 'https://example.com/video',
        'description'    => 'A video.',
        'thumbnailUrl'   => 'https://example.com/thumb.jpg',
        'duration'       => '10:00',
        'views'          => 500,
        'creator'        => 'Alice',
        'publisher'      => 'Vimeo',
        'age'            => '1 week ago',
        'familyFriendly' => true,
    ]);
});
