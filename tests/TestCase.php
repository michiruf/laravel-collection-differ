<?php

namespace CollectionDiffer\Tests;

use CollectionDiffer\CollectionDifferServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Workbench\App\Providers\WorkbenchServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CollectionDifferServiceProvider::class,
            WorkbenchServiceProvider::class,
        ];
    }
}
