<?php

namespace Aerni\Zipper;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\File;
use STS\ZipStream\ZipStreamFacade as Zip;

class Zipper
{
    public static function route(Collection|array $files, ?string $filename = null): string
    {
        return route('statamic.zipper.create', [
            'files' => self::files($files),
            'filename' => $filename,
        ]);
    }

    public static function create(Collection|array $files, ?string $filename = null)
    {
        $zip = Zip::create(self::filename($filename), self::files($files));

        if (! config('zipper.save')) {
            return $zip;
        }

        $path = Storage::disk(config('zipper.disk'))->getAdapter()->getPathPrefix();
        $cachepath = self::filename($path.$zip->getFingerprint());

        return File::exists($cachepath)
            ? response()->download($cachepath, $zip->getName())
            : $zip->cache($cachepath);
    }

    protected static function files(Collection|array $files): array
    {
        return collect($files)
            ->map(fn ($file) => match (true) {
                ($file instanceof Asset) => substr($file->url(), 1),
                (is_array($file)) => Arr::get($file, 'url'),
                default => $file,
            })
            ->filter(fn ($file) => File::exists(public_path($file)))
            ->all();
    }

    protected static function filename(?string $filename): string
    {
        return $filename ? "{$filename}.zip" : time().'.zip';
    }
}
