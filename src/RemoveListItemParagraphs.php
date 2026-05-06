<?php

namespace Daun\BardMutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;

class RemoveListItemParagraphs extends Plugin
{
    protected array $types = ['listItem'];

    public function process(object $item, object $info): void
    {
        $content = $item->content ?? [];

        if (count($content) === 1 && $content[0]->type === 'paragraph') {
            $item->content = $content[0]->content;
        }
    }
}
