<?php

namespace Aerni\Zipper;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\File;
use STS\ZipStream\ZipStreamFacade as Zip;

class Zipper
{
    public static function route(Collection|array $files, ?string $filename = null): string
    {
        return route('statamic.zipper.create', [
            'files' => Crypt::encryptString(self::files($files)),
            'filename' => $filename,
        ]);
    }

    public static function create(Collection|array $files, ?string $filename = null)
    {
        $files = self::files($files)->filter(fn ($file) => self::fileExists($file));

        if ($files->isEmpty()) {
            return redirect()->back();
        }

        $zip = Zip::create(self::filename($filename), $files->all());

        if (! config('zipper.save')) {
            return $zip;
        }

        $path = Storage::disk(config('zipper.disk'))->getAdapter()->getPathPrefix();
        $cachepath = self::filename($path.$zip->getFingerprint());

        return File::exists($cachepath)
            ? response()->download($cachepath, $zip->getName())
            : $zip->cache($cachepath);
    }

    protected static function files(Collection|array $files): Collection
    {
        return collect($files)
            ->map(fn ($file) => match (true) {
                ($file instanceof Asset) => $file->resolvedPath(),
                default => $file,
            });
    }

    protected static function filename(?string $filename): string
    {
        return $filename ? "{$filename}.zip" : time().'.zip';
    }

    protected static function fileExists(string $path): bool
    {
        if (URL::isValidUrl($path)) {
            try {
                return Http::get($path)->successful();
            } catch (Exception $e) {
                return false;
            }
        }

        return File::exists($path);
    }
}
