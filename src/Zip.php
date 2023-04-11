<?php

namespace Aerni\Zipper;

use Aerni\Zipper\Facades\ZipperStore;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Statamic\Contracts\Assets\Asset;
use STS\ZipStream\Models\File;
use STS\ZipStream\ZipStream;
use STS\ZipStream\ZipStreamFacade;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Zip
{
    protected Collection $files;
    protected string $filename;
    protected int $expiry;

    public function __construct(array $files)
    {
        $this
            ->files($files)
            ->filename(time())
            ->expiry((int) config('zipper.expiry'));
    }

    public static function make(array $files): self
    {
        return new self($files);
    }

    /**
     * Get and set the files to zip.
     */
    public function files(array $files = null): Collection|self
    {
        if (! func_get_args()) {
            return $this->files;
        }

        $this->files = collect($files);

        return $this;
    }

    /**
     * Get and set the filename of the zip.
     */
    public function filename(string $filename = null): string|self
    {
        if (! func_get_args()) {
            return $this->filename;
        }

        // Make sure we never have an empty string as filename.
        $this->filename = empty($filename) ? time() : $filename;

        return $this;
    }

    /**
     * Get and set the expiry of the zip route.
     */
    public function expiry(int $expiry = null): int|self
    {
        if (! func_get_args()) {
            return $this->expiry;
        }

        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Check if the stored zip reference file is expired.
     */
    public function expired(): bool
    {
        if (empty($this->expiry)) {
            return false;
        }

        return ZipperStore::createdAt($this->id())
            ->addMinutes($this->expiry)
            ->isPast();
    }

    /**
     * Returns the route that handles creating the zip.
     */
    public function url(): string
    {
        $this->storeReferenceFile();

        return empty($this->expiry)
            ? URL::signedRoute('statamic.zipper.create', $this->id())
            : URL::temporarySignedRoute('statamic.zipper.create', now()->addMinutes($this->expiry), $this->id());
    }

    /**
     * Encrypt and store this class so we can later restore it in the controller.
     */
    protected function storeReferenceFile(): self
    {
        ZipperStore::put($this->id(), $this);

        return $this;
    }

    /**
     * Delete the zip reference file.
     */
    public function deleteReferenceFile(): self
    {
        ZipperStore::delete($this->id());

        return $this;
    }

    /**
     * Create a new zip or download a previously cached zip.
     */
    public function get(): ZipStream|StreamedResponse
    {
        return $this->shouldCacheZip() ? $this->cache() : $this->create();
    }

    /**
     * Create and stream a new zip.
     */
    protected function create(): ZipStream
    {
        $zip = ZipStreamFacade::create("{$this->filename}.zip");

        $this->files->each(fn ($file) => $this->addFileToZip($file, $zip));

        return $zip;
    }

    /**
     * Stream the zip while also caching it to disk for future requests.
     * This let's us download previously cached zips instead of creating new ones.
     */
    protected function cache(): ZipStream|StreamedResponse
    {
        $zip = $this->create();
        $filename = "{$zip->getFingerprint()}.zip";
        $disk = Storage::disk(config('zipper.disk'));

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

    /**
     * Add a file to the zip.
     */
    protected function addFileToZip(Asset|string $file, ZipStream $zip): ZipStream
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

    /**
     * The filename will be '0' if it isn't a timestamp.
     */
    protected function hasCustomFilename(): bool
    {
        return (int) $this->filename === 0 ? true : false;
    }

    /**
     * If the zip doesn't have a custom filename, we would be endlessly caching
     * new zips and never returning previously cached zips.
     */
    protected function shouldCacheZip(): bool
    {
        return config('zipper.save') && $this->hasCustomFilename();
    }

    /**
     * The unique reference of this zip file.
     */
    protected function id(): string
    {
        return md5($this->create()->getFingerprint().$this->expiry);
    }
}
