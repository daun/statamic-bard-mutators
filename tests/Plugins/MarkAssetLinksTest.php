<?php

use Daun\BardMutators\MarkAssetLinks;
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Statamic\Assets\Asset as AssetClass;
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

function fakeAsset(string $basename, array $meta = []): AssetClass
{
    $asset = Mockery::mock(AssetClass::class);
    $asset->shouldReceive('basename')->andReturn($basename);
    $asset->shouldReceive('extension')->andReturn(pathinfo($basename, PATHINFO_EXTENSION));
    $asset->shouldReceive('get')->andReturnUsing(fn ($key, $fallback = null) => $meta[$key] ?? $fallback);

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

it('uses the original filename as the download name when configured', function () {
    Asset::shouldReceive('findByUrl')
        ->with('/files/my-file.pdf')
        ->andReturn(fakeAsset('my-file.pdf', ['original_filename' => 'My File']));

    $value = assetLinkValue('/files/my-file.pdf');

    Mutator::plugin(new MarkAssetLinks(useOriginalFilename: true));

    expect($this->renderTestValue($value))->toEqual(
        '<p><a href="/files/my-file.pdf" download="My File.pdf">download</a></p>'
    );
});

it('falls back to the basename when no original filename is stored', function () {
    Asset::shouldReceive('findByUrl')
        ->with('/files/no-meta.pdf')
        ->andReturn(fakeAsset('no-meta.pdf'));

    $value = assetLinkValue('/files/no-meta.pdf');

    Mutator::plugin(new MarkAssetLinks(useOriginalFilename: true));

    expect($this->renderTestValue($value))->toEqual(
        '<p><a href="/files/no-meta.pdf" download="no-meta.pdf">download</a></p>'
    );
});

it('does not modify links with an empty href', function () {
    $value = assetLinkValue('');

    Mutator::plugin(MarkAssetLinks::class);

    expect($this->renderTestValue($value))->toEqual('<p><a>download</a></p>');
});
