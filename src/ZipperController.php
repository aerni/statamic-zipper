<?php

namespace Aerni\Zipper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Statamic\Http\Controllers\Controller;

class ZipperController extends Controller
{
    public function create(string $files, Request $request)
    {
        $request->validate([
            'filename' => 'sometimes|required|string',
        ]);

        return Zipper::create(
            files: json_decode(Crypt::decryptString($files), true),
            filename: $request->get('filename')
        );
    }
}
