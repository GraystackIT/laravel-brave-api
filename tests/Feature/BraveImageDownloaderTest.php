<?php

declare(strict_types=1);

use Graystack\BraveSearch\BraveImageDownloader;
use Graystack\BraveSearch\Exceptions\BraveApiException;
use Illuminate\Support\Facades\Http;

it('is resolved from the container', function () {
    expect(app(BraveImageDownloader::class))->toBeInstanceOf(BraveImageDownloader::class);
});

it('returns image bytes on success', function () {
    Http::fake(['https://example.com/img.jpg' => Http::response('fake-image-bytes', 200)]);

    $bytes = (new BraveImageDownloader())->download('https://example.com/img.jpg');

    expect($bytes)->toBe('fake-image-bytes');
});

it('throws BraveApiException on non-2xx', function () {
    Http::fake(['https://example.com/img.jpg' => Http::response('Not Found', 404)]);

    expect(fn () => (new BraveImageDownloader())->download('https://example.com/img.jpg'))
        ->toThrow(BraveApiException::class);
});

it('detects JPEG by magic bytes', function () {
    expect((new BraveImageDownloader())->detectMimeType("\xFF\xD8\xFF\xE0data"))->toBe('image/jpeg');
});

it('detects PNG by magic bytes', function () {
    expect((new BraveImageDownloader())->detectMimeType("\x89PNGdata"))->toBe('image/png');
});

it('detects GIF by magic bytes', function () {
    expect((new BraveImageDownloader())->detectMimeType('GIF89a...'))->toBe('image/gif');
});

it('detects WebP by magic bytes', function () {
    expect((new BraveImageDownloader())->detectMimeType('RIFF????WEBP'))->toBe('image/webp');
});

it('returns null for unrecognised format', function () {
    expect((new BraveImageDownloader())->detectMimeType('random data'))->toBeNull();
});
