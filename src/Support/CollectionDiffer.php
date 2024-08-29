<?php

namespace CollectionDiffer\Support;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * @template TSource
 * @template TDestination
 */
class CollectionDiffer
{
    /**
     * @var Collection<int, TSource>
     */
    protected Collection $source;

    /**
     * @var Collection<int, TDestination>
     */
    protected Collection $destination;

    /**
     * @var callable<TSource>|string
     */
    protected $identifySourceUsingCallback;

    /**
     * @var callable<TSource>
     */
    protected $handleUnmatchedSourceUsingCallback;

    /**
     * @var callable<TDestination>|string
     */
    protected $identifyDestinationUsingCallback;

    /**
     * @var callable<TDestination>
     */
    protected $handleUnmatchedDestinationUsingCallback;

    /**
     * @var callable<TSource, TDestination>
     */
    protected $handleMatchedUsingCallback;

    public function __construct(Collection|array|null $source, Collection|array|null $destination)
    {
        $this->setSource($source);
        $this->setDestination($destination);
    }

    /**
     * @return Collection<int, TSource>
     */
    public function getSource(): Collection
    {
        return $this->source;
    }

    /**
     * @param  Collection<int, TSource>|TSource[]|null  $source
     * @return static<TSource,TDestination>
     */
    public function setSource(Collection|array|null $source): static
    {
        $this->source = Collection::wrap($source);

        return $this;
    }

    /**
     * @return Collection<int, TDestination>
     */
    public function getDestination(): Collection
    {
        return $this->destination;
    }

    /**
     * @param  Collection<int, TDestination>|TDestination[]|null  $destination
     * @return static<TSource,TDestination>
     */
    public function setDestination(Collection|array|null $destination): static
    {
        $this->destination = Collection::wrap($destination);

        return $this;
    }

    /**
     * @param  callable<TSource>|string|null  $callback
     */
    public function identifySourceUsing(callable|string|null $callback): static
    {
        $this->identifySourceUsingCallback = $callback;

        return $this;
    }

    /**
     * @param  ?callable<TSource>  $callback
     */
    public function handleUnmatchedSourceUsing(?callable $callback): static
    {
        $this->handleUnmatchedSourceUsingCallback = $callback;

        return $this;
    }

    /**
     * @param  callable<TDestination>|string|null  $callback
     */
    public function identifyDestinationUsing(callable|string|null $callback): static
    {
        $this->identifyDestinationUsingCallback = $callback;

        return $this;
    }

    /**
     * @param  ?callable<TDestination>  $callback
     */
    public function handleUnmatchedDestinationUsing(?callable $callback): static
    {
        $this->handleUnmatchedDestinationUsingCallback = $callback;

        return $this;
    }

    /**
     * @param  ?callable<TSource, TDestination>  $callback
     */
    public function handleMatchedUsing(?callable $callback): static
    {
        $this->handleMatchedUsingCallback = $callback;

        return $this;
    }

    public function diff(): DiffResult
    {
        // Patch entries with their identifiers
        $source = $this->source->map(fn ($entry) => [
            'id' => $this->idRetriever($entry, $this->identifySourceUsingCallback),
            'entry' => $entry,
        ]);
        $destination = $this->destination->map(fn ($entry) => [
            'id' => $this->idRetriever($entry, $this->identifyDestinationUsingCallback),
            'entry' => $entry,
        ]);

        // Perform the diff
        $sourceMatches = $source->map(function ($wrappedEntry) use ($destination) {
            $wrappedDestinationMatch = $destination->firstWhere('id', $wrappedEntry['id']);

            return [
                $wrappedEntry['entry'],
                $wrappedDestinationMatch['entry'] ?? null,
            ];
        });
        [$unmatchedSource, $matched] = $sourceMatches->partition(fn ($tupleEntry) => $tupleEntry[1] === null);
        $unmatchedSource = $unmatchedSource->map(fn ($tupleEntry) => $tupleEntry[0]);
        $unmatchedDestination = $destination
            ->filter(fn ($wrappedEntry) => $source->firstWhere('id', $wrappedEntry['id']) === null)
            ->map(fn ($wrappedEntry) => $wrappedEntry['entry']);

        $result = new DiffResult(
            $unmatchedSource,
            $unmatchedDestination,
            $matched,
        );

        if ($this->handleUnmatchedSourceUsingCallback) {
            $result->handleUnmatchedSource($this->handleUnmatchedSourceUsingCallback);
        }

        if ($this->handleUnmatchedDestinationUsingCallback) {
            $result->handleUnmatchedDestination($this->handleUnmatchedDestinationUsingCallback);
        }

        if ($this->handleMatchedUsingCallback) {
            $result->handleMatched($this->handleMatchedUsingCallback);
        }

        return $result;
    }

    protected function idRetriever(mixed $entry, mixed $callback): mixed
    {
        return match (true) {
            $callback === null => $entry,
            is_callable($callback) => $callback($entry),
            is_string($callback) => data_get($entry, $callback),
            default => throw new RuntimeException('Unexpected id retriever type')
        };
    }
}
