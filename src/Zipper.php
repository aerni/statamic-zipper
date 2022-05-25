<?php

namespace Aerni\Zipper;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
        $fingerprint = md5(collect($files)->implode(''));

        $files = Cache::remember($fingerprint, 3600, function () use ($files) {
            return collect(self::files($files))
                ->filter(fn ($file) => self::fileExists($file))
                ->all();
        });

        $zip = Zip::create(self::filename($filename), $files);

        if (! config('zipper.save')) {
            return $zip;
        }

        $path = Storage::disk(config('zipper.disk'))->getAdapter()->getPathPrefix();
        $cachepath = self::filename($path.$fingerprint);

        return File::exists($cachepath)
            ? response()->download($cachepath, $zip->getName())
            : $zip->cache($cachepath);
    }

    protected static function files(Collection|array $files): array
    {
        return collect($files)
            ->map(fn ($file) => match (true) {
                ($file instanceof Asset) => $file->absoluteUrl(),
                (is_array($file)) => Arr::get($file, 'url'),
                default => $file,
            })
            ->filter(fn ($file) => filter_var($file, FILTER_VALIDATE_URL))
            ->all();
    }

    protected static function filename(?string $filename): string
    {
        return $filename ? "{$filename}.zip" : time().'.zip';
    }

    protected static function fileExists(string $file): bool
    {
        try {
            return Http::get($file)->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
