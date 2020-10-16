<?php

namespace Aerni\Zipper;

use Statamic\Tags\Tags;

class ZipperTags extends Tags
{
    protected static $handle = 'zip';

    public function wildcard()
    {
        return (new Zipper())
            ->files($this->context->get($this->method)->value())
            ->filename($this->params->get('filename') ?? time())
            ->save()
            ->url();
    }
}
