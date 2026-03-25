<?php

use Aerni\Zipper\Facades\ZipperStore;
use Illuminate\Support\Str;
use Statamic\Facades\Antlers;
use Statamic\Facades\AssetContainer;

uses(Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk::class);

beforeEach(function () {
    config(['filesystems.disks.test' => [
        'driver' => 'local',
        'root' => __DIR__.'/__fixtures__/assets',
    ]]);

    $this->container = AssetContainer::make('test')->disk('test')->save();
});

test('can handle a single asset', function () {
    $asset = $this->container->assets()->first();


    $url = Antlers::parse('{{ zip:assets }}', ['assets' => $asset], true);

    $id = Str::before(basename($url), '?signature');

    $files = ZipperStore::get($id)->files();

    expect($files->first()->resolvedPath())->toEqual($asset->resolvedPath());
});

test('can handle multiple assets', function () {
    $assets = $this->container->queryAssets();

    $url = Antlers::parse('{{ zip:assets }}', ['assets' => $assets], true);

    $id = Str::before(basename($url), '?signature');

    $files = ZipperStore::get($id)->files()->map(fn ($file) => $file->resolvedPath());

    $assets->get()->each(fn ($asset) => expect($files)->toContain($asset->resolvedPath()));
});
