<?php

use Daun\BardMutators\RemoveListItemParagraphs;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

it('removes paragraphs inside list items', function () {
    $value = $this->getTestValue([[
        'type' => 'listItem',
        'content' => [[
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => 'Lorem ipsum',
            ]],
        ]],
    ]]);

    expect($this->renderTestValue($value))->toEqual('<li><p>Lorem ipsum</p></li>');

    Mutator::plugin(RemoveListItemParagraphs::class);

    expect($this->renderTestValue($value))->toEqual('<li>Lorem ipsum</li>');
});

it('does not remove other elements inside list items', function () {
    $value = $this->getTestValue([[
        'type' => 'listItem',
        'content' => [[
            'type' => 'heading',
            'attrs' => [
                'level' => 2,
            ],
            'content' => [[
                'type' => 'text',
                'text' => 'Lorem ipsum',
            ]],
        ]],
    ]]);

    Mutator::plugin(RemoveListItemParagraphs::class);

    expect($this->renderTestValue($value))->toEqual('<li><h2>Lorem ipsum</h2></li>');
});

it('does not remove paragraphs with siblings', function () {
    $value = $this->getTestValue([[
        'type' => 'listItem',
        'content' => [[
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => 'Lorem ipsum',
            ]],
        ], [
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => 'Dolor sit amet',
            ]],
        ]],
    ]]);

    Mutator::plugin(RemoveListItemParagraphs::class);

    expect($this->renderTestValue($value))->toEqual('<li><p>Lorem ipsum</p><p>Dolor sit amet</p></li>');
});
