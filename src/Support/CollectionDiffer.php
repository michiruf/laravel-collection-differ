<?php

namespace CollectionDiffer\Support;

use Illuminate\Support\Collection;
use RuntimeException;
use stdClass;

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
     * @var callable<TSource>|string|null
     */
    protected $identifySourceUsingCallback;

    /**
     * @var callable<TSource>|null
     */
    protected $handleUnmatchedSourceUsingCallback;

    /**
     * @var callable<TDestination>|string|null
     */
    protected $identifyDestinationUsingCallback;

    /**
     * @var callable<TDestination>|null
     */
    protected $handleUnmatchedDestinationUsingCallback;

    /**
     * @var callable<TSource, TDestination>|null
     */
    protected $handleMatchedUsingCallback;

    protected bool $validateUniqueness = false;

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

    public function validateUniqueness(bool $validateUniqueness = true): static
    {
        $this->validateUniqueness = $validateUniqueness;

        return $this;
    }

    /**
     * @return DiffResult<TSource, TDestination>
     */
    public function diff(): DiffResult
    {
        // Patch entries with their identifiers
        $source = $this->source->mapWithKeys(function ($value, $key) {
            $entry = new stdClass;
            $entry->key = $key;
            $entry->value = &$value;

            return [$this->idRetriever($value, $this->identifySourceUsingCallback) => $entry];
        });
        $destination = $this->destination->mapWithKeys(function ($value, $key) {
            $entry = new stdClass;
            $entry->key = $key;
            $entry->value = &$value;

            return [$this->idRetriever($value, $this->identifyDestinationUsingCallback) => $entry];
        });

        // Check uniqueness
        if ($this->validateUniqueness && (
            $this->source->count() != $source->count() ||
            $this->destination->count() != $destination->count()
        )) {
            throw new RuntimeException('Identifiers are not unique');
        }

        // Perform the diff
        [$unmatchedSource, $matched] = $source
            ->each(fn (stdClass $entry, $id) => $entry->destination = $destination->get($id))
            ->partition(fn ($entry, $id) => $entry->destination === null);
        $unmatchedDestination = $destination
            ->filter(fn (stdClass $entry, $id) => ! $source->has($id));

        // Map to the target format
        $unmatchedSource = $unmatchedSource->mapWithKeys(fn (stdClass $entry) => [$entry->key => $entry->value]);
        $unmatchedDestination = $unmatchedDestination->mapWithKeys(fn (stdClass $entry) => [$entry->key => $entry->value]);
        $matched = $matched->mapWithKeys(fn (stdClass $entry) => [$entry->key => [$entry->value, $entry->destination->value]]);

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

    protected function idRetriever(mixed &$entry, mixed $callback): mixed
    {
        return match (true) {
            $callback === null => $entry,
            is_callable($callback) => $callback($entry),
            is_string($callback) => data_get($entry, $callback),
            default => throw new RuntimeException('Unexpected id retriever type')
        };
    }
}
