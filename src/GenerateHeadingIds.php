<?php

namespace Daun\BardMutators;

use Illuminate\Support\Str;
use JackSleight\StatamicBardMutator\Plugins\Plugin;

class GenerateHeadingIds extends Plugin
{
    protected array $types = ['heading'];

    public function __construct(
        protected array $levels = [1, 2, 3, 4, 5, 6],
        protected string $prefix = '',
    ) {}

    public function process(object $item, object $info): void
    {
        if (! in_array($item->attrs->level ?? null, $this->levels, true)) {
            return;
        }
        if (! empty($item->attrs->id ?? null)) {
            return;
        }
        if ($slug = Str::slug($this->extractText($item->content ?? []))) {
            $item->attrs->id = $this->prefix.$slug;
        }
    }

    public function render(array $value, object $info, array $params): array
    {
        if ($id = $info->item->attrs->id ?? null) {
            $value[1]['id'] ??= $id;
        }

        return $value;
    }

    protected function extractText(iterable $content): string
    {
        return collect($content)->reduce(function (string $carry, object $node) {
            if (isset($node->text)) {
                return $carry.$node->text;
            }
            if (isset($node->content)) {
                return $carry.$this->extractText($node->content);
            }

            return $carry;
        }, '');
    }
}
