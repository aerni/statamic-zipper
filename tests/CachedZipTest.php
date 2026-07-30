<?php

use Aerni\Zipper\Zip;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(PreventsSavingStacheItemsToDisk::class);

beforeEach(function () {
    config(['filesystems.disks.test' => [
        'driver' => 'local',
        'root' => __DIR__.'/__fixtures__/assets',
    ]]);

    Storage::fake('cache');
    Storage::fake('sink');

    config([
        'zipper.save' => true,
        'zipper.disk' => 'cache',
    ]);

    $this->container = AssetContainer::make('test')->disk('test')->save();

    /**
     * Stream the zip to a throwaway disk rather than php://output, so the
     * cached copy is written without the zip landing in the test output.
     */
    $this->streamZip = fn (Zip $zip) => $zip->get()->saveTo(Storage::disk('sink')->path(''));
});

test('caches the zip to disk on the first request', function () {
    $zip = Zip::make($this->container->assets()->all())->filename('my-files');

    ($this->streamZip)($zip);

    expect(Storage::disk('cache')->allFiles())->toHaveCount(1);
});

test('downloads the cached zip on subsequent requests', function () {
    $zip = Zip::make($this->container->assets()->all())->filename('my-files');

    ($this->streamZip)($zip);

    $response = $zip->get();

    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->headers->get('content-disposition'))->toContain('my-files.zip');
});
