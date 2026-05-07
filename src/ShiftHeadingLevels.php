<?php

namespace Daun\BardMutators;

use InvalidArgumentException;
use JackSleight\StatamicBardMutator\Plugins\Plugin;

class ShiftHeadingLevels extends Plugin
{
    protected array $types = ['heading'];

    /** @var array<int, int> */
    protected array $offsetCache = [];

    public function __construct(
        protected int $shift = 0,
        protected int $min = 1,
        protected ?int $start = null,
    ) {
        if ($this->min < 1 || $this->min > 6) {
            throw new InvalidArgumentException('min must be between 1 and 6');
        }
        if ($this->start !== null && ($this->start < 1 || $this->start > 6)) {
            throw new InvalidArgumentException('start must be between 1 and 6');
        }
        if ($this->start !== null && $this->shift !== 0) {
            throw new InvalidArgumentException('shift and start cannot be combined');
        }
    }

    public function render(array $value, object $info, array $params): array
    {
        $level = $info->item->attrs->level ?? null;
        if (! $level) {
            return $value;
        }

        $newLevel = max($this->min, min(6, $level + $this->resolveOffset($info)));
        if ($newLevel !== $level) {
            $value[0] = "h{$newLevel}";
        }

        return $value;
    }

    protected function resolveOffset(object $info): int
    {
        if ($this->start === null) {
            return $this->shift;
        }

        $key = spl_object_id($info->root);

        return $this->offsetCache[$key] ??= $this->computeStartOffset($info->root);
    }

    protected function computeStartOffset(object $root): int
    {
        $minPresent = $this->findMinHeadingLevel($root);

        return $minPresent === null ? 0 : $this->start - $minPresent;
    }

    protected function findMinHeadingLevel(object $node): ?int
    {
        $min = null;
        if (($node->type ?? null) === 'heading') {
            $min = $node->attrs->level ?? null;
        }
        foreach ($node->content ?? [] as $child) {
            $childMin = $this->findMinHeadingLevel($child);
            if ($childMin !== null && ($min === null || $childMin < $min)) {
                $min = $childMin;
            }
        }

        return $min;
    }
}
