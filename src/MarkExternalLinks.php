<?php

namespace App\Bard\Mutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;
use Statamic\Facades\URL;

class MarkExternalLinks extends Plugin
{
    protected array $types = ['link'];

    public function __construct(
        protected ?string $target = '_blank',
        protected ?string $rel = 'external'
    ) {}

    public function render(array $value, object $info, array $params): array
    {
        $url = $value[1]['href'] ?? '';
        if (!$url || !URL::isExternal($url)) return $value;

        if ($this->target) {
            $value[1]['target'] = ($value[1]['target'] ?? null) ?: $this->target;
        }
        if ($this->rel) {
            $value[1]['rel'] = ($value[1]['rel'] ?? null) ?: $this->rel;
        }

        return $value;
    }
}
