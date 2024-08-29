# Laravel Collection Differ

Tiny helper to diff collections.

## Installation

1. Add the github repository and the dev dependency in your composer.json like so:
   ```json5
   {
       // ...
       "repositories": [
           {
               "type": "vcs",
               "url": "https://github.com/michiruf/laravel-collection-differ.git"
           }
       ],
       "require-dev": {
           // ...
           "michiruf/laravel-collection-differ": "dev-main",
           // ...
       }
   }
   ```
2. Perform a composer update for the package
   ```shell
   composer update michiruf/laravel-collection-differ
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
    ->diff();
```

Identifier usage example: 
```php
ProductModel::all()
    ->differ(ProductApi::getAll())
    ->identifySourceUsing(fn (ProductModel $model) => $model->id)
    ->identifyDestinationUsing('meta.id') // or: fn (ProductDto $dto) => $dto->meta->id
    ->diff();
```

For additional running examples, have at look at the [tests](tests/Unit/Support/CollectionDifferTest.php).
