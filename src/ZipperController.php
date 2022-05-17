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
            'filename' => 'required',
            'files' => 'required',
        ]);

        return Zipper::create($data['filename'], $data['files']);
    }
}
