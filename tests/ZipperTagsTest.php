<?php

namespace Aerni\Zipper\Tests;

use Aerni\Zipper\ZipperTags;

class ZipperTagsTest extends TestCase
{
    protected $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(ZipperTags::class);
    }

    /** @test */
    public function it_returns_the_zip_url()
    {
        //
    }
}
