<?php

namespace Aerni\Zipper;

use Statamic\Facades\Asset;
use STS\ZipStream\ZipStreamFacade as Zip;
use STS\ZipStream\ZipStream;

class Zipper
{
    public function create(string $filename, array $files): ZipStream
    {
        $filename = $this->filename($filename);
        $files = $this->files($files);

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
}
