<?php

use CollectionDiffer\Support\CollectionDiffer;
use Illuminate\Support\Collection;

if (! function_exists('differ')) {
    /**
     * @template TSource
     * @template TDestination
     *
     * @param  Collection<TSource>|TSource[]  $source
     * @param  Collection<TDestination>|TDestination[]|null  $destination
     * @return CollectionDiffer<TSource, TDestination>
     */
    function differ(Collection|array $source, Collection|array|null $destination = null)
    {
        return new CollectionDiffer($source, $destination);
    }
}
