<?php

use CollectionDiffer\Support\CollectionDiffer;
use Illuminate\Support\Collection;

it('can diff using mixin', function () {
    /** @var CollectionDiffer<int, int> $differ */
    $differ = collect([1, 2, 3])->differ([3, 4]);
    expect($differ)->toBeInstanceOf(CollectionDiffer::class)
        ->and($differ->getSource())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(3)
        ->and($differ->getDestination())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
});
