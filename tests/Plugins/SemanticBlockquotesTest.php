<?php

use Daun\BardMutators\SemanticBlockquotes;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

it('wraps blockquotes in a figure', function () {
    $value = $this->getTestValue([[
        'type' => 'blockquote',
        'content' => [[
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => 'Quote',
            ]],
        ]],
    ]]);

    expect($this->renderTestValue($value))->toEqual('<blockquote><p>Quote</p></blockquote>');

    Mutator::plugin(SemanticBlockquotes::class);

    expect($this->renderTestValue($value))->toEqual('<figure><blockquote><p>Quote</p></blockquote></figure>');
});

it('keeps paragraphs in blockquotes', function () {
    $value = $this->getTestValue([[
        'type' => 'blockquote',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => 'Quote',
                ]],
            ],
            [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => 'Quote',
                ]],
            ],
        ],
    ]]);

    Mutator::plugin(SemanticBlockquotes::class);

    expect($this->renderTestValue($value))->toEqual('<figure><blockquote><p>Quote</p><p>Quote</p></blockquote></figure>');
});

it('extracts figcaption from last paragraph if formatted like a source', function () {
    $value = $this->getTestValue([[
        'type' => 'blockquote',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => 'Lorem ipsum',
                ]],
            ],
            [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => 'Dolor sit amet',
                ]],
            ],
            [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => '— Source',
                ]],
            ],
        ],
    ]]);

    Mutator::plugin(SemanticBlockquotes::class);

    expect($this->renderTestValue($value))->toEqual('<figure><blockquote><p>Lorem ipsum</p><p>Dolor sit amet</p></blockquote><figcaption>Source</figcaption></figure>');
});

it('adds a class to figures', function () {
    $value = $this->getTestValue([[
        'type' => 'blockquote',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => 'Quote',
                ]],
            ],
        ],
    ]]);

    Mutator::plugin(new SemanticBlockquotes(class: 'quote'));

    expect($this->renderTestValue($value))->toEqual('<figure class="quote"><blockquote><p>Quote</p></blockquote></figure>');
});
