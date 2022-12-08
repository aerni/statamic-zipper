<?php

namespace Aerni\Zipper;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class ZipperController extends Controller
{
    public function create(string $files, Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $request->validate([
            'filename' => 'sometimes|required|string',
        ]);

        return Zipper::create(
            files: Zipper::decrypt($files),
            filename: $request->get('filename')
        );
    }
}
