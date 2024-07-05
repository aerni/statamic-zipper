<?php

namespace Aerni\Zipper;

use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Contracts\Assets\Asset;
use Statamic\Tags\Tags;

class ZipperTags extends Tags
{
    protected static $handle = 'zip';

    public function wildcard(): ?string
    {
        $value = $this->context->value($this->method);

        $files = match (true) {
            ($value instanceof Asset) => [$value], // Handle asset fields with `max_files: 1`.
            ($value instanceof OrderedQueryBuilder) => $value->get()->all(), // Handle asset fields without `max_files`.
            default => [],
        };

        if (empty($files)) {
            return null;
        }

        return Zipper::make($files)
            ->filename($this->params->get('filename'))
            ->expiry($this->params->get('expiry') ?? (int) config('zipper.expiry'))
            ->url();
    }
}
