<?php

use Daun\BardMutators\LazyLoadImages;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

function imageNode(string $src = 'test.jpg', ?string $alt = null): array
{
    $attrs = ['src' => $src];
    if ($alt !== null) {
        $attrs['alt'] = $alt;
    }

    return ['type' => 'image', 'attrs' => $attrs];
}

function paragraphNode(array $content): array
{
    return ['type' => 'paragraph', 'content' => $content];
}

it('adds loading and decoding attributes to images', function () {
    $value = $this->getTestValue([imageNode('a.jpg')]);

    expect($this->renderTestValue($value))->toEqual('<img src="a.jpg">');

    Mutator::plugin(LazyLoadImages::class);

    expect($this->renderTestValue($value))->toEqual(
        '<img src="a.jpg" loading="lazy" decoding="async">'
    );
});

it('lazy-loads every image by default', function () {
    $value = $this->getTestValue([
        imageNode('a.jpg'),
        imageNode('b.jpg'),
        imageNode('c.jpg'),
    ]);

    Mutator::plugin(LazyLoadImages::class);

    $html = $this->renderTestValue($value);
    expect(substr_count($html, 'loading="lazy"'))->toEqual(3);
});

it('skips lazy loading on the first image when it is the first top-level node', function () {
    $value = $this->getTestValue([
        imageNode('hero.jpg'),
        paragraphNode([['type' => 'text', 'text' => 'Body']]),
        imageNode('inline.jpg'),
    ]);

    Mutator::plugin(new LazyLoadImages(skipFirst: true));

    $html = $this->renderTestValue($value);
    expect($html)->toContain('<img src="hero.jpg" decoding="async">');
    expect($html)->toContain('<img src="inline.jpg" loading="lazy" decoding="async">');
});

it('skips lazy loading on the first image inside the first paragraph', function () {
    $value = $this->getTestValue([
        paragraphNode([imageNode('hero.jpg'), imageNode('second.jpg')]),
        paragraphNode([imageNode('later.jpg')]),
    ]);

    Mutator::plugin(new LazyLoadImages(skipFirst: true));

    $html = $this->renderTestValue($value);
    expect($html)->toContain('<img src="hero.jpg" decoding="async">');
    expect($html)->toContain('<img src="second.jpg" loading="lazy" decoding="async">');
    expect($html)->toContain('<img src="later.jpg" loading="lazy" decoding="async">');
});

it('still lazy-loads the first image when the document starts with a heading', function () {
    $value = $this->getTestValue([
        [
            'type' => 'heading',
            'attrs' => ['level' => 1],
            'content' => [['type' => 'text', 'text' => 'Title']],
        ],
        paragraphNode([imageNode('not-hero.jpg')]),
    ]);

    Mutator::plugin(new LazyLoadImages(skipFirst: true));

    expect($this->renderTestValue($value))->toContain(
        '<img src="not-hero.jpg" loading="lazy" decoding="async">'
    );
});

it('switches to lazysizes markup when usingLazysizes() is called', function () {
    $value = $this->getTestValue([imageNode('a.jpg')]);

    Mutator::plugin((new LazyLoadImages)->usingLazysizes());

    expect($this->renderTestValue($value))->toEqual(
        '<img class="lazyload" data-src="a.jpg">'
    );
});

it('omits the native loading and decoding attributes in lazysizes mode', function () {
    $value = $this->getTestValue([imageNode('a.jpg')]);

    Mutator::plugin((new LazyLoadImages)->usingLazysizes());

    $html = $this->renderTestValue($value);
    expect($html)->not->toContain('loading=');
    expect($html)->not->toContain('decoding=');
});

it('uses a custom class name when passed to usingLazysizes', function () {
    $value = $this->getTestValue([imageNode('a.jpg')]);

    Mutator::plugin((new LazyLoadImages)->usingLazysizes('lazy-load'));

    expect($this->renderTestValue($value))->toContain('class="lazy-load"');
});

it('returns the plugin instance from usingLazysizes for fluent chaining', function () {
    $plugin = new LazyLoadImages;
    expect($plugin->usingLazysizes())->toBe($plugin);
});

it('skips lazysizes markup on the first image when skipFirst is set', function () {
    $value = $this->getTestValue([
        imageNode('hero.jpg'),
        paragraphNode([['type' => 'text', 'text' => 'Body']]),
        imageNode('inline.jpg'),
    ]);

    Mutator::plugin((new LazyLoadImages(skipFirst: true))->usingLazysizes());

    $html = $this->renderTestValue($value);
    expect($html)->toContain('<img src="hero.jpg">');
    expect($html)->toContain('<img class="lazyload" data-src="inline.jpg">');
});
