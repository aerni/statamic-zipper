<?php

namespace Aerni\Zipper;

use Facades\Aerni\Zipper\Zipper;
use Statamic\Assets\AssetCollection;
use Statamic\Tags\Tags;

class ZipperTags extends Tags
{
    protected static $handle = 'zip';

    public function wildcard(): ?string
    {
        return Zipper::route($this->files(), $this->filename());
    }

    protected function files(): AssetCollection
    {
        $files = $this->context->get($this->method);

        $value = optional($files)->value();

        // Handle asset fields with `max_files: 1`.
        if ($value instanceof \Statamic\Assets\Asset) {
            return new AssetCollection([$value]);
        }

        // Handle asset fields without `max_files`.
        if ($value instanceof \Statamic\Assets\OrderedQueryBuilder) {
            return $value->get();
        }

        // Simply return an empty collection by default.
        return new AssetCollection();
    }

    protected function filename(): ?string
    {
        return $this->params->get('filename');
    }
}
