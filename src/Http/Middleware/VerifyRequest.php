<?php

namespace Aerni\Zipper\Http\Middleware;

use Aerni\Zipper\Facades\ZipperStore;
use Closure;
use Statamic\Exceptions\NotFoundHttpException;

class VerifyRequest
{
    public function handle($request, Closure $next)
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        if (! ZipperStore::exists($request->route()->parameter('id'))) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
