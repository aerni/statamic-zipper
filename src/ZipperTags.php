<?php

namespace Aerni\Zipper;

use Aerni\Zipper\Zipper;
use Illuminate\Support\Arr;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Compare;
use Statamic\Tags\Tags;

class ZipperTags extends Tags
{
    protected static $handle = 'zip';

    public function wildcard(): ?string
    {
        $value = $this->context->value($this->method);

        if (Compare::isQueryBuilder($value)) {
            $value = $value->get()->all();
        }

        $files = collect(Arr::wrap($value))
            ->whereInstanceOf(Asset::class)
            ->all();

        if (empty($files)) {
            return null;
        }

        return Zipper::make($files)
            ->filename($this->params->get('filename'))
            ->expiry($this->params->get('expiry') ?? (int) config('zipper.expiry'))
            ->url();
    }
}
