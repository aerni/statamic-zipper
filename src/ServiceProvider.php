<?php

namespace Aerni\Zipper;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        ZipperTags::class,
    ];
}
