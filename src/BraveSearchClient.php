<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch;

use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use GraystackIT\BraveSearch\Data\ImageResult;
use GraystackIT\BraveSearch\Data\NewsResult;
use GraystackIT\BraveSearch\Data\VideoResult;
use GraystackIT\BraveSearch\Data\WebResult;
use GraystackIT\BraveSearch\Enums\Freshness;
use GraystackIT\BraveSearch\Enums\SafeSearch;
use GraystackIT\BraveSearch\Exceptions\BraveApiException;
use GraystackIT\BraveSearch\Requests\SearchImagesRequest;
use GraystackIT\BraveSearch\Requests\SearchNewsRequest;
use GraystackIT\BraveSearch\Requests\SearchVideosRequest;
use GraystackIT\BraveSearch\Requests\SearchWebRequest;
use Illuminate\Support\Facades\Log;
use Saloon\Exceptions\Request\RequestException;

class BraveSearchClient
{
    public function __construct(private readonly BraveSearchConnector $connector) {}

    /**
     * Search for images using the Brave Search API.
     *
     * @param  string  $query   The search query
     * @param  int     $count   Number of results (max 200)
     * @param  array<string, mixed>  $options  Extra query parameters to override defaults
     * @return ImageResult[]
     *
     * @throws BraveApiException
     */
    public function searchImages(string $query, int $count = 20, array $options = []): array
    {
        Log::info('BraveSearch: searching images', ['query' => $query, 'count' => $count]);

        try {
            $response = $this->connector->send(new SearchImagesRequest($query, $count, $options));
        } catch (RequestException $e) {
            Log::error('BraveSearch: API request failed', [
                'query'  => $query,
                'status' => $e->getResponse()->status(),
                'body'   => substr($e->getResponse()->body(), 0, 500),
            ]);

            throw new BraveApiException(
                "Brave Search API returned HTTP {$e->getResponse()->status()}: {$query}",
                $e->getResponse()->status(),
                $e
            );
        } catch (\Throwable $e) {
            Log::error('BraveSearch: unexpected error', ['query' => $query, 'message' => $e->getMessage()]);

            throw new BraveApiException("Brave Search request failed: {$e->getMessage()}", 0, $e);
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new BraveApiException('Brave Search API returned a non-JSON response.');
        }

        $results = array_map(
            static fn (array $item) => ImageResult::fromArray($item),
            $data['results'] ?? []
        );

        Log::info('BraveSearch: search completed', ['query' => $query, 'results' => count($results)]);

        return $results;
    }

    /**
     * Search the web using the Brave Search API.
     *
     * @param  string      $query       The search query (required, non-empty)
     * @param  int         $count       Number of results (max 20)
     * @param  int         $offset      Pagination offset
     * @param  SafeSearch  $safesearch  Safe search level
     * @param  string      $searchLang  Language code (e.g. 'en')
     * @param  string      $country     Country code (e.g. 'us')
     * @param  Freshness|null  $freshness  Restrict results by recency
     * @param  bool        $spellcheck  Enable spell-check correction
     * @param  array<string, mixed>  $options  Extra query parameters to override defaults
     * @return WebResult[]
     *
     * @throws \InvalidArgumentException
     * @throws BraveApiException
     */
    public function searchWeb(
        string $query,
        int $count = 10,
        int $offset = 0,
        SafeSearch $safesearch = SafeSearch::Moderate,
        string $searchLang = 'en',
        string $country = 'us',
        ?Freshness $freshness = null,
        bool $spellcheck = true,
        array $options = [],
    ): array {
        if (trim($query) === '') {
            throw new \InvalidArgumentException('Search query must not be empty.');
        }

        Log::info('BraveSearch: searching web', ['query' => $query, 'count' => $count]);

        try {
            $response = $this->connector->send(
                new SearchWebRequest($query, $count, $offset, $safesearch, $searchLang, $country, $freshness, $spellcheck, $options)
            );
        } catch (RequestException $e) {
            Log::error('BraveSearch: web search API request failed', [
                'query'  => $query,
                'status' => $e->getResponse()->status(),
                'body'   => substr($e->getResponse()->body(), 0, 500),
            ]);

            throw new BraveApiException(
                "Brave Search API returned HTTP {$e->getResponse()->status()}: {$query}",
                $e->getResponse()->status(),
                $e
            );
        } catch (\Throwable $e) {
            Log::error('BraveSearch: unexpected error in web search', ['query' => $query, 'message' => $e->getMessage()]);

            throw new BraveApiException("Brave web search request failed: {$e->getMessage()}", 0, $e);
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new BraveApiException('Brave Search API returned a non-JSON response.');
        }

        $items   = $data['web']['results'] ?? [];
        $results = array_map(
            static fn (array $item) => WebResult::fromArray($item),
            $items
        );

        Log::info('BraveSearch: web search completed', ['query' => $query, 'results' => count($results)]);

        return $results;
    }

