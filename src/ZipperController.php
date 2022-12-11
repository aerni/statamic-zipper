<?php

namespace Aerni\Zipper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Statamic\Http\Controllers\Controller;

class ZipperController extends Controller
{
    public function create(string $cipher, Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $zip = Crypt::decrypt($cipher);

        return $zip->get();
    }
}
