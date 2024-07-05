<?php

namespace Aerni\Zipper\Tests;

use Statamic\Facades\Path;
use Illuminate\Support\Str;
use Statamic\Facades\Stache;

trait PreventSavingStacheItemsToDisk
{
    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected function preventSavingStacheItemsToDisk()
    {
        $this->fakeStacheDirectory = Path::tidy($this->fakeStacheDirectory);

        Stache::stores()->each(function ($store) {
            $dir = Path::tidy(__DIR__.'/__fixtures__');
            $relative = Str::after(str::after($store->directory(), $dir), '/');
            $store->directory($this->fakeStacheDirectory.'/'.$relative);
        });
    }

    protected function deleteFakeStacheDirectory()
    {
        app('files')->deleteDirectory($this->fakeStacheDirectory);

        mkdir($this->fakeStacheDirectory);
        touch($this->fakeStacheDirectory.'/.gitkeep');
    }
}
