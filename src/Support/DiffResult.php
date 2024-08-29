<?php

namespace CollectionDiffer\Support;

use Illuminate\Support\Collection;

/**
 * @template TSource
 * @template TDestination
 */
class DiffResult
{
    /**
     * @var Collection<TSource>
     */
    public Collection $unmatchedSource;

    /**
     * @var Collection<TDestination>
     */
    public Collection $unmatchedDestination;

    /**
     * @var Collection<array{TSource, TDestination}>
     */
    public Collection $matched;

    /**
     * @param  Collection<TSource>  $unmatchedSource
     * @param  Collection<TDestination>  $unmatchedDestination
     * @param  Collection<array{TSource, TDestination}>  $matched
     */
    public function __construct(Collection $unmatchedSource, Collection $unmatchedDestination, Collection $matched)
    {
        $this->unmatchedSource = $unmatchedSource;
        $this->unmatchedDestination = $unmatchedDestination;
        $this->matched = $matched;
    }

    /**
     * @return Collection<int, TSource>
     */
    public function matchedSources(): Collection
    {
        return $this->matched->map(fn ($entry) => $entry[0]);
    }

    /**
     * @return Collection<int, TDestination>
     */
    public function matchedDestinations(): Collection
    {
        return $this->matched->map(fn ($entry) => $entry[1]);
    }

    /**
     * @param  callable<TSource>  $callback
     */
    public function handleUnmatchedSource(callable $callback): static
    {
        $this->unmatchedSource->each($callback);

        return $this;
    }

    /**
     * @param  callable<TDestination>  $callback
     */
    public function handleUnmatchedDestination(callable $callback): static
    {
        $this->unmatchedDestination->each($callback);

        return $this;
    }

    /**
     * @param  callable<TSource, TDestination>  $callback
     */
    public function handleMatched(callable $callback): static
    {
        $this->matched->each(fn ($entry) => $callback($entry[0], $entry[1]));

        return $this;
    }
}
