<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Tests;

use GraystackIT\BraveSearch\BraveSearchServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [BraveSearchServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('brave-search.api_key',  config('brave-search.api_key'));
        $app['config']->set('brave-search.base_url',  config('brave-search.base_url'));
    }
}
