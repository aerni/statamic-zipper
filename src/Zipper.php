<?php

namespace Aerni\Zipper;

use Illuminate\Support\Facades\Storage;
use Statamic\Assets\AssetCollection;
use Statamic\Facades\File;
use STS\ZipStream\ZipStreamFacade as Zip;

class Zipper
{
    public function route(AssetCollection $files, string $filename = null): string
    {
        return route('statamic.zipper.create', [
            'files' => $this->fileUrls($files),
            'filename' => $this->filename($filename),
        ]);
    }

    public function create(array $files, string $filename)
    {
        $zip = Zip::create($filename, $files);

        if (! config('zipper.save')) {
            return $zip;
        }

        $path = Storage::disk(config('zipper.disk'))
            ->getAdapter()
            ->getPathPrefix();

        $cachepath = "{$path}{$zip->getFingerprint()}.zip";

        return File::exists($cachepath)
            ? response()->download($cachepath, $filename)
            : $zip->cache($cachepath);
    }

    protected function fileUrls(AssetCollection $files): array
    {
        return $files->map(function ($file) {
            return substr($file->url(), 1);
        })->all();
    }

    protected function filename(string $filename = null): string
    {
        return $filename
            ? "{$filename}.zip"
            : time().'.zip';
    }
}
