<?php

namespace Aerni\Zipper\Tests;

use Statamic\Assets\Asset;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Illuminate\Support\Str;
use Aerni\Zipper\ZipperTags;
use Statamic\Facades\Antlers;
use Aerni\Zipper\Facades\ZipperStore;
use Statamic\Fieldtypes\Assets\Assets;
use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Facades\AssetContainer;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class ZipperTagsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private ZipperTags $tag;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/assets',
        ]]);

        $this->container = AssetContainer::make('test')->disk('test')->save();
    }

    /** @test */
    public function can_handle_a_single_asset()
    {
        $asset = $this->container->assets()->first();

        $url = Antlers::parse('{{ zip:assets }}', ['assets' => $asset]);

        $id = Str::before(basename($url), '?signature');

        $files = ZipperStore::get($id)->files();

        $this->assertEquals($asset->resolvedPath(), $files->first()->resolvedPath());
    }

    /** @test */
    public function can_handle_multiple_assets()
    {
        $assets = $this->container->queryAssets();

        $url = Antlers::parse('{{ zip:assets }}', ['assets' => $assets]);

        $id = Str::before(basename($url), '?signature');

        $files = ZipperStore::get($id)->files()->map(fn ($file) => $file->resolvedPath());

        $assets->get()->each(fn ($asset) => $this->assertContains($asset->resolvedPath(), $files));
    }
}
