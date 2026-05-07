<?php

use Daun\BardMutators\NormalizeHeadingLevels;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

function heading(int $level, string $text): array
{
    return [
        'type' => 'heading',
        'attrs' => ['level' => $level],
        'content' => [['type' => 'text', 'text' => $text]],
    ];
}

it('closes a single skip-level gap', function () {
    $value = $this->getTestValue([
        heading(2, 'Two'),
        heading(4, 'Four'),
    ]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual('<h2>Two</h2><h3>Four</h3>');
});

it('closes multiple skip-level gaps in sequence', function () {
    $value = $this->getTestValue([
        heading(2, 'A'),
        heading(4, 'B'),
        heading(5, 'C'),
    ]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual('<h2>A</h2><h3>B</h3><h4>C</h4>');
});

it('leaves a well-formed hierarchy alone', function () {
    $value = $this->getTestValue([
        heading(2, 'A'),
        heading(3, 'B'),
        heading(4, 'C'),
    ]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual('<h2>A</h2><h3>B</h3><h4>C</h4>');
});

it('allows going back up to a shallower level', function () {
    $value = $this->getTestValue([
        heading(2, 'A'),
        heading(3, 'B'),
        heading(2, 'C'),
    ]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual('<h2>A</h2><h3>B</h3><h2>C</h2>');
});

it('fixes a skip after going up', function () {
    $value = $this->getTestValue([
        heading(2, 'A'),
        heading(3, 'B'),
        heading(2, 'C'),
        heading(4, 'D'),
    ]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual(
        '<h2>A</h2><h3>B</h3><h2>C</h2><h3>D</h3>'
    );
});

it('leaves the first heading at whatever level it starts', function () {
    $value = $this->getTestValue([heading(3, 'Deep start')]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual('<h3>Deep start</h3>');
});

it('leaves h1 alone but closes the gap that follows', function () {
    $value = $this->getTestValue([
        heading(1, 'Title'),
        heading(3, 'Section'),
    ]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual('<h1>Title</h1><h2>Section</h2>');
});

it('does nothing for documents without headings', function () {
    $value = $this->getTestValue([[
        'type' => 'paragraph',
        'content' => [['type' => 'text', 'text' => 'Body']],
    ]]);

    Mutator::plugin(NormalizeHeadingLevels::class);

    expect($this->renderTestValue($value))->toEqual('<p>Body</p>');
});
