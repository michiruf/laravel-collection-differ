# Laravel Collection Differ

[![Run Tests](https://github.com/michiruf/laravel-collection-differ/actions/workflows/run-tests.yml/badge.svg)](https://github.com/michiruf/laravel-collection-differ/actions/workflows/run-tests.yml)

Tiny helper to diff collections.

## Installation

```shell
composer require michiruf/laravel-collection-differ
```
   
## Usage

Result usage example:
```php
$result = collect([1, 2, 3])->differ([2, 4])->diff();

dump($result->unmatchedSource); // [1, 3]
dump($result->unmatchedDestination); // [4]
dump($result->matched); // [[2, 2]]
```

Callbacks usage example:

```php
ProductModel::all()
    ->differ(ProductApi::getAll())
    ->handleUnmatchedSourceUsing(fn (ProductModel $model) => $model->delete())
    ->handleUnmatchedDestinationUsing(fn (ProductDto) $dto => ProductModel::createFromDto($dto))
    ->handleMatchedUsing(fn (ProductModel $model, ProductDto $dto) => $model->updateWithDto($dto))
    ->validateUniqueness() // throw if identifiers are not unique
    ->diff();
```

Identifier usage example: 
```php
ProductModel::all()
    ->differ(ProductApi::getAll())
    ->identifySourceUsing(fn (ProductModel $model) => $model->id)
    ->identifyDestinationUsing('meta.id') // or: fn (ProductDto $dto) => $dto->meta->id
    ->validateUniqueness() // throw if identifiers are not unique
    ->diff();
```

For additional examples, have at look at the [tests](tests/Unit/Support/CollectionDifferTest.php).
