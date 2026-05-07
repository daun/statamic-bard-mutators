<?php

use Daun\BardMutators\WrapTables;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

function tableValue()
{
    return test()->getTestValue([[
        'type' => 'table',
        'content' => [[
            'type' => 'tableRow',
            'content' => [[
                'type' => 'tableCell',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'Cell']],
                ]],
            ]],
        ]],
    ]]);
}

it('wraps tables in a div with a default class', function () {
    $value = tableValue();

    expect($this->renderTestValue($value))->toEqual(
        '<table><tbody><tr><td><p>Cell</p></td></tr></tbody></table>'
    );

    Mutator::plugin(WrapTables::class);

    expect($this->renderTestValue($value))->toEqual(
        '<div class="table-wrapper"><table><tbody><tr><td><p>Cell</p></td></tr></tbody></table></div>'
    );
});

it('uses a configurable wrapper tag and class', function () {
    $value = tableValue();

    Mutator::plugin(new WrapTables(tag: 'figure', class: 'table'));

    expect($this->renderTestValue($value))->toEqual(
        '<figure class="table"><table><tbody><tr><td><p>Cell</p></td></tr></tbody></table></figure>'
    );
});

it('preserves the table content when wrapping', function () {
    $value = $this->getTestValue([[
        'type' => 'table',
        'content' => [
            [
                'type' => 'tableRow',
                'content' => [
                    ['type' => 'tableHeader', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'A']]]]],
                    ['type' => 'tableHeader', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'B']]]]],
                ],
            ],
            [
                'type' => 'tableRow',
                'content' => [
                    ['type' => 'tableCell', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '1']]]]],
                    ['type' => 'tableCell', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '2']]]]],
                ],
            ],
        ],
    ]]);

    Mutator::plugin(WrapTables::class);

    expect($this->renderTestValue($value))->toEqual(
        '<div class="table-wrapper"><table><tbody><tr><th><p>A</p></th><th><p>B</p></th></tr><tr><td><p>1</p></td><td><p>2</p></td></tr></tbody></table></div>'
    );
});
