<?php

use CollectionDiffer\Support\CollectionDiffer;
use CollectionDiffer\Support\DiffResult;

it('can diff collections', function () {
    $differ = new CollectionDiffer([1, 2, 3], [2, 4]);
    /** @var DiffResult<int, int> $result */
    $result = $differ->diff();
    expect()
        ->and($result->unmatchedSource)->values()->toMatchArray([1, 3])
        ->and($result->unmatchedDestination)->values()->toMatchArray([4])
        ->and($result->matched)->values()->toMatchArray([
            [2, 2],
        ])
        ->and($result->matchedSources())->values()->toMatchArray([2])
        ->and($result->matchedDestinations())->values()->toMatchArray([2]);

    $matchedCount = 0;
    $result->handleMatched(function (int $i, int $j) use (&$matchedCount) {
        $matchedCount++;
        expect($i)->toBe($j);
    });
    expect($matchedCount)->toBe(1);

    $unmatchedSourceCount = 0;
    $result->handleUnmatchedSource(function () use (&$unmatchedSourceCount) {
        $unmatchedSourceCount++;
    });
    expect($unmatchedSourceCount)->toBe(2);

    $unmatchedDestinationCount = 0;
    $result->handleUnmatchedDestination(function () use (&$unmatchedDestinationCount) {
        $unmatchedDestinationCount++;
    });
    expect($unmatchedDestinationCount)->toBe(1);
});

it('can use handlers when diffing', function () {
    $matchedCount = 0;
    $unmatchedSourceCount = 0;
    $unmatchedDestinationCount = 0;
    (new CollectionDiffer([1, 2, 3], [2, 4]))
        ->handleMatchedUsing(function (int $i, int $j) use (&$matchedCount) {
            $matchedCount++;
            expect($i)->toBe($j);
        })
        ->handleUnmatchedSourceUsing(function () use (&$unmatchedSourceCount) {
            $unmatchedSourceCount++;
        })
        ->handleUnmatchedDestinationUsing(function () use (&$unmatchedDestinationCount) {
            $unmatchedDestinationCount++;
        })
        ->diff();

    expect()
        ->and($matchedCount)->toBe(1)
        ->and($unmatchedSourceCount)->toBe(2)
        ->and($unmatchedDestinationCount)->toBe(1);
});

it('can use identifiers when diffing', function () {
    $existingData = [
        [
            'id' => 1,
            'name' => 'Anton',
        ],
        [
            'id' => 2,
            'name' => 'Bill',
        ],
    ];
    $newData = [
        [
            'object_id' => 11,
            'contact' => [
                'contact_id' => 2,
                'name' => 'Tom',
            ],
        ],
        [
            'object_id' => 2,
            'contact' => [
                'contact_id' => 3,
                'name' => 'Alex',
            ],
        ],
    ];

    $result = (new CollectionDiffer($existingData, $newData))
        ->identifySourceUsing(fn ($entry) => $entry['id'])
        ->identifyDestinationUsing(fn ($entry) => $entry['contact']['contact_id'])
        //->identifyDestinationUsing('object_id')
        ->diff();

    expect($result)
        ->unmatchedSource->toMatchArrayAsJson([
            [
                'id' => 1,
                'name' => 'Anton',
            ],
        ])
        ->unmatchedDestination->values()->toMatchArrayAsJson([
            [
                'object_id' => 2,
                'contact' => [
                    'contact_id' => 3,
                    'name' => 'Alex',
                ],
            ],
        ])
        ->matched->values()->toMatchArrayAsJson([
            [
                [
                    'id' => 2,
                    'name' => 'Bill',
                ],
                [
                    'object_id' => 11,
                    'contact' => [
                        'contact_id' => 2,
                        'name' => 'Tom',
                    ],
                ],
            ],
        ]);

    $resultUsingString = (new CollectionDiffer($existingData, $newData))
        ->identifySourceUsing('id')
        ->identifyDestinationUsing('contact.contact_id')
        ->diff();
    expect($resultUsingString)
        ->unmatchedSource->toMatchArrayAsJson($resultUsingString->unmatchedSource)
        ->unmatchedDestination->toMatchArrayAsJson($resultUsingString->unmatchedDestination)
        ->matched->toMatchArrayAsJson($resultUsingString->matched);
});
