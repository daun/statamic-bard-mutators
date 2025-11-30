<?php

namespace Daun\BardMutators;

use JackSleight\StatamicBardMutator\Plugins\Plugin;
use Statamic\Facades\Asset;

class MarkAssetLinks extends Plugin
{
    protected array $types = ['link'];

    public function render(array $value, object $info, array $params): array
    {
        $url = $value[1]['href'] ?? '';
        if (! $url) {
            return $value;
        }

        if ($asset = Asset::findByUrl($url)) {
            $value[1]['download'] = $asset->basename();
        }

        return $value;
    }
}
