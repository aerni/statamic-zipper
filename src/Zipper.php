<?php

namespace Aerni\Zipper;

use Statamic\Facades\Asset;
use Illuminate\Support\Facades\Storage;
use STS\ZipStream\ZipStreamFacade as Zip;

class Zipper
{
    public function create(string $filename, array $files)
    {
        $filename = $this->filename($filename);
        $files = $this->files($files);

        if ($this->saveToDisk()) {
            return $this->save($filename, $files);
        }

        return $this->stream($filename, $files);
    }

    protected function save(string $filename, array $files)
    {
        $disk = Storage::disk(config('zipper.disk'));
        $path = $disk->getAdapter()->getPathPrefix();

        Zip::create($filename, $files)->saveTo($path);

        return response()->download($path . '/' . $filename);
    }

    protected function stream(string $filename, array $files)
    {
        return Zip::create($filename, $files);
    }

    protected function filename(string $filename): string
    {
        return $filename . '.zip';
    }

    protected function files(array $files): array
    {
        return collect($files)->map(function ($file) {
            return $this->url($file);
        })->filter()->all();
    }

    protected function url($file): ?string
    {
        $file = Asset::findById($file);

        if (is_null($file)) {
            return null;
        }

        return substr($file->url(), 1);
    }

    protected function saveToDisk(): bool
    {
        if (! config('zipper.save')) {
            return false;
        }

        return true;
    }
}
