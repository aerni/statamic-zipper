<?php

namespace Aerni\Zipper;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as AssetFacade;
use STS\ZipStream\Models\File;
use STS\ZipStream\ZipStream;
use STS\ZipStream\ZipStreamFacade as Zip;

class Zipper
{
    public static function route(Collection $files, ?string $filename = null): string
    {
        return route('statamic.zipper.create', [
            'files' => self::encrypt($files),
            'filename' => $filename,
        ]);
    }

    public static function create(Collection $files, ?string $filename = null): mixed
    {
        $zip = Zip::create(self::filename($filename));

        $files->each(fn ($file) => self::addFile($file, $zip));

        return config('zipper.save')
            ? self::cache($zip)
            : $zip;
    }

    protected static function cache(ZipStream $zip): mixed
    {
        $disk = Storage::disk(config('zipper.disk'));
        $filename = self::filename($zip->getFingerprint());

        if ($disk->exists($filename)) {
            return $disk->download($filename, $zip->getName());
        }

        $adapter = $disk->getAdapter();

        if ($adapter instanceof LocalFilesystemAdapter) {
            return $zip->cache($disk->path($filename));
        }

        if ($adapter instanceof AwsS3V3Adapter) {
            $path = "s3://{$disk->getConfig()['bucket']}/{$disk->path($filename)}";
            $s3Client = $disk->getClient();
            $file = File::make($path)->setS3Client($s3Client);

            return $zip->cache($file);
        }

        throw new Exception('Zipper doesn\'t support ['.$adapter::class.'].');
    }

    protected static function addFile(Asset|string $file, ZipStream $zip): ZipStream
    {
        if (is_string($file)) {
            return $zip->add($file);
        }

        $disk = $file->disk()->filesystem();
        $adapter = $disk->getAdapter();

        if ($adapter instanceof LocalFilesystemAdapter) {
            return $zip->add($file->resolvedPath());
        }

        if ($adapter instanceof AwsS3V3Adapter) {
            $path = "s3://{$disk->getConfig()['bucket']}/{$file->path()}";
            $s3Client = $disk->getClient();
            $file = File::make($path)->setS3Client($s3Client);

            return $zip->add($file);
        }

        throw new Exception('Zipper doesn\'t support ['.$adapter::class.'].');
    }

    protected static function encrypt(Collection $files): string
    {
        $files = $files->map(fn ($file) => match (true) {
            ($file instanceof Asset) => $file->id(),
            (is_string($file)) => $file,
            default => throw new Exception('Unsupported file type. The file has to be an Statamic Asset or a string.')
        });

        return Crypt::encryptString($files);
    }

    public static function decrypt(string $files): Collection
    {
        $files = json_decode(Crypt::decryptString($files));

        return collect($files)->map(fn ($file) => AssetFacade::find($file) ?? $file);
    }

    protected static function filename(?string $filename): string
    {
        return $filename ? "{$filename}.zip" : time().'.zip';
    }
}
