<?php

use Daun\BardMutators\MarkAssetLinks;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Asset;
use Tests\TestCase;

uses(TestCase::class);

function assetLinkValue($href)
{
    return test()->getTestValue([[
        'type' => 'paragraph',
        'content' => [[
            'type' => 'text',
            'marks' => [[
                'type' => 'link',
                'attrs' => ['href' => $href],
            ]],
            'text' => 'download',
        ]],
    ]]);
}

function fakeAsset(string $basename): AssetContract
{
    $asset = Mockery::mock(AssetContract::class);
    $asset->shouldReceive('basename')->andReturn($basename);

    return $asset;
}

it('adds a download attribute to links pointing at assets', function () {
    Asset::shouldReceive('findByUrl')
        ->with('/assets/document.pdf')
        ->andReturn(fakeAsset('document.pdf'));

    $value = assetLinkValue('/assets/document.pdf');

    Mutator::plugin(MarkAssetLinks::class);

    expect($this->renderTestValue($value))->toEqual(
        '<p><a href="/assets/document.pdf" download="document.pdf">download</a></p>'
    );
});

it('uses the asset basename as the download filename', function () {
    Asset::shouldReceive('findByUrl')
        ->with('/files/whitepaper.pdf')
        ->andReturn(fakeAsset('whitepaper.pdf'));

    $value = assetLinkValue('/files/whitepaper.pdf');

    Mutator::plugin(MarkAssetLinks::class);

    expect($this->renderTestValue($value))->toEqual(
        '<p><a href="/files/whitepaper.pdf" download="whitepaper.pdf">download</a></p>'
    );
});

it('does not modify links to nonexistent assets', function () {
    Asset::shouldReceive('findByUrl')
        ->with('/files/missing.pdf')
        ->andReturn(null);

    $value = assetLinkValue('/files/missing.pdf');

    Mutator::plugin(MarkAssetLinks::class);

    expect($this->renderTestValue($value))->toEqual('<p><a href="/files/missing.pdf">download</a></p>');
});

it('does not modify links that do not match an asset', function () {
    Asset::shouldReceive('findByUrl')
        ->with('/about')
        ->andReturn(null);

    $value = assetLinkValue('/about');

    Mutator::plugin(MarkAssetLinks::class);

    expect($this->renderTestValue($value))->toEqual('<p><a href="/about">download</a></p>');
});

it('does not modify links with an empty href', function () {
    $value = assetLinkValue('');

    Mutator::plugin(MarkAssetLinks::class);

    expect($this->renderTestValue($value))->toEqual('<p><a>download</a></p>');
});
