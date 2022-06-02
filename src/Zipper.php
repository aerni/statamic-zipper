<?php

namespace Aerni\Zipper;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Statamic\Contracts\Assets\Asset;
use STS\ZipStream\Models\File;
use STS\ZipStream\Models\S3File;
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

        $zip = Zip::create(self::filename($filename));

        $files->each(function ($file) use ($zip) {
            $disk = Storage::disk($file['disk']);

            $adapter = $disk->getAdapter();

            $path = match (true) {
                ($adapter instanceof Local) => $file['path'],
                ($adapter instanceof AwsS3Adapter) => "s3://{$adapter->getBucket()}{$file['path']}",
                default => throw new Exception('Zipper doesn\'t support ['.$adapter::class.'].'),
            };

            $file = File::make($path);

            if ($file instanceof S3File) {
                $file->setS3Client($adapter->getClient());
            }

            $zip->add($file);
        });

        if (! config('zipper.save')) {
            return $zip;
        }

        $disk = Storage::disk(config('zipper.disk'));
        $adapter = $disk->getAdapter();
        $filename = self::filename($zip->getFingerprint());

        if ($disk->exists($filename)) {
            return $disk->download($filename, $zip->getName());
        }

        $path = match (true) {
            ($adapter instanceof Local) => $disk->path($filename),
            ($adapter instanceof AwsS3Adapter) => "s3://{$adapter->getBucket()}/{$disk->path($filename)}",
            default => throw new Exception('Zipper doesn\'t support ['.$adapter::class.'].'),
        };

        $file = File::make($path);

        if ($file instanceof S3File) {
            $file->setS3Client($adapter->getClient());
        }

        return $zip->cache($file);
    }

    protected static function files(Collection|array $files): Collection
    {
        return collect($files)->map(fn ($file) => match (true) {
            ($file instanceof Asset) => [
                'path' => $file->resolvedPath(),
                'disk' => $file->container()->diskHandle(),
            ],
            default => $file,
        });
    }

    protected static function filename(?string $filename): string
    {
        return $filename ? "{$filename}.zip" : time().'.zip';
    }

    protected static function exists(array $file): bool
    {
        return Storage::disk($file['disk'])->exists($file['path']);
    }
}
