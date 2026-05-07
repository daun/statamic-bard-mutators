<?php

namespace Daun\BardMutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;

class LazyLoadImages extends Plugin
{
    protected array $types = ['image'];

    protected ?string $lazysizesClass = null;

    public function __construct(
        protected bool $skipFirst = false,
    ) {}

    public function usingLazysizes(string $class = 'lazyload'): self
    {
        $this->lazysizesClass = $class;

        return $this;
    }

    public function render(array $value, object $info, array $params): array
    {
        $skip = $this->skipFirst && $this->isAboveTheFold($info);

        if ($this->lazysizesClass !== null) {
            return $skip ? $value : $this->applyLazysizes($value);
        }

        if (! $skip) {
            $value[1]['loading'] ??= 'lazy';
        }
        $value[1]['decoding'] ??= 'async';

        return $value;
    }

    protected function applyLazysizes(array $value): array
    {
        $existing = trim($value[1]['class'] ?? '');
        $value[1]['class'] = $existing === ''
            ? $this->lazysizesClass
            : $existing.' '.$this->lazysizesClass;

        if (isset($value[1]['src'])) {
            $value[1]['data-src'] = $value[1]['src'];
            unset($value[1]['src']);
        }

        return $value;
    }

    /**
     * Detect whether this image is the first top-level node, or the first image
     * inside the first paragraph/figure of the document.
     */
    protected function isAboveTheFold(object $info): bool
    {
        $first = ($info->root->content ?? [])[0] ?? null;
        if (! $first) {
            return false;
        }

        if ($first === $info->item) {
            return true;
        }

        if (in_array($first->type ?? null, ['paragraph', 'figure'], true)) {
            return $this->findFirstImage($first) === $info->item;
        }

        return false;
    }

    protected function findFirstImage(object $node): ?object
    {
        if (($node->type ?? null) === 'image') {
            return $node;
        }
        foreach ($node->content ?? [] as $child) {
            if ($found = $this->findFirstImage($child)) {
                return $found;
            }
        }

        return null;
    }
}
