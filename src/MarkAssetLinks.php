<?php

namespace Daun\BardMutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;
use Statamic\Assets\Asset;
use Statamic\Facades\Asset as Assets;

class MarkAssetLinks extends Plugin
{
    protected array $types = ['link'];

    public function __construct(
        protected bool $useOriginalFilename = false
    ) {}

    public function render(array $value, object $info, array $params): array
    {
        $url = $value[1]['href'] ?? '';
        if (! $url) {
            return $value;
        }

        if (($asset = Assets::findByUrl($url)) instanceof Asset) {
            $value[1]['download'] = $this->getBasename($asset);
        }

        return $value;
    }

    protected function getBasename(Asset $asset): string
    {
        if ($this->useOriginalFilename) {
            if ($original = $asset->get('original_filename')) {
                return $original.'.'.$asset->extension();
            }
        }

        return $asset->basename();
    }
}
