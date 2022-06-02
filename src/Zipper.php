<?php

namespace Aerni\Zipper;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
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
        $files = self::files($files)->filter(fn ($file) => self::exists($file));

        if ($files->isEmpty()) {
            return redirect()->back();
        }

        $zip = Zip::create(self::filename($filename), $files->all());

        if (! config('zipper.save')) {
            return $zip;
        }

        $cachepath = Storage::disk(config('zipper.disk'))->path(self::filename($zip->getFingerprint()));

        return File::exists($cachepath)
            ? response()->download($cachepath, $zip->getName())
            : $zip->cache($cachepath);
    }

    protected static function files(Collection|array $files): Collection
    {
        return collect($files)->map(fn ($file) => match (true) {
            ($file instanceof Asset) => self::path($file),
            default => $file,
        });
    }

    protected static function path(Asset $file): string
    {
        $adapter = $file->disk()->filesystem()->getAdapter();

        return match (true) {
            ($adapter instanceof Local) => $file->resolvedPath(),
            ($adapter instanceof AwsS3Adapter) => $file->absoluteUrl(),
            default => throw new Exception('Zipper doesn\'t support ['.$adapter::class.'].'),
        };
    }

    protected static function filename(?string $filename): string
    {
        return $filename ? "{$filename}.zip" : time().'.zip';
    }

    protected static function exists(string $file): bool
    {
        if (URL::isValidUrl($file)) {
            try {
                return Http::get($file)->successful();
            } catch (Exception $e) {
                return false;
            }
        }

        return File::exists($file);
    }
}
