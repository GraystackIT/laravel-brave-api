# graystackit/laravel-brave-api

A Laravel package for the [Brave Search API](https://api.search.brave.com/), built on [Saloon 4](https://docs.saloon.dev/).

Supports **Web Search**, **Image Search**, **Video Search**, and **News Search**.

## Requirements

- PHP 8.3+
- Laravel 11, 12, or 13

## Installation

```bash
composer require graystackit/laravel-brave-api
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

Resolve `BraveSearchClient` from the container or inject it via the constructor:

```php
use GraystackIT\BraveSearch\BraveSearchClient;

class SearchController extends Controller
{
    public function __construct(private BraveSearchClient $brave) {}
}
```

---

### Web Search

**Endpoint:** `GET /res/v1/web/search`

```php
use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Enums\SafeSearch;

$results = $this->brave->searchWeb('laravel tutorial');

foreach ($results as $result) {
    echo $result->title;          // page title
    echo $result->url;            // page URL
    echo $result->description;    // meta description snippet
    echo $result->thumbnailUrl;   // preview image URL (may be empty)
    echo $result->age;            // e.g. "2 days ago" or ISO datetime
    echo $result->language;       // detected language code
    echo $result->familyFriendly; // bool
    print_r($result->extraSnippets); // array of extra text snippets
}
```

#### Parameters

| Parameter    | Type         | Default             | Description                                  |
|---|---|---|---|
| `$query`     | `string`     | — (required)        | Search query                                 |
| `$count`     | `int`        | `10` (max 20)       | Number of results                            |
| `$offset`    | `int`        | `0`                 | Pagination offset                            |
| `$safesearch`| `SafeSearch` | `SafeSearch::Moderate` | Safe search level                         |
| `$searchLang`| `string`     | `'en'`              | Language code                                |
| `$country`   | `string`     | `'us'`              | Country code                                 |
| `$freshness` | `Freshness\|null` | `null`         | Restrict by recency                          |
| `$spellcheck`| `bool`       | `true`              | Enable spell-check                           |
| `$options`   | `array`      | `[]`                | Extra query parameters (override defaults)   |

#### Filter by recency or safe-search level

```php
use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Enums\SafeSearch;

$results = $brave->searchWeb(
    query:      'php 8.4 features',
    count:      5,
    freshness:  Freshness::PastWeek,
    safesearch: SafeSearch::Strict,
);
```

**`Freshness` enum values:**

| Case            | API value | Meaning       |
|---|---|---|
| `PastDay`       | `pd`      | Past 24 hours |
| `PastWeek`      | `pw`      | Past week     |
| `PastMonth`     | `pm`      | Past month    |
| `PastYear`      | `py`      | Past year     |

**`SafeSearch` enum values:**

| Case       | API value  |
|---|---|
| `Off`      | `off`      |
| `Moderate` | `moderate` |
| `Strict`   | `strict`   |

---

### Video Search

**Endpoint:** `GET /res/v1/videos/search`

```php
$results = $this->brave->searchVideos('laravel tutorial', count: 5);

foreach ($results as $result) {
    echo $result->title;          // video title
    echo $result->url;            // video page URL
    echo $result->description;    // video description
    echo $result->thumbnailUrl;   // thumbnail image URL
    echo $result->duration;       // e.g. "15:32"
    echo $result->views;          // view count (int or null)
    echo $result->creator;        // uploader/creator name
    echo $result->publisher;      // platform (e.g. "YouTube")
    echo $result->age;            // e.g. "1 week ago"
    echo $result->familyFriendly; // bool
}
```

#### Parameters

| Parameter    | Type         | Default             | Description                                |
|---|---|---|---|
| `$query`     | `string`     | — (required)        | Search query                               |
| `$count`     | `int`        | `10` (max 20)       | Number of results                          |
| `$offset`    | `int`        | `0`                 | Pagination offset                          |
| `$safesearch`| `SafeSearch` | `SafeSearch::Moderate` | Safe search level                       |
| `$searchLang`| `string`     | `'en'`              | Language code                              |
| `$country`   | `string`     | `'us'`              | Country code                               |
| `$freshness` | `Freshness\|null` | `null`         | Restrict by recency                        |
| `$options`   | `array`      | `[]`                | Extra query parameters (override defaults) |

```php
$results = $brave->searchVideos(
    query:     'php conference talks',
    freshness: Freshness::PastMonth,
    country:   'gb',
);
```

---

### News Search

**Endpoint:** `GET /res/v1/news/search`

```php
$results = $this->brave->searchNews('laravel 12 release', count: 10);

foreach ($results as $result) {
    echo $result->title;          // article headline
    echo $result->url;            // article URL
    echo $result->description;    // article summary
    echo $result->thumbnailUrl;   // article image URL (may be empty)
    echo $result->age;            // e.g. "3 hours ago"
    echo $result->sourceName;     // e.g. "Laravel News"
    echo $result->sourceUrl;      // source website URL
    echo $result->breaking;       // bool — true for breaking news
    echo $result->familyFriendly; // bool
}
```

#### Parameters

| Parameter    | Type         | Default             | Description                                |
|---|---|---|---|
| `$query`     | `string`     | — (required)        | Search query                               |
| `$count`     | `int`        | `10` (max 20)       | Number of results                          |
| `$offset`    | `int`        | `0`                 | Pagination offset                          |
| `$safesearch`| `SafeSearch` | `SafeSearch::Moderate` | Safe search level                       |
| `$searchLang`| `string`     | `'en'`              | Language code                              |
| `$country`   | `string`     | `'us'`              | Country code                               |
| `$freshness` | `Freshness\|null` | `null`         | Restrict by recency                        |
| `$spellcheck`| `bool`       | `true`              | Enable spell-check                         |
| `$options`   | `array`      | `[]`                | Extra query parameters (override defaults) |

```php
// Latest breaking tech news from the past day
$results = $brave->searchNews(
    query:     'artificial intelligence',
    count:     10,
    freshness: Freshness::PastDay,
    country:   'us',
);
```

---

### Image Search

**Endpoint:** `GET /res/v1/images/search`

```php
$results = $this->brave->searchImages('mountain landscape', count: 20);

foreach ($results as $result) {
    echo $result->url;          // full-size image URL
    echo $result->thumbnailUrl; // small preview URL
    echo $result->title;        // image title
    echo $result->sourceDomain; // e.g. "example.com"
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
use GraystackIT\BraveSearch\BraveImageDownloader;
use GraystackIT\BraveSearch\Exceptions\BraveApiException;

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

---

## Exceptions

| Exception         | When thrown                                                      |
|---|---|
| `BraveApiException`       | API returned 4xx/5xx, network failure, or non-JSON response |
| `InvalidArgumentException`| Empty query string passed to `searchWeb`, `searchVideos`, or `searchNews` |

---

## Testing

This package uses Saloon's `MockClient` so you can test without making real HTTP calls:

```php
use GraystackIT\BraveSearch\BraveSearchClient;
use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use GraystackIT\BraveSearch\Requests\SearchWebRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$mockClient = new MockClient([
    SearchWebRequest::class => MockResponse::make([
        'web' => [
            'results' => [
                [
                    'title'       => 'Example',
                    'url'         => 'https://example.com',
                    'description' => 'An example page.',
                ],
            ],
        ],
    ], 200),
]);

$connector = app(BraveSearchConnector::class);
$connector->withMockClient($mockClient);

$results = (new BraveSearchClient($connector))->searchWeb('example');
```

Run the package test suite:

```bash
composer install
vendor/bin/pest
```

---

## API Endpoints Reference

| Method           | Brave API Endpoint            |
|---|---|
| `searchWeb()`    | `GET /res/v1/web/search`      |
| `searchVideos()` | `GET /res/v1/videos/search`   |
| `searchNews()`   | `GET /res/v1/news/search`     |
| `searchImages()` | `GET /res/v1/images/search`   |

---

## License

MIT
