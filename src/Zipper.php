<?php

namespace Aerni\Zipper;

use ZipArchive;
use Statamic\Assets\Asset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Zipper
{
    protected $zip;
    protected $disk;
    protected $files;
    protected $filename;

    public function __construct()
    {
        $this->zip = new ZipArchive();
        $this->disk = Storage::disk(config('zipper.disk'));
    }

    public function disk(string $disk): self
    {
        $this->disk = Storage::disk($disk);

        return $this;
    }

    public function files(Collection $files): self
    {
        $this->files = $files;

        return $this;
    }

    public function filename(string $filename): self
    {
        $this->filename = $filename . '.zip';

        return $this;
    }

    public function save(): self
    {
        $this->zip->open($this->path(), ZipArchive::CREATE);

        $this->files->each(function (Asset $file) {
            $this->zip->addFile($file->resolvedPath(), $file->basename());
        });

        $this->zip->close();

        return $this;
    }

    public function url(): string
    {
        return $this->disk->url($this->filename);
    }

    public function path(): string
    {
        return $this->disk->path($this->filename);
    }
}
