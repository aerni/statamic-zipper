<?php

namespace Aerni\Zipper;

use Illuminate\Http\Request;
use Facades\Aerni\Zipper\Zipper;
use Statamic\Http\Controllers\Controller;

class ZipperController extends Controller
{
    public function create(Request $request)
    {
        if (! $this->isValid($request)) {
            abort(403);
        }

        $filename = $request->get('filename');
        $files = $request->get('files');

        return Zipper::create($filename, $files);
    }

    protected function isValid(Request $request): bool
    {
        if (empty($request->query())) {
            return false;
        }

        $request->validate([
            'filename' => 'required',
            'files' => 'required',
        ]);

        return true;
    }
}
