<?php

namespace Aerni\Zipper\Tests;

use Aerni\Zipper\ServiceProvider;
use Statamic\Testing\AddonTestCase;
use STS\ZipStream\ZipStreamServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getPackageProviders($app)
    {
        return array_merge(
            parent::getPackageProviders($app),
            [ZipStreamServiceProvider::class],
        );
    }
}
