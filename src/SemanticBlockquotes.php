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
        if ($info->parent->type === 'figure') {
            return;
        }

        [$content, $source] = $this->extractSource($item);

        if (count($source)) {
            Data::morph($item, Data::html('figure', ['class' => $this->class], [
                Data::clone($item, content: $content),
                Data::html('figcaption', content: $source),
            ]));
        } else {
            Data::morph($item, Data::html('figure', ['class' => $this->class], [
                Data::clone($item),
            ]));
        }
    }

    /**
     * Read author/source from last paragraph
     */
    protected function extractSource(object $item): array
    {
        $sourceParagraph = Arr::last($item->content);
        if ($sourceParagraph?->type !== 'paragraph') {
            return [$item->content, []];
        }

        $source = Arr::first($sourceParagraph->content ?? []);
        if ($source?->type !== 'text' || ! Str::startsWith($source->text, $this->sourcePrefix)) {
            return [$item->content, []];
        }

        $source->text = Str::ltrim(Str::chopStart($source->text, $this->sourcePrefix));

        return [
            collect($item->content)->filter(fn ($node) => $node !== $sourceParagraph)->values()->all(),
            $sourceParagraph->content,
        ];
    }
}
