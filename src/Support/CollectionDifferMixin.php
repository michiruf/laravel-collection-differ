<?php

namespace CollectionDiffer\Support;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue
 */
class CollectionDifferMixin
{
    /**
     * @return callable(): CollectionDiffer
     * @noinspection PhpUnused
     */
    public function differ(): callable
    {
        /**
         * Diff the given collections.
         *
         * @param  (callable(TValue): mixed)|string|null  $callback
         * @return CollectionDiffer<TKey, TValue>
         */
        return function (Collection|array|null $destination) {
            return new CollectionDiffer($this->items, $destination);
        };
    }
}
