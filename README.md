# graystack/laravel-brave-api

A Laravel package for the [Brave Search Images API](https://api.search.brave.com/), built on Saloon 4.

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12

## Installation

```bash
composer require graystack/laravel-brave-api
```

The service provider is auto-discovered by Laravel.

Publish the config file:

```bash
php artisan vendor:publish --tag=brave-search-config
```

Add your API key to `.env`:

```env
BRAVE_API_KEY=your-api-key
```

Get a key at [api.search.brave.com](https://api.search.brave.com/).

---

## Configuration

After publishing, the config file lives at `config/brave-search.php`:

```php
return [
    'api_key'  => env('BRAVE_API_KEY'),
    'base_url' => env('BRAVE_BASE_URL', 'https://api.search.brave.com'),

    'defaults' => [
        'count'       => 20,
        'safesearch'  => 'strict',
        'search_lang' => 'en',
        'country'     => 'us',
    ],
];
```

---

## Usage

### Search for images

Resolve `BraveSearchClient` from the container or inject it via the constructor:

```php
use Graystack\BraveSearch\BraveSearchClient;

class ProductController extends Controller
{
    public function __construct(private BraveSearchClient $brave) {}

    public function search(Request $request)
    {
        $results = $this->brave->searchImages($request->input('q'), count: 20);

        foreach ($results as $result) {
            echo $result->url;          // full-size image URL
            echo $result->thumbnailUrl; // small preview URL
            echo $result->title;        // image title
            echo $result->sourceDomain; // e.g. "example.com"
        }
    }
}
```

#### Override search options per call

```php
$results = $brave->searchImages('running shoes', count: 10, options: [
    'country'     => 'de',
    'search_lang' => 'de',
    'safesearch'  => 'moderate',
]);
```

---

### Download an image

```php
use Graystack\BraveSearch\BraveImageDownloader;
use Graystack\BraveSearch\Exceptions\BraveApiException;

class ImageController extends Controller
{
    public function __construct(private BraveImageDownloader $downloader) {}

    public function download(string $url)
    {
        try {
            $bytes = $this->downloader->download($url);
            $mime  = $this->downloader->detectMimeType($bytes); // "image/jpeg", "image/png", ...

            return response($bytes, 200)->header('Content-Type', $mime ?? 'application/octet-stream');

        } catch (BraveApiException $e) {
            abort(502, 'Image download failed.');
        }
    }
}
```

#### Detect image format

```php
$bytes = $downloader->download($url);

$mime = $downloader->detectMimeType($bytes);
// "image/jpeg" | "image/png" | "image/gif" | "image/webp" | null
```

---

## Exceptions

| Exception | When thrown |
|---|---|
| `BraveApiException` | API returned 4xx/5xx, network failure, or non-JSON response |

---

## Testing

This package uses Saloon's `MockClient` so you can test without making real HTTP calls:

```php
use Graystack\BraveSearch\Connectors\BraveSearchConnector;
use Graystack\BraveSearch\BraveSearchClient;
use Graystack\BraveSearch\Requests\SearchImagesRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$mockClient = new MockClient([
    SearchImagesRequest::class => MockResponse::make([
        'results' => [
            [
                'url'       => 'https://example.com/img.jpg',
                'thumbnail' => ['src' => 'https://example.com/thumb.jpg'],
                'title'     => 'Test',
                'source'    => 'example.com',
            ],
        ],
    ], 200),
]);

$connector = app(BraveSearchConnector::class);
$connector->withMockClient($mockClient);

$results = (new BraveSearchClient($connector))->searchImages('shoes');
```

Run the package test suite:

```bash
composer install
vendor/bin/pest
```

---

## License

MIT