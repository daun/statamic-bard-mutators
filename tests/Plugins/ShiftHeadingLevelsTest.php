<?php

use Daun\BardMutators\ShiftHeadingLevels;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

function headingNode(int $level, string $text): array
{
    return [
        'type' => 'heading',
        'attrs' => ['level' => $level],
        'content' => [['type' => 'text', 'text' => $text]],
    ];
}

it('shifts every heading by a positive offset', function () {
    $value = $this->getTestValue([
        headingNode(1, 'One'),
        headingNode(2, 'Two'),
        headingNode(3, 'Three'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(shift: 1));

    expect($this->renderTestValue($value))->toEqual(
        '<h2>One</h2><h3>Two</h3><h4>Three</h4>'
    );
});

it('shifts every heading by a negative offset', function () {
    $value = $this->getTestValue([
        headingNode(2, 'Two'),
        headingNode(3, 'Three'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(shift: -1));

    expect($this->renderTestValue($value))->toEqual('<h1>Two</h1><h2>Three</h2>');
});

it('clamps to h6 when shifting past the maximum', function () {
    $value = $this->getTestValue([
        headingNode(5, 'Five'),
        headingNode(6, 'Six'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(shift: 3));

    expect($this->renderTestValue($value))->toEqual('<h6>Five</h6><h6>Six</h6>');
});

it('clamps to a minimum heading level', function () {
    $value = $this->getTestValue([
        headingNode(1, 'One'),
        headingNode(2, 'Two'),
        headingNode(3, 'Three'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(min: 2));

    expect($this->renderTestValue($value))->toEqual(
        '<h2>One</h2><h2>Two</h2><h3>Three</h3>'
    );
});

it('combines shift and min', function () {
    $value = $this->getTestValue([
        headingNode(2, 'Two'),
        headingNode(3, 'Three'),
        headingNode(4, 'Four'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(shift: -2, min: 2));

    expect($this->renderTestValue($value))->toEqual(
        '<h2>Two</h2><h2>Three</h2><h2>Four</h2>'
    );
});

it('shifts the document so the shallowest heading reaches start level', function () {
    $value = $this->getTestValue([
        headingNode(1, 'One'),
        headingNode(2, 'Two'),
        headingNode(3, 'Three'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(start: 2));

    expect($this->renderTestValue($value))->toEqual(
        '<h2>One</h2><h3>Two</h3><h4>Three</h4>'
    );
});

it('promotes deep documents up to the start level', function () {
    $value = $this->getTestValue([
        headingNode(3, 'Three'),
        headingNode(4, 'Four'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(start: 2));

    expect($this->renderTestValue($value))->toEqual('<h2>Three</h2><h3>Four</h3>');
});

it('leaves headings alone when start matches the shallowest heading', function () {
    $value = $this->getTestValue([
        headingNode(2, 'Two'),
        headingNode(4, 'Four'),
    ]);

    Mutator::plugin(new ShiftHeadingLevels(start: 2));

    expect($this->renderTestValue($value))->toEqual('<h2>Two</h2><h4>Four</h4>');
});

it('does nothing when start is set but the document has no headings', function () {
    $value = $this->getTestValue([[
        'type' => 'paragraph',
        'content' => [['type' => 'text', 'text' => 'No headings']],
    ]]);

    Mutator::plugin(new ShiftHeadingLevels(start: 2));

    expect($this->renderTestValue($value))->toEqual('<p>No headings</p>');
});

it('rejects shift and start used together', function () {
    expect(fn () => new ShiftHeadingLevels(shift: 1, start: 2))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects out-of-range min', function () {
    expect(fn () => new ShiftHeadingLevels(min: 7))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects out-of-range start', function () {
    expect(fn () => new ShiftHeadingLevels(start: 0))
        ->toThrow(InvalidArgumentException::class);
});