    /**
     * Search for videos using the Brave Search API.
     *
     * @param  string      $query       The search query (required, non-empty)
     * @param  int         $count       Number of results (max 20)
     * @param  int         $offset      Pagination offset
     * @param  SafeSearch  $safesearch  Safe search level
     * @param  string      $searchLang  Language code (e.g. 'en')
     * @param  string      $country     Country code (e.g. 'us')
     * @param  Freshness|null  $freshness  Restrict results by recency
     * @param  array<string, mixed>  $options  Extra query parameters to override defaults
     * @return VideoResult[]
     *
     * @throws \InvalidArgumentException
     * @throws BraveApiException
     */
    public function searchVideos(
        string $query,
        int $count = 10,
        int $offset = 0,
        SafeSearch $safesearch = SafeSearch::Moderate,
        string $searchLang = 'en',
        string $country = 'us',
        ?Freshness $freshness = null,
        array $options = [],
    ): array {
        if (trim($query) === '') {
            throw new \InvalidArgumentException('Search query must not be empty.');
        }

        Log::info('BraveSearch: searching videos', ['query' => $query, 'count' => $count]);

        try {
            $response = $this->connector->send(
                new SearchVideosRequest($query, $count, $offset, $safesearch, $searchLang, $country, $freshness, $options)
            );
        } catch (RequestException $e) {
            Log::error('BraveSearch: video search API request failed', [
                'query'  => $query,
                'status' => $e->getResponse()->status(),
                'body'   => substr($e->getResponse()->body(), 0, 500),
            ]);

            throw new BraveApiException(
                "Brave Search API returned HTTP {$e->getResponse()->status()}: {$query}",
                $e->getResponse()->status(),
                $e
            );
        } catch (\Throwable $e) {
            Log::error('BraveSearch: unexpected error in video search', ['query' => $query, 'message' => $e->getMessage()]);

            throw new BraveApiException("Brave video search request failed: {$e->getMessage()}", 0, $e);
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new BraveApiException('Brave Search API returned a non-JSON response.');
        }

        $results = array_map(
            static fn (array $item) => VideoResult::fromArray($item),
            $data['results'] ?? []
        );

        Log::info('BraveSearch: video search completed', ['query' => $query, 'results' => count($results)]);

        return $results;
    }

    /**
     * Search for news articles using the Brave Search API.
     *
     * @param  string      $query       The search query (required, non-empty)
     * @param  int         $count       Number of results (max 20)
     * @param  int         $offset      Pagination offset
     * @param  SafeSearch  $safesearch  Safe search level
     * @param  string      $searchLang  Language code (e.g. 'en')
     * @param  string      $country     Country code (e.g. 'us')
     * @param  Freshness|null  $freshness  Restrict results by recency
     * @param  bool        $spellcheck  Enable spell-check correction
     * @param  array<string, mixed>  $options  Extra query parameters to override defaults
     * @return NewsResult[]
     *
     * @throws \InvalidArgumentException
     * @throws BraveApiException
     */
    public function searchNews(
        string $query,
        int $count = 10,
        int $offset = 0,
        SafeSearch $safesearch = SafeSearch::Moderate,
        string $searchLang = 'en',
        string $country = 'us',
        ?Freshness $freshness = null,
        bool $spellcheck = true,
        array $options = [],
    ): array {
        if (trim($query) === '') {
            throw new \InvalidArgumentException('Search query must not be empty.');
        }

        Log::info('BraveSearch: searching news', ['query' => $query, 'count' => $count]);

        try {
            $response = $this->connector->send(
                new SearchNewsRequest($query, $count, $offset, $safesearch, $searchLang, $country, $freshness, $spellcheck, $options)
            );
        } catch (RequestException $e) {
            Log::error('BraveSearch: news search API request failed', [
                'query'  => $query,
                'status' => $e->getResponse()->status(),
                'body'   => substr($e->getResponse()->body(), 0, 500),
            ]);

            throw new BraveApiException(
                "Brave Search API returned HTTP {$e->getResponse()->status()}: {$query}",
                $e->getResponse()->status(),
                $e
            );
        } catch (\Throwable $e) {
            Log::error('BraveSearch: unexpected error in news search', ['query' => $query, 'message' => $e->getMessage()]);

            throw new BraveApiException("Brave news search request failed: {$e->getMessage()}", 0, $e);
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new BraveApiException('Brave Search API returned a non-JSON response.');
        }

        $results = array_map(
            static fn (array $item) => NewsResult::fromArray($item),
            $data['results'] ?? []
        );

        Log::info('BraveSearch: news search completed', ['query' => $query, 'results' => count($results)]);

        return $results;
    }
}
