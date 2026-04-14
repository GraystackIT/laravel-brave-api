<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch;

use GraystackIT\BraveSearch\Connectors\BraveSearchConnector;
use Illuminate\Support\ServiceProvider;

class BraveSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/brave-search.php', 'brave-search');

        $this->app->singleton(BraveSearchConnector::class, function () {
            $apiKey = (string) config('brave-search.api_key', '');

            if (empty($apiKey)) {
                throw new \RuntimeException(
                    'Brave Search API key is not configured. Set BRAVE_API_KEY in your .env file.'
                );
            }

            return new BraveSearchConnector(
                apiKey: $apiKey,
                baseUrl: (string) config('brave-search.base_url', 'https://api.search.brave.com'),
            );
        });

        $this->app->singleton(BraveImageDownloader::class, fn () => new BraveImageDownloader());

        $this->app->singleton(BraveSearchClient::class, fn ($app) => new BraveSearchClient(
            connector: $app->make(BraveSearchConnector::class),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/brave-search.php' => config_path('brave-search.php'),
            ], 'brave-search-config');
        }
    }
}
