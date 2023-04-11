<?php

use Aerni\Zipper\Http\Controllers\ZipperController;
use Aerni\Zipper\Http\Middleware\VerifyRequest;
use Illuminate\Support\Facades\Route;

Route::get('{id}', [ZipperController::class, 'create'])
    ->middleware(VerifyRequest::class)
    ->name('zipper.create');
