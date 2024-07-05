<?php

namespace Aerni\Zipper;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class ZipperStore
{
    public function __construct(protected Filesystem $store) {}

    public function all(): Collection
    {
        return collect($this->store->allFiles())
            ->map(fn ($file) => $this->get($file));
    }

    public function put(string $path, Zipper $zip): bool
    {
        return $this->store->put($path, Crypt::encrypt($zip));
    }

    public function get(string $path): ?Zipper
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

    public function lastModified(string $path): Carbon
    {
        return Carbon::createFromTimestamp($this->store->lastModified($path));
    }
}
