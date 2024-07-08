<?php

namespace Aerni\Zipper;

use Statamic\Tags\Tags;
use Illuminate\Support\Arr;
use Statamic\Facades\Compare;
use Aerni\Zipper\Facades\Zipper;
use Statamic\Contracts\Assets\Asset;

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
