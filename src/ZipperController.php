<?php

namespace Aerni\Zipper;

use Aerni\Zipper\ZipStore;
use Illuminate\Http\Request;
use STS\ZipStream\ZipStream;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipperController extends Controller
{
    public function create(string $reference, Request $request, ZipStore $store): ZipStream|StreamedResponse
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
