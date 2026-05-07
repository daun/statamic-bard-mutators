<?php

namespace Daun\BardMutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;

class NormalizeHeadingLevels extends Plugin
{
    protected array $types = ['heading'];

    /** @var array<int, array<int, int>> */
    protected array $levelCache = [];

    public function render(array $value, object $info, array $params): array
    {
        $level = $info->item->attrs->level ?? null;
        if (! $level) {
            return $value;
        }

        $rootKey = spl_object_id($info->root);
        $this->levelCache[$rootKey] ??= $this->computeLevels($info->root);

        $newLevel = $this->levelCache[$rootKey][spl_object_id($info->item)] ?? $level;
        if ($newLevel !== $level) {
            $value[0] = "h{$newLevel}";
        }

        return $value;
    }

    /**
     * @return array<int, int>
     */
    protected function computeLevels(object $root): array
    {
        $levels = [];
        $prev = null;
        $this->walk($root, $levels, $prev);

        return $levels;
    }

    /**
     * @param  array<int, int>  $levels
     */
    protected function walk(object $node, array &$levels, ?int &$prev): void
    {
        if (($node->type ?? null) === 'heading' && $level = $node->attrs->level ?? null) {
            $newLevel = $prev === null ? $level : min($level, $prev + 1);
            $levels[spl_object_id($node)] = $newLevel;
            $prev = $newLevel;
        }

        foreach ($node->content ?? [] as $child) {
            $this->walk($child, $levels, $prev);
        }
    }
}
