<?php

namespace Aerni\Zipper;

use Facades\Aerni\Zipper\Zipper;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class ZipperController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'files' => 'required',
            'filename' => 'required',
        ]);

        return Zipper::create($data['files'], $data['filename']);
    }
}
