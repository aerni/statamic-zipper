<?php

namespace Aerni\Zipper\Http\Controllers;

use Aerni\Zipper\Facades\ZipperStore;
use Statamic\Http\Controllers\Controller;
use STS\ZipStream\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipperController extends Controller
{
    public function __invoke(string $id): Builder|StreamedResponse
    {
        return ZipperStore::get($id)->get();
    }
}
