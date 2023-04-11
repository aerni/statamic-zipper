<?php

namespace Aerni\Zipper\Jobs;

use Aerni\Zipper\Facades\ZipperStore;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanReferenceFilesJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $scope = 'expired')
    {
        //
    }

    public function handle(): void
    {
        $zips = ZipperStore::all();

        $zips = match (true) {
            ($this->scope === 'expired') => $zips->filter(fn ($zip) => $zip->expired()), // Only delete expired reference files
            ($this->scope === 'all') => $zips->filter(fn ($zip) => $zip->expired() || empty($zip->expiry())), // Delete all reference files (excluding unexpired files)
            ($this->scope === 'force') => $zips, // Delete all reference files (including unexpired files)
            default => throw new \Exception('Please provide a valid cleanup scope.')
        };

        $zips->each(fn ($zip) => $zip->deleteReferenceFile());
    }
}
