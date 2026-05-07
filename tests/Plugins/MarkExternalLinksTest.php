<?php

use Daun\BardMutators\MarkExternalLinks;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Tests\TestCase;

uses(TestCase::class);

function externalLinkValue($href)
{
    return test()->getTestValue([[
        'type' => 'paragraph',
        'content' => [[
            'type' => 'text',
            'marks' => [[
                'type' => 'link',
                'attrs' => ['href' => $href],
            ]],
            'text' => 'click',
        ]],
    ]]);
}

it('adds target and rel to external links', function () {
    $value = externalLinkValue('https://example.com');

    expect($this->renderTestValue($value))->toEqual('<p><a href="https://example.com">click</a></p>');

    Mutator::plugin(MarkExternalLinks::class);

    expect($this->renderTestValue($value))->toEqual(
        '<p><a rel="external" target="_blank" href="https://example.com">click</a></p>'
    );
});

it('does not modify internal links', function () {
    $value = externalLinkValue('/about');

    Mutator::plugin(MarkExternalLinks::class);

    expect($this->renderTestValue($value))->toEqual('<p><a href="/about">click</a></p>');
});

it('does not modify hash links', function () {
    $value = externalLinkValue('#section');

    Mutator::plugin(MarkExternalLinks::class);

    expect($this->renderTestValue($value))->toEqual('<p><a href="#section">click</a></p>');
});

it('does not modify links pointing to the current site', function () {
    $value = externalLinkValue(config('app.url').'/about');

    Mutator::plugin(MarkExternalLinks::class);

    expect($this->renderTestValue($value))->toEqual(sprintf('<p><a href="%s/about">click</a></p>', config('app.url')));
});

it('honors custom target and rel values', function () {
    $value = externalLinkValue('https://example.com');

    Mutator::plugin(new MarkExternalLinks(target: '_self', rel: 'noopener noreferrer'));

    expect($this->renderTestValue($value))->toEqual(
        '<p><a rel="noopener noreferrer" target="_self" href="https://example.com">click</a></p>'
    );
});

it('skips target when target is null', function () {
    $value = externalLinkValue('https://example.com');

    Mutator::plugin(new MarkExternalLinks(target: null, rel: 'external'));

    expect($this->renderTestValue($value))->toEqual(
        '<p><a rel="external" href="https://example.com">click</a></p>'
    );
});

it('skips rel when rel is null', function () {
    $value = externalLinkValue('https://example.com');

    Mutator::plugin(new MarkExternalLinks(target: '_blank', rel: null));

    expect($this->renderTestValue($value))->toEqual(
        '<p><a target="_blank" href="https://example.com">click</a></p>'
    );
});
