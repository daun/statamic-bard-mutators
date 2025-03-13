<?php

namespace Daun\BardMutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;

class WrapTables extends Plugin
{
    protected array $types = ['table'];

    public function __construct(
        protected string $tag = 'div',
        protected string $class = 'table-wrapper'
    ) {}

    public function render(array $value, object $info, array $params): array
    {
        if ($info->parent->type === $this->tag) return $value;

        $inner = array_splice($value, 2, count($value), [0]);
        return [$this->tag, ['class' => $this->class], $value, ...$inner];
    }
}
