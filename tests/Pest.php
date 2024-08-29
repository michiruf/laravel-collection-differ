<?php

use CollectionDiffer\Tests\TestCase;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

uses(TestCase::class)->in(__DIR__);
uses(RefreshDatabase::class)->in(__DIR__.'/Unit');

expect()->extend('toMatchArrayAsJson', function ($expected, string $message = '') {
    $value = ($this->value instanceof Arrayable) ? $this->value->toArray() : $this->value;
    $value = Arr::sortRecursive($value);
    $value = json_encode($value, JSON_PRETTY_PRINT);
    $expected = ($expected instanceof Arrayable) ? $expected->toArray() : $expected;
    $expected = Arr::sortRecursive($expected);
    $expected = json_encode($expected, JSON_PRETTY_PRINT);
    expect($value)->toBe($expected, $message);
});
