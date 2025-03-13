<?php

namespace Daun\BardMutators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JackSleight\StatamicBardMutator\Plugins\Plugin;
use JackSleight\StatamicBardMutator\Support\Data;

class SemanticBlockquotes extends Plugin
{
    protected array $types = ['blockquote'];

    public function __construct(
        protected ?string $class = null,
        protected string|array $sourcePrefix = ['-', '–', '—']
    ) {}

    public function process(object $item, object $info): void
    {
        if ($info->parent->type === 'figure') return;

        // Read author/source from last paragraph
        $paragraph = Arr::last($item->content, fn($node) => $node->type === 'paragraph');
        $source = Arr::last($paragraph->content ?? [], fn($node) => $node->type === 'text' && Str::startsWith($node->text, $this->sourcePrefix));

        if (!$source) return;

        // Remove source prefix
        $source->text = Str::ltrim(Str::chopStart($source->text, $this->sourcePrefix));

        // Turn into figure + figcaption
        Data::morph($item, Data::html('figure', ['class' => $this->class], [
            Data::clone($item, content: collect($item->content)->filter(fn ($node) => $node !== $paragraph)->values()->all()),
            Data::html('figcaption', [], [$source]),
        ]));
    }
}
