<?php

namespace Aerni\Zipper;

use Illuminate\Support\Facades\Storage;
use Statamic\Assets\AssetCollection;
use Statamic\Facades\File;
use STS\ZipStream\ZipStreamFacade as Zip;

class Zipper
{
    public static function route(AssetCollection $files, string $filename = null): string
    {
        return route('statamic.zipper.create', [
            'files' => self::fileUrls($files),
            'filename' => self::filename($filename),
        ]);
    }

    public static function create(array $files, string $filename)
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

    protected static function fileUrls(AssetCollection $files): array
    {
        return $files->map(function ($file) {
            return substr($file->url(), 1);
        })->all();
    }

    protected static function filename(string $filename = null): string
    {
        return $filename
            ? "{$filename}.zip"
            : time().'.zip';
    }
}
