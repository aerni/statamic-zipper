<?php

namespace Aerni\Zipper;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class ZipperController extends Controller
{
    public function create(string $cipher, Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $plaintext = Zipper::decrypt($cipher);

        return Zipper::create(
            files: $plaintext['files'],
            filename: $plaintext['filename'],
        );
    }
}
