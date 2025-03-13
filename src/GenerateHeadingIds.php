<?php

namespace Daun\BardMutators;

use Illuminate\Support\Str;
use JackSleight\StatamicBardMutator\Plugins\Plugin;

class GenerateHeadingIds extends Plugin
{
    protected array $types = ['heading'];

    public function __construct(
        protected array $levels = [1, 2, 3, 4, 5, 6]
    ) {}

    public function render(array $value, object $info, array $params): array
    {
        if (in_array($info->item->attrs->level, $this->levels)) {
            $content = collect($info->item->content)->implode('text', '');
            $value[1]['id'] ??= Str::slug($content);
        }
        return $value;
    }
}
