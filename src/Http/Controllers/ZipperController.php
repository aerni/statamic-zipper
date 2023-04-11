<?php

namespace Aerni\Zipper\Http\Controllers;

use Aerni\Zipper\ZipperStore;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\Controller;
use STS\ZipStream\ZipStream;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipperController extends Controller
{
    public function create(string $reference, Request $request, ZipperStore $store): ZipStream|StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        if (! $zip = $store->get($reference)) {
            throw new NotFoundHttpException();
        }

        return $zip->get();
    }
}
