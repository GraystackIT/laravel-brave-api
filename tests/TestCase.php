<?php

declare(strict_types=1);

namespace Graystack\BraveSearch\Tests;

use Graystack\BraveSearch\BraveSearchServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [BraveSearchServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('brave-search.api_key', 'test-api-key');
        $app['config']->set('brave-search.base_url', 'https://api.search.brave.com');
    }
}
