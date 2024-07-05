<?php

namespace Aerni\Zipper;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Scheduling\Schedule;
use Statamic\Providers\AddonServiceProvider;
use Aerni\Zipper\Commands\CleanReferenceFilesCommand;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        CleanReferenceFilesCommand::class,
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
    ];

    protected $tags = [
        ZipperTags::class,
    ];

    public function register(): void
    {
        $this->app->singleton(ZipperStore::class, function () {
            return new ZipperStore(Storage::build([
                'driver' => 'local',
                'root' => storage_path('statamic/zipper'),
            ]));
        });
    }

    protected function schedule(Schedule $schedule): void
    {
        $scope = config('zipper.cleanup', 'expired');

        $schedule->command(CleanReferenceFilesCommand::class, ["--scope={$scope}"])->daily();
    }
}
