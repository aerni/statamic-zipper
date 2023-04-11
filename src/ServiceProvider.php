<?php

namespace Aerni\Zipper;

use Aerni\Zipper\Commands\CleanReferenceFilesCommand;
use Statamic\Providers\AddonServiceProvider;

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

    protected function schedule($schedule)
    {
        $scope = config('zipper.cleanup', 'expired');

        $schedule->command(CleanReferenceFilesCommand::class, ["--scope={$scope}"])->daily();
    }
}
