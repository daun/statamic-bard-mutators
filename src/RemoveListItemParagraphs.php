<?php

namespace Daun\BardMutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;

class RemoveListItemParagraphs extends Plugin
{
    protected array $types = ['paragraph'];

    public function render(array $value, object $info, array $params): ?array
    {
        if (($info->parent->type ?? null) === 'listItem') {
            return null;
        }

        return $value;
    }
}
