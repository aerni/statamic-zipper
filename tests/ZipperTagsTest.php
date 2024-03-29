<?php

namespace Aerni\Zipper\Tests;

use Aerni\Zipper\Facades\ZipperStore;
use Aerni\Zipper\ZipperTags;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Assets\Assets;

class ZipperTagsTest extends TestCase
{
    use HasAssets;
    use PreventSavingStacheItemsToDisk;

    private ZipperTags $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeAssets();

        $this->tag = app(ZipperTags::class);

        Http::fake();
    }

    /** @test */
    public function can_handle_a_single_asset()
    {
        $file = $this->assetContainer->files()->first();

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
        $files = $this->assetContainer->files()->all();

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
