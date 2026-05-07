<?php

namespace Daun\BardMutators;

use InvalidArgumentException;
use JackSleight\StatamicBardMutator\Plugins\Plugin;
use JackSleight\StatamicBardMutator\Support\Data;

class InsertHeadingPermalinks extends Plugin
{
    protected array $types = ['heading'];

    public function __construct(
        protected string $behavior = 'prepend',
        protected string $icon = '#',
        protected string $label = 'Permalink to {text}',
        protected array $levels = [1, 2, 3, 4, 5, 6],
        protected ?string $class = null,
    ) {
        if (! in_array($behavior, ['prepend', 'append'], true)) {
            throw new InvalidArgumentException("behavior must be 'prepend' or 'append'");
        }
    }

    public function process(object $item, object $info): void
    {
        if (! in_array($item->attrs->level ?? null, $this->levels, true)) {
            return;
        }

        $id = $item->attrs->id ?? null;
        if (! $id) {
            return;
        }

        if ($this->hasPermalink($item)) {
            return;
        }

        $text = $this->extractText($item->content ?? []);
        if ($text === '') {
            return;
        }

        $permalink = $this->buildPermalink($id, $text);
        $content = $item->content ?? [];

        Data::apply($item, content: $this->behavior === 'prepend'
            ? [$permalink, ...$content]
            : [...$content, $permalink]);
    }

    protected function buildPermalink(string $id, string $text): object
    {
        $attrs = ['href' => '#'.$id, 'aria-label' => $this->resolveLabel($text)];
        if ($this->class !== null) {
            $attrs['class'] = $this->class;
        }
        $attrs['data-bmu-permalink'] = '';

        return Data::html('a', $attrs, [
            Data::html('<span aria-hidden="true">'.$this->icon.'</span>'),
        ]);
    }

    protected function resolveLabel(string $text): string
    {
        return str_replace('{text}', $text, $this->label);
    }

    protected function hasPermalink(object $item): bool
    {
        foreach ($item->content ?? [] as $child) {
            if (($child->type ?? null) === 'bmuHtml'
                && isset($child->render[1]['data-bmu-permalink'])) {
                return true;
            }
        }

        return false;
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
