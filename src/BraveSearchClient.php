<?php

declare(strict_types=1);

namespace Graystack\BraveSearch;

use Graystack\BraveSearch\Connectors\BraveSearchConnector;
use Graystack\BraveSearch\Data\ImageResult;
use Graystack\BraveSearch\Exceptions\BraveApiException;
use Graystack\BraveSearch\Requests\SearchImagesRequest;
use Illuminate\Support\Facades\Log;
use Saloon\Exceptions\Request\RequestException;

class BraveSearchClient
{
    public function __construct(private readonly BraveSearchConnector $connector) {}

    /**
     * Search for images using the Brave Search API.
     *
     * @param  string  $query   The search query
     * @param  int     $count   Number of results (max 100)
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
}
