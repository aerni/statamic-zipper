<?php

namespace Aerni\Zipper;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Http\Controllers\Controller;

class ZipperController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'files' => 'required|array',
            'filename' => 'sometimes|required|string',
        ]);

        return Zipper::create(Arr::get($data, 'files'), Arr::get($data, 'filename'));
    }
}
