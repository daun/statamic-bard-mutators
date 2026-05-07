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

    public function render(array $value, object $info, array $params): array
    {
        if (in_array($info->item->attrs->level, $this->levels)) {
            if ($slug = Str::slug($this->extractText($info->item->content ?? []))) {
                $value[1]['id'] ??= $this->prefix.$slug;
            }
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
