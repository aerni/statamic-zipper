<?php

namespace Aerni\Zipper;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

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
}
