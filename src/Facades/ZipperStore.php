<?php

namespace Aerni\Zipper\Facades;

use Illuminate\Support\Facades\Facade;

class ZipperStore extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Aerni\Zipper\ZipperStore::class;
    }
}
