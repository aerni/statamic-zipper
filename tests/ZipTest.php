<?php

use Aerni\Zipper\Zip;
use Illuminate\Support\Facades\Storage;
use STS\ZipStream\Builder;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(PreventsSavingStacheItemsToDisk::class);

function makeZip(Asset $asset): Zip
{
    return Zip::make([$asset])->filename('my-files');
}

function cachedZipPath(Zip $zip): string
{
    return (new ReflectionMethod($zip, 'create'))->invoke($zip)->getFingerprint().'.zip';
}

function cacheZipOnDisk(Zip $zip, string $contents): void
{
    Storage::disk('cache')->put(cachedZipPath($zip), $contents);
}

beforeEach(function () {
    config(['filesystems.disks.test' => [
        'driver' => 'local',
        'root' => __DIR__.'/__fixtures__/assets',
    ]]);

    config(['zipper.disk' => 'cache']);

    Storage::fake('cache');

    $this->container = AssetContainer::make('test')->disk('test')->save();
});

test('caches the zip to disk when caching is enabled and none is cached yet', function () {
    config(['zipper.save' => true]);

    $zip = makeZip($this->container->assets()->first());

    expect(Storage::disk('cache')->exists(cachedZipPath($zip)))->toBeFalse();

    $response = $zip->get();

    // Nothing was cached yet, so Zipper builds a fresh zip (a streaming Builder)
    // and writes it to the cache disk for subsequent requests to reuse.
    expect($response)->toBeInstanceOf(Builder::class)
        ->and(Storage::disk('cache')->exists(cachedZipPath($zip)))->toBeTrue();
});

test('downloads the cached zip from disk when one already exists', function () {
    config(['zipper.save' => true]);

    $zip = makeZip($this->container->assets()->first());

    // Pretend a previous request already cached this exact zip to disk.
    cacheZipOnDisk($zip, 'the-cached-zip');

    $response = $zip->get();

    ob_start();
    $response->sendContent();
    $streamed = ob_get_clean();

    // We streamed the file that was already on disk rather than building a new
    // zip, so the response body is the cached file byte-for-byte.
    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($streamed)->toBe('the-cached-zip');
});

test('returns a fresh zip on every request when caching is disabled', function () {
    config(['zipper.save' => false]);

    $first = makeZip($this->container->assets()->first())->get();
    $second = makeZip($this->container->assets()->first())->get();

    // Each request builds a new zip and nothing is ever written to the cache disk.
    expect($first)->toBeInstanceOf(Builder::class)
        ->and($second)->toBeInstanceOf(Builder::class)
        ->and($first)->not->toBe($second)
        ->and(Storage::disk('cache')->allFiles())->toBeEmpty();
});

test('ignores a cached zip on disk when caching is disabled', function () {
    config(['zipper.save' => false]);

    $zip = makeZip($this->container->assets()->first());

    // A cached zip exists on disk, but with caching turned off it must not be used.
    cacheZipOnDisk($zip, 'the-cached-zip');

    $response = $zip->get();

    // Zipper builds a fresh zip (a streaming Builder) instead of downloading the
    // cached file on disk.
    expect($response)->toBeInstanceOf(Builder::class);
});
