<?php

namespace Aerni\Zipper\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Statamic\Assets\AssetContainer;

trait HasAssets
{
    protected $fakeAssetsDirectory = __DIR__ . '/__fixtures__/dev-null/assets';

    protected function makeAssets()
    {
        // Define the config for the disk we are going to use
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => $this->fakeAssetsDirectory,
            'url' => '/test',
        ]]);

        $this->assetContainer = (new AssetContainer)->handle('test')->disk('test')->save();

        collect(File::files(__DIR__.'/__fixtures__/assets'))->each(function ($file) {
            $file = new UploadedFile($file->getPathname(), $file->getFilename());

            $this->assetContainer->makeAsset($file->getFilename())->upload($file);
        });
    }
}
