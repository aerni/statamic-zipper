<?php

namespace Aerni\Zipper;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Aerni\Zipper\Jobs\CleanReferenceFilesJob;
use Illuminate\Contracts\Filesystem\Filesystem;

class ZipperStore
{
    protected Filesystem $store;

    public function __construct()
    {
        $this->store = Storage::build([
            'driver' => 'local',
            'root' => storage_path('statamic/zipper'),
        ]);
    }

    public function all(): Collection
    {
        return collect($this->store->allFiles())
            ->map(fn ($file) => $this->get($file));
    }

    public function put(string $path, Zip $zip): bool
    {
        return $this->store->put($path, Crypt::encrypt($zip));
    }

    public function get(string $path): ?Zip
    {
        try {
            return Crypt::decrypt($this->store->get($path));
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function exists(string $path): bool
    {
        return $this->store->exists($path);
    }

    public function delete(string $path): bool
    {
        return $this->store->delete($path);
    }

    public function createdAt(string $path): Carbon
    {
        $createdAt = filemtime(storage_path('statamic/zipper').'/'.$path);

        return Carbon::createFromTimestamp($createdAt);
    }
}
