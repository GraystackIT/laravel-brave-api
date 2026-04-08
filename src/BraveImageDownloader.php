<?php

declare(strict_types=1);

namespace Graystack\BraveSearch;

use Graystack\BraveSearch\Exceptions\BraveApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BraveImageDownloader
{
    /**
     * Download an image from the given URL and return the raw binary content.
     *
     * @throws BraveApiException  If the HTTP request fails or returns a non-2xx status.
     */
    public function download(string $url): string
    {
        Log::info('BraveImageDownloader: downloading image', ['url' => $url]);

        $response = Http::timeout(30)->get($url);

        if ($response->failed()) {
            Log::error('BraveImageDownloader: download failed', [
                'url'    => $url,
                'status' => $response->status(),
            ]);

            throw new BraveApiException(
                "Image download returned HTTP {$response->status()} for URL: {$url}",
                $response->status()
            );
        }

        return $response->body();
    }

    /**
     * Detect the image MIME type from raw binary content.
     * Returns null if the content is not a recognised image format.
     */
    public function detectMimeType(string $content): ?string
    {
        if (strlen($content) < 4) {
            return null;
        }

        $header = substr($content, 0, 4);

        if (str_starts_with($header, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }

        if ($header === "\x89PNG") {
            return 'image/png';
        }

        if (str_starts_with($header, 'GIF8')) {
            return 'image/gif';
        }

        if (strlen($content) >= 12 && str_starts_with($content, 'RIFF') && substr($content, 8, 4) === 'WEBP') {
            return 'image/webp';
        }

        return null;
    }
}
