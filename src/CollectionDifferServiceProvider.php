<?php

namespace CollectionDiffer;

use CollectionDiffer\Support\CollectionDifferMixin;
use Illuminate\Support\Collection;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/*
 * This class is a Package Service Provider
 *
 * More info: https://github.com/spatie/laravel-package-tools
 */

class CollectionDifferServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('collection-differ');
    }

    public function bootingPackage(): void {
        Collection::mixin(new CollectionDifferMixin);
    }
}
