<?php

namespace Aerni\Zipper\Tests;

use Aerni\Zipper\Facades\ZipperStore;
use Aerni\Zipper\ZipperTags;
use Illuminate\Support\Str;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Assets\Assets;
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

        $this->container = (new AssetContainer)->handle('test')->disk('test');

        AssetContainerFacade::shouldReceive('findByHandle')->andReturn($this->container);
        AssetContainerFacade::shouldReceive('find')->andReturn($this->container);
        AssetContainerFacade::shouldReceive('all')->andReturn(collect([$this->container]));

        $this->tag = app(ZipperTags::class);
    }

    /** @test */
    public function can_handle_a_single_asset()
    {
        $file = $this->container->files()->first();

        $fieldtype = (new Assets)->setField(new Field('assets', [
            'type' => 'assets',
            'max_files' => 1,
        ]));

        $value = new Value($file, 'assets', $fieldtype);

        $this->tag
            ->setContext(['assets' => $value])
            ->setParameters([]);

        $this->tag->method = 'assets';

        $url = $this->tag->wildcard();

        $uri = Str::afterLast($url, '/');
        $id = Str::before($uri, '?signature');

        $file = ZipperStore::get($id)->files()[0];

        $this->assertSame($value->value()->resolvedPath(), $file->resolvedPath());
    }

    /** @test */
    public function can_handle_multiple_assets()
    {
        $files = $this->container->files()->all();

        $fieldtype = (new Assets)->setField(new Field('assets', [
            'type' => 'assets',
        ]));

        $value = new Value($files, 'assets', $fieldtype);

        $this->tag
            ->setContext(['assets' => $value])
            ->setParameters([]);

        $this->tag->method = 'assets';

        $url = $this->tag->wildcard();

        $uri = Str::afterLast($url, '/');
        $id = Str::before($uri, '?signature');

        $files = ZipperStore::get($id)->files()->map(fn ($file) => $file->resolvedPath());

        $value->value()->get()->each(function ($file) use ($files) {
            $this->assertContains($file->resolvedPath(), $files);
        });
    }
}
