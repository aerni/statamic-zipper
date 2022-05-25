<?php

namespace Aerni\Zipper;

use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Contracts\Assets\Asset;
use Statamic\Tags\Tags;

class ZipperTags extends Tags
{
    protected static $handle = 'zip';

    public function wildcard(): string
    {
        return Zipper::route($this->files(), $this->filename());
    }

    protected function files(): array
    {
        $value = $this->context->get($this->method)?->value();

        return match (true) {
            ($value instanceof Asset) => [$value], // Handle asset fields with `max_files: 1`.
            ($value instanceof OrderedQueryBuilder) => $value->get()->all(), // Handle asset fields without `max_files`.
            default => [],
        };
    }

    protected function filename(): ?string
    {
        return $this->params->get('filename');
    }
}
