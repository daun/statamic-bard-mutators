<?php

use Daun\BardMutators\GenerateHeadingIds;
use Daun\BardMutators\InsertHeadingAnchors;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

function anchorHeading(int $level, array $content, ?string $id = null): array
{
    $attrs = ['level' => $level];
    if ($id !== null) {
        $attrs['id'] = $id;
    }

    return ['type' => 'heading', 'attrs' => $attrs, 'content' => $content];
}

function anchorText(string $text): array
{
    return ['type' => 'text', 'text' => $text];
}

it('prepends an anchor link inside the heading by default', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Introduction')])]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(InsertHeadingAnchors::class);

    $html = $this->renderTestValue($value);
    expect($html)->toEqual(
        '<h2 id="introduction"><a href="#introduction" aria-label="Permalink to Introduction"><span aria-hidden="true">#</span></a>Introduction</h2>'
    );
});

it('appends the anchor link when behavior is append', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Introduction')])]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(new InsertHeadingAnchors(behavior: 'append'));

    expect($this->renderTestValue($value))->toEqual(
        '<h2 id="introduction">Introduction<a href="#introduction" aria-label="Permalink to Introduction"><span aria-hidden="true">#</span></a></h2>'
    );
});

it('uses an existing id when present on the heading', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Hello')], id: 'custom')]);

    Mutator::plugin(InsertHeadingAnchors::class);

    expect($this->renderTestValue($value))->toContain('href="#custom"');
});

it('skips headings that have no id', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Introduction')])]);

    Mutator::plugin(InsertHeadingAnchors::class);

    expect($this->renderTestValue($value))->toEqual('<h2>Introduction</h2>');
});

it('honors a prefix configured on GenerateHeadingIds', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Section One')])]);

    Mutator::plugin(new GenerateHeadingIds(prefix: 'sec-'));
    Mutator::plugin(new InsertHeadingAnchors);

    $html = $this->renderTestValue($value);
    expect($html)->toContain('id="sec-section-one"');
    expect($html)->toContain('href="#sec-section-one"');
});

it('substitutes the heading text into the label template', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Hello World')])]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(new InsertHeadingAnchors(label: 'Anchor: {text}'));

    expect($this->renderTestValue($value))->toContain('aria-label="Anchor: Hello World"');
});

it('extracts text across inline marks and nested nodes', function () {
    $value = $this->getTestValue([anchorHeading(2, [
        ['type' => 'text', 'text' => 'Hello '],
        ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
        ['type' => 'text', 'text' => ' world'],
    ])]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(InsertHeadingAnchors::class);

    $html = $this->renderTestValue($value);
    expect($html)->toContain('href="#hello-bold-world"');
    expect($html)->toContain('aria-label="Permalink to Hello bold world"');
    expect($html)->toContain('Hello <strong>bold</strong> world');
});

it('renders raw HTML content like an SVG icon', function () {
    $svg = '<svg viewBox="0 0 16 16"><path d="M0 0"/></svg>';
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Heading')])]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(new InsertHeadingAnchors(icon: $svg));

    expect($this->renderTestValue($value))->toContain(
        '<span aria-hidden="true">'.$svg.'</span>'
    );
});

it('renders an emoji as the icon', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Heading')])]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(new InsertHeadingAnchors(icon: '🔗'));

    expect($this->renderTestValue($value))->toContain('<span aria-hidden="true">🔗</span>');
});

it('adds a custom class to the anchor', function () {
    $value = $this->getTestValue([anchorHeading(2, [anchorText('Heading')])]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(new InsertHeadingAnchors(class: 'heading-anchor'));

    expect($this->renderTestValue($value))->toContain('class="heading-anchor"');
});

it('skips heading levels not in the configured list', function () {
    $value = $this->getTestValue([
        anchorHeading(1, [anchorText('One')]),
        anchorHeading(2, [anchorText('Two')]),
    ]);

    Mutator::plugin(GenerateHeadingIds::class);
    Mutator::plugin(new InsertHeadingAnchors(levels: [2, 3]));

    $html = $this->renderTestValue($value);
    expect($html)->toEqual(
        '<h1 id="one">One</h1><h2 id="two"><a href="#two" aria-label="Permalink to Two"><span aria-hidden="true">#</span></a>Two</h2>'
    );
});

it('rejects an invalid behavior value', function () {
    expect(fn () => new InsertHeadingAnchors(behavior: 'wrap'))
        ->toThrow(InvalidArgumentException::class);
});
