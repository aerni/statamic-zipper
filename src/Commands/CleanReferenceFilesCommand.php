<?php

namespace Aerni\Zipper\Commands;

use Aerni\Zipper\Jobs\CleanReferenceFilesJob;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class CleanReferenceFilesCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'zipper:clean {--scope=expired}';

    protected $description = 'Delete old zipper reference files';

    public function handle()
    {
        CleanReferenceFilesJob::dispatch($this->option('scope'));

        $this->info('Successfully dispatched the cleanup job.');
    }
}
