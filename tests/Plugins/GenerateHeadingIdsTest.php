<?php

use Daun\BardMutators\GenerateHeadingIds;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

it('generates an id slug from heading text', function () {
    $value = $this->getTestValue([[
        'type' => 'heading',
        'attrs' => ['level' => 2],
        'content' => [[
            'type' => 'text',
            'text' => 'Lorem Ipsum',
        ]],
    ]]);

    expect($this->renderTestValue($value))->toEqual('<h2>Lorem Ipsum</h2>');

    Mutator::plugin(GenerateHeadingIds::class);

    expect($this->renderTestValue($value))->toEqual('<h2 id="lorem-ipsum">Lorem Ipsum</h2>');
});

it('generates ids for all heading levels by default', function () {
    $value = $this->getTestValue([
        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'One']]],
        ['type' => 'heading', 'attrs' => ['level' => 3], 'content' => [['type' => 'text', 'text' => 'Three']]],
        ['type' => 'heading', 'attrs' => ['level' => 6], 'content' => [['type' => 'text', 'text' => 'Six']]],
    ]);

    Mutator::plugin(GenerateHeadingIds::class);

    expect($this->renderTestValue($value))->toEqual(
        '<h1 id="one">One</h1><h3 id="three">Three</h3><h6 id="six">Six</h6>'
    );
});

it('only generates ids for configured heading levels', function () {
    $value = $this->getTestValue([
        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'One']]],
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Two']]],
        ['type' => 'heading', 'attrs' => ['level' => 3], 'content' => [['type' => 'text', 'text' => 'Three']]],
    ]);

    Mutator::plugin(new GenerateHeadingIds(levels: [2, 3]));

    expect($this->renderTestValue($value))->toEqual(
        '<h1>One</h1><h2 id="two">Two</h2><h3 id="three">Three</h3>'
    );
});

it('prepends a configurable prefix to generated ids', function () {
    $value = $this->getTestValue([[
        'type' => 'heading',
        'attrs' => ['level' => 2],
        'content' => [[
            'type' => 'text',
            'text' => 'Lorem Ipsum',
        ]],
    ]]);

    Mutator::plugin(new GenerateHeadingIds(prefix: 'section-'));

    expect($this->renderTestValue($value))->toEqual('<h2 id="section-lorem-ipsum">Lorem Ipsum</h2>');
});

it('slugifies headings with punctuation and unicode', function () {
    $value = $this->getTestValue([
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Hello, World!']]],
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Über uns']]],
    ]);

    Mutator::plugin(GenerateHeadingIds::class);

    expect($this->renderTestValue($value))->toEqual(
        '<h2 id="hello-world">Hello, World!</h2><h2 id="uber-uns">Über uns</h2>'
    );
});

it('does not generate an id for headings without text content', function () {
    $value = $this->getTestValue([
        ['type' => 'heading', 'attrs' => ['level' => 2]],
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '   ']]],
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'hardBreak']]],
    ]);

    Mutator::plugin(GenerateHeadingIds::class);

    expect($this->renderTestValue($value))->toEqual('<h2></h2><h2>   </h2><h2><br></h2>');
});

it('extracts text from nested marks for the slug', function () {
    $value = $this->getTestValue([[
        'type' => 'heading',
        'attrs' => ['level' => 3],
        'content' => [
            ['type' => 'text', 'text' => 'Pre '],
            ['type' => 'text', 'text' => 'italic ', 'marks' => [['type' => 'italic']]],
            ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'italic'], ['type' => 'bold']]],
            ['type' => 'text', 'text' => ' end', 'marks' => [['type' => 'italic']]],
            ['type' => 'text', 'text' => ' post'],
        ],
    ]]);

    Mutator::plugin(GenerateHeadingIds::class);

    expect($this->renderTestValue($value))->toEqual(
        '<h3 id="pre-italic-bold-end-post">Pre <em>italic <strong>bold</strong> end</em> post</h3>'
    );
});

it('concatenates text content from multiple text nodes', function () {
    $value = $this->getTestValue([[
        'type' => 'heading',
        'attrs' => ['level' => 2],
        'content' => [
            ['type' => 'text', 'text' => 'Hello '],
            ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
            ['type' => 'text', 'text' => ' world'],
        ],
    ]]);

    Mutator::plugin(GenerateHeadingIds::class);

    expect($this->renderTestValue($value))->toEqual('<h2 id="hello-bold-world">Hello <strong>bold</strong> world</h2>');
});
